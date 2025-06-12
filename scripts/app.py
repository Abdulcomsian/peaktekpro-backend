import os
import logging
from flask import Flask, request, jsonify
from werkzeug.utils import secure_filename
import fitz  # PyMuPDF
import cv2
import numpy as np
import base64

app = Flask(__name__)
app.config['UPLOAD_FOLDER'] = 'uploads'
app.config['SIGNATURE_FOLDER'] = 'static/signatures'
app.config['ALLOWED_EXTENSIONS'] = {'pdf'}

# Configure logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

# Create directories if they don't exist
os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)
os.makedirs(app.config['SIGNATURE_FOLDER'], exist_ok=True)

def allowed_file(filename):
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in app.config['ALLOWED_EXTENSIONS']

def image_to_base64(image):
    """Convert OpenCV image to base64 string"""
    try:
        _, buffer = cv2.imencode('.png', image)
        img_base64 = base64.b64encode(buffer).decode('utf-8')
        return img_base64
    except Exception as e:
        logger.error(f"Error converting image to base64: {str(e)}")
        return None

def is_signature(image):
    """More precise signature detection to avoid false positives"""
    try:
        # Convert to grayscale
        gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
        
        # Apply adaptive thresholding
        thresh = cv2.adaptiveThreshold(gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
                                    cv2.THRESH_BINARY_INV, 11, 2)
        
        # Count non-zero pixels (ink)
        ink_pixels = cv2.countNonZero(thresh)
        total_pixels = thresh.size
        coverage = ink_pixels / total_pixels
        
        # Find contours
        contours, _ = cv2.findContours(thresh, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
        if not contours:
            return False
            
        # Get largest contour
        largest_contour = max(contours, key=cv2.contourArea)
        x, y, w, h = cv2.boundingRect(largest_contour)
        aspect_ratio = w / h if h > 0 else 0
        
        # More strict signature heuristics to reduce false positives
        logger.debug(f"Signature detection - Coverage: {coverage:.4f}, Aspect Ratio: {aspect_ratio:.2f}, Size: {w}x{h}")
        
        # Stricter criteria: 
        # - Coverage between 0.005 and 0.3 (avoid tiny dots and full pages)
        # - Aspect ratio between 0.5 and 6 (more realistic signature shapes)
        # - Minimum size requirements
        is_valid_signature = (
            (0.005 < coverage < 0.3) and 
            (0.5 < aspect_ratio < 6) and
            (w > 50 and h > 20)  # Minimum size to avoid tiny artifacts
        )
        
        return is_valid_signature
        
    except Exception as e:
        logger.error(f"Error in signature detection: {str(e)}")
        return False

def extract_signatures(pdf_path):
    """Extract signatures with improved logic to avoid duplicates and false positives"""
    signatures = []
    
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
            
            page_signatures_found = False
            
            # First try extracting embedded images (prioritize this)
            if image_list:
                for img_index, img in enumerate(image_list):
                    try:
                        xref = img[0]
                        base_image = doc.extract_image(xref)
                        image_bytes = base_image["image"]
                        nparr = np.frombuffer(image_bytes, np.uint8)
                        img_cv = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
                        
                        if img_cv is not None and is_signature(img_cv):
                            signature_filename = f"signature_page_{page_num+1}_img{img_index}.png"
                            full_path = os.path.join(app.config['SIGNATURE_FOLDER'], signature_filename)
                            cv2.imwrite(full_path, img_cv)
                            
                            signature_data = {
                                'page': page_num + 1,
                                'type': 'embedded',
                                'identifier': f"page_{page_num+1}_img_{img_index}",
                                'width': img_cv.shape[1],
                                'height': img_cv.shape[0],
                                'confidence': 0.8,
                                'file_path': full_path,
                                'filename': signature_filename,
                                'image_url': f"/static/signatures/{signature_filename}",
                                'imageData': f"data:image/png;base64,{image_to_base64(img_cv)}"
                            }
                            
                            signatures.append(signature_data)
                            page_signatures_found = True
                            logger.info(f"Found embedded signature on page {page_num+1}, image {img_index}")
                    except Exception as e:
                        logger.error(f"Error processing embedded image {img_index} on page {page_num+1}: {str(e)}")
            
            # Only try rendering if NO embedded signatures were found on this page
            if not page_signatures_found:
                try:
                    pix = page.get_pixmap(dpi=150)
                    img_bytes = pix.tobytes("png")
                    nparr = np.frombuffer(img_bytes, np.uint8)
                    img_cv = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
                    
                    if img_cv is not None and is_signature(img_cv):
                        signature_filename = f"signature_page_{page_num+1}_rendered.png"
                        full_path = os.path.join(app.config['SIGNATURE_FOLDER'], signature_filename)
                        cv2.imwrite(full_path, img_cv)
                        
                        signature_data = {
                            'page': page_num + 1,
                            'type': 'rendered',
                            'identifier': f"page_{page_num+1}_rendered",
                            'width': img_cv.shape[1],
                            'height': img_cv.shape[0],
                            'confidence': 0.7,
                            'file_path': full_path,
                            'filename': signature_filename,
                            'image_url': f"/static/signatures/{signature_filename}",
                            'imageData': f"data:image/png;base64,{image_to_base64(img_cv)}"
                        }
                        
                        signatures.append(signature_data)
                        logger.info(f"Found rendered signature on page {page_num+1}")
                except Exception as e:
                    logger.error(f"Error processing rendered page {page_num+1}: {str(e)}")
        
        doc.close()
        logger.info(f"Extraction complete. Found {len(signatures)} signatures total")
        
    except Exception as e:
        logger.error(f"Error processing PDF: {str(e)}", exc_info=True)
        raise RuntimeError(f"PDF processing error: {str(e)}")
    
    return signatures

@app.route('/api/extract-signatures', methods=['POST'])
def api_extract_signatures():
    """API endpoint for signature extraction"""
    try:
        if 'pdf_file' not in request.files:
            logger.debug("No file part in request")
            return jsonify({
                'success': False,
                'error': 'No file provided'
            }), 400
        
        file = request.files['pdf_file']
        if file.filename == '':
            logger.debug("Empty filename")
            return jsonify({
                'success': False,
                'error': 'No file selected'
            }), 400
        
        if not (file and allowed_file(file.filename)):
            logger.debug(f"Invalid file type: {file.filename}")
            return jsonify({
                'success': False,
                'error': 'Invalid file type. Only PDF files are allowed.'
            }), 400
            
        filename = secure_filename(file.filename)
        filepath = os.path.join(app.config['UPLOAD_FOLDER'], filename)
        logger.debug(f"Saving file to {filepath}")
        
        file.save(filepath)
        logger.debug("File saved successfully, extracting signatures")
        
        signatures = extract_signatures(filepath)
        logger.debug(f"Signature extraction result: {signatures}")
        
        # Convert local paths to URLs
        base_url = request.host_url.rstrip('/')
        for sig in signatures:
            sig['image_url'] = base_url + sig['image_url']
        
        return jsonify({
            'success': True,
            'message': 'Signatures extracted successfully',
            'signatures': signatures,
            'count': len(signatures)
        }), 200
        
    except Exception as e:
        logger.error(f"Unexpected error: {str(e)}", exc_info=True)
        return jsonify({
            'success': False,
            'error': f"An error occurred: {str(e)}"
        }), 500

@app.route('/api/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy',
        'version': '1.0'
    })

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5001, debug=True)