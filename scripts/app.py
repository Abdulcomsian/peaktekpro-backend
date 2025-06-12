import os
import logging
import json
import tempfile
from flask import Flask, request, jsonify
from werkzeug.utils import secure_filename
import fitz  # PyMuPDF
import cv2
import numpy as np

app = Flask(__name__)

# Configure paths - use temp directory to avoid permission issues
TEMP_DIR = tempfile.gettempdir()
app.config['UPLOAD_FOLDER'] = os.path.join(TEMP_DIR, 'pdf_uploads')
app.config['SIGNATURE_FOLDER'] = os.path.join(TEMP_DIR, 'signatures')
app.config['ALLOWED_EXTENSIONS'] = {'pdf'}

# Configure logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

# Create directories if they don't exist
try:
    os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)
    os.makedirs(app.config['SIGNATURE_FOLDER'], exist_ok=True)
    logger.info(f"Directories created: {app.config['UPLOAD_FOLDER']}, {app.config['SIGNATURE_FOLDER']}")
except Exception as e:
    logger.error(f"Failed to create directories: {str(e)}")

def allowed_file(filename):
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in app.config['ALLOWED_EXTENSIONS']

def is_signature(image):
    """More robust signature detection using multiple features"""
    try:
        if image is None:
            return False
            
        # Convert to grayscale
        gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
        
        # Apply adaptive thresholding
        thresh = cv2.adaptiveThreshold(gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
                                    cv2.THRESH_BINARY_INV, 11, 2)
        
        # Count non-zero pixels (ink)
        ink_pixels = cv2.countNonZero(thresh)
        total_pixels = thresh.size
        coverage = ink_pixels / total_pixels if total_pixels > 0 else 0
        
        # Find contours
        contours, _ = cv2.findContours(thresh, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
        if not contours:
            return False
            
        # Get largest contour
        largest_contour = max(contours, key=cv2.contourArea)
        x, y, w, h = cv2.boundingRect(largest_contour)
        aspect_ratio = w / h if h > 0 else 0
        
        # More lenient signature heuristics
        logger.debug(f"Signature detection - Coverage: {coverage:.4f}, Aspect Ratio: {aspect_ratio:.2f}")
        return (0.001 < coverage < 0.5) and (0.3 < aspect_ratio < 8)
        
    except Exception as e:
        logger.error(f"Error in signature detection: {str(e)}")
        return False

def extract_signatures(pdf_path):
    signatures = {}
    try:
        if not os.path.exists(pdf_path):
            raise FileNotFoundError(f"PDF file not found: {pdf_path}")
            
        doc = fitz.open(pdf_path)
        logger.info(f"Processing PDF with {len(doc)} pages")
        
        for page_num in range(len(doc)):
            page = doc.load_page(page_num)
            
            # Get list of images on the page
            image_list = page.get_images(full=True)
            logger.debug(f"Page {page_num+1} has {len(image_list)} images")
            
            # First try extracting embedded images
            if image_list:
                for img_index, img in enumerate(image_list):
                    try:
                        xref = img[0]
                        base_image = doc.extract_image(xref)
                        image_bytes = base_image["image"]
                        nparr = np.frombuffer(image_bytes, np.uint8)
                        img_cv = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
                        
                        if img_cv is not None and is_signature(img_cv):
                            signature_key = f"Page {page_num+1}-Img{img_index}"
                            signature_filename = f"signature_page_{page_num+1}_img{img_index}.png"
                            full_path = os.path.join(app.config['SIGNATURE_FOLDER'], signature_filename)
                            
                            # Save signature image
                            cv2.imwrite(full_path, img_cv)
                            signatures[signature_key] = {
                                'type': 'embedded_image',
                                'page': page_num + 1,
                                'file_path': full_path,
                                'bbox': [0, 0, img_cv.shape[1], img_cv.shape[0]]  # width, height
                            }
                            logger.info(f"Found signature: {signature_key}")
                    except Exception as e:
                        logger.error(f"Error processing embedded image {img_index} on page {page_num+1}: {str(e)}")
                        continue
            
            # If no embedded images found, try rendering the page and look for signature areas
            if not image_list or len([s for s in signatures.keys() if f"Page {page_num+1}" in s]) == 0:
                try:
                    pix = page.get_pixmap(dpi=150)
                    img_bytes = pix.tobytes("png")
                    nparr = np.frombuffer(img_bytes, np.uint8)
                    img_cv = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
                    
                    if img_cv is not None:
                        # Look for signature-like areas in the rendered page
                        signature_areas = find_signature_areas(img_cv)
                        
                        for area_index, area in enumerate(signature_areas):
                            signature_key = f"Page {page_num+1}-Area{area_index}"
                            signature_filename = f"signature_page_{page_num+1}_area{area_index}.png"
                            full_path = os.path.join(app.config['SIGNATURE_FOLDER'], signature_filename)
                            
                            # Extract and save the signature area
                            x, y, w, h = area['bbox']
                            signature_roi = img_cv[y:y+h, x:x+w]
                            cv2.imwrite(full_path, signature_roi)
                            
                            signatures[signature_key] = {
                                'type': 'signature_area',
                                'page': page_num + 1,
                                'file_path': full_path,
                                'bbox': area['bbox']
                            }
                            logger.info(f"Found signature area: {signature_key}")
                            
                except Exception as e:
                    logger.error(f"Error rendering page {page_num+1}: {str(e)}")
                    continue
        
        doc.close()
        logger.info(f"Extracted {len(signatures)} signatures total")
        
    except Exception as e:
        logger.error(f"Error processing PDF: {str(e)}")
        raise RuntimeError(f"PDF processing error: {str(e)}")
    
    return signatures

def find_signature_areas(image):
    """Find potential signature areas in a rendered page"""
    try:
        # Convert to grayscale
        gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
        
        # Apply threshold to find dark areas (potential signatures)
        _, thresh = cv2.threshold(gray, 240, 255, cv2.THRESH_BINARY_INV)
        
        # Find contours
        contours, _ = cv2.findContours(thresh, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
        
        signature_areas = []
        for contour in contours:
            area = cv2.contourArea(contour)
            if area > 500:  # Minimum area threshold
                x, y, w, h = cv2.boundingRect(contour)
                aspect_ratio = w / h if h > 0 else 0
                
                # Check if it looks like a signature
                if 1.0 < aspect_ratio < 8 and 50 < w < 400 and 20 < h < 150:
                    # Extract the region and test if it's a signature
                    roi = image[y:y+h, x:x+w]
                    if is_signature(roi):
                        signature_areas.append({
                            'bbox': [x, y, w, h],
                            'area': area,
                            'aspect_ratio': aspect_ratio
                        })
        
        return signature_areas
        
    except Exception as e:
        logger.error(f"Error finding signature areas: {str(e)}")
        return []

@app.route('/api/detect-signatures', methods=['POST'])
def detect_signatures():
    """API endpoint to detect signatures in PDF"""
    try:
        logger.info("Received signature detection request")
        
        # Check if file is provided
        if 'pdf_file' not in request.files:
            logger.warning("No file provided in request")
            return jsonify({
                'success': False,
                'error': 'No PDF file provided'
            }), 400
        
        file = request.files['pdf_file']
        if file.filename == '':
            logger.warning("Empty filename provided")
            return jsonify({
                'success': False,
                'error': 'No file selected'
            }), 400
        
        # Validate file type
        if not allowed_file(file.filename):
            logger.warning(f"Invalid file type: {file.filename}")
            return jsonify({
                'success': False,
                'error': 'Invalid file type. Only PDF files are allowed.'
            }), 400
        
        # Save file temporarily
        filename = secure_filename(file.filename)
        filepath = os.path.join(app.config['UPLOAD_FOLDER'], filename)
        
        try:
            file.save(filepath)
            logger.info(f"File saved to: {filepath}")
            
            # Extract signatures
            signatures = extract_signatures(filepath)
            
            # Clean up uploaded file
            if os.path.exists(filepath):
                os.remove(filepath)
            
            # Format response similar to original
            if not signatures:
                return jsonify({
                    'success': True,
                    'signatures': {},
                    'message': 'No signatures found in the PDF',
                    'count': 0
                })
            
            # Convert signatures to simpler format for API response
            api_signatures = {}
            for key, sig_info in signatures.items():
                api_signatures[key] = {
                    'type': sig_info['type'],
                    'page': sig_info['page'],
                    'bbox': sig_info['bbox']
                }
            
            return jsonify({
                'success': True,
                'signatures': api_signatures,
                'message': f'Found {len(signatures)} signature(s)',
                'count': len(signatures)
            })
            
        except Exception as e:
            # Clean up file on error
            if os.path.exists(filepath):
                try:
                    os.remove(filepath)
                except:
                    pass
            raise e
            
    except Exception as e:
        logger.error(f"API error: {str(e)}")
        return jsonify({
            'success': False,
            'error': f'Processing error: {str(e)}'
        }), 500

@app.route('/api/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({
        'success': True,
        'message': 'PDF Signature Detection API is running',
        'version': '1.0.0'
    })

@app.errorhandler(Exception)
def handle_exception(e):
    """Global error handler"""
    logger.error(f"Unhandled exception: {str(e)}")
    return jsonify({
        'success': False,
        'error': 'Internal server error'
    }), 500

if __name__ == '__main__':
    try:
        logger.info("Starting PDF Signature Detection API...")
        logger.info(f"Upload folder: {app.config['UPLOAD_FOLDER']}")
        logger.info(f"Signature folder: {app.config['SIGNATURE_FOLDER']}")
        
        # Test dependencies
        logger.info("Testing dependencies...")
        import fitz
        import cv2
        import numpy as np
        logger.info("All dependencies loaded successfully")
        
        app.run(host='0.0.0.0', port=5001, debug=False)
        
    except ImportError as e:
        logger.error(f"Missing dependency: {str(e)}")
        print(f"ERROR: Missing dependency: {str(e)}")
        print("Please install required packages: pip install PyMuPDF opencv-python numpy flask")
    except Exception as e:
        logger.error(f"Failed to start server: {str(e)}")
        print(f"ERROR: Failed to start server: {str(e)}")