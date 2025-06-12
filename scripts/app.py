import os
import logging
from flask import Flask, request, jsonify
from werkzeug.utils import secure_filename
import fitz  # PyMuPDF
import cv2
import numpy as np

app = Flask(__name__)
app.config['UPLOAD_FOLDER'] = 'uploads'
app.config['SIGNATURE_FOLDER'] = 'static/signatures'
app.config['ALLOWED_EXTENSIONS'] = {'pdf'}

# Configure logging
logging.basicConfig(level=logging.DEBUG)
logger = logging.getLogger(__name__)

# Create directories if they don't exist
os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)
os.makedirs(app.config['SIGNATURE_FOLDER'], exist_ok=True)

def allowed_file(filename):
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in app.config['ALLOWED_EXTENSIONS']

def is_signature(image):
    """More robust signature detection using multiple features"""
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
        aspect_ratio = w / h
        
        # More lenient signature heuristics
        logger.debug(f"Signature detection - Coverage: {coverage:.4f}, Aspect Ratio: {aspect_ratio:.2f}")
        return (0.001 < coverage < 0.5) and (0.3 < aspect_ratio < 8)
        
    except Exception as e:
        logger.error(f"Error in signature detection: {str(e)}")
        return False

def extract_signatures(pdf_path):
    signatures = {}
    try:
        doc = fitz.open(pdf_path)
        logger.debug(f"Processing PDF with {len(doc)} pages")
        
        for page_num in range(len(doc)):
            page = doc.load_page(page_num)
            
            # Get list of images on the page
            image_list = page.get_images(full=True)
            logger.debug(f"Page {page_num+1} has {len(image_list)} images")
            
            # First try extracting embedded images
            if image_list:
                for img_index, img in enumerate(image_list):
                    xref = img[0]
                    base_image = doc.extract_image(xref)
                    image_bytes = base_image["image"]
                    nparr = np.frombuffer(image_bytes, np.uint8)
                    img_cv = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
                    
                    if is_signature(img_cv):
                        signature_filename = f"signature_page_{page_num+1}_img{img_index}.png"
                        full_path = os.path.join(app.config['SIGNATURE_FOLDER'], signature_filename)
                        cv2.imwrite(full_path, img_cv)
                        signatures[f"Page {page_num+1}-Img{img_index}"] = os.path.join('static', 'signatures', signature_filename)
            
            # If no embedded images found, try rendering the page
            if not image_list or not signatures:
                pix = page.get_pixmap(dpi=150)
                img_bytes = pix.tobytes("png")
                nparr = np.frombuffer(img_bytes, np.uint8)
                img_cv = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
                
                if is_signature(img_cv):
                    signature_filename = f"signature_page_{page_num+1}_rendered.png"
                    full_path = os.path.join(app.config['SIGNATURE_FOLDER'], signature_filename)
                    cv2.imwrite(full_path, img_cv)
                    signatures[f"Page {page_num+1}"] = os.path.join('static', 'signatures', signature_filename)
        
        doc.close()
        logger.debug(f"Extracted {len(signatures)} signatures")
        
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
                'status': 'error',
                'message': 'No file provided'
            }), 400
        
        file = request.files['pdf_file']
        if file.filename == '':
            logger.debug("Empty filename")
            return jsonify({
                'status': 'error',
                'message': 'No file selected'
            }), 400
        
        if not (file and allowed_file(file.filename)):
            logger.debug(f"Invalid file type: {file.filename}")
            return jsonify({
                'status': 'error',
                'message': 'Invalid file type. Only PDF files are allowed.'
            }), 400
            
        filename = secure_filename(file.filename)
        filepath = os.path.join(app.config['UPLOAD_FOLDER'], filename)
        logger.debug(f"Saving file to {filepath}")
        
        file.save(filepath)
        logger.debug("File saved successfully, extracting signatures")
        
        signatures = extract_signatures(filepath)
        logger.debug(f"Signature extraction result: {signatures}")
        
        if not signatures:
            return jsonify({
                'status': 'success',
                'message': 'No signatures found in the PDF',
                'signatures': []
            }), 200
        
        # Convert paths to be accessible from web
        web_signatures = {
            key: request.host_url.rstrip('/') + '/' + path.lstrip('/')
            for key, path in signatures.items()
        }
        
        return jsonify({
            'status': 'success',
            'message': 'Signatures extracted successfully',
            'signatures': web_signatures,
            'count': len(signatures)
        }), 200
        
    except Exception as e:
        logger.error(f"Unexpected error: {str(e)}", exc_info=True)
        return jsonify({
            'status': 'error',
            'message': f"An error occurred: {str(e)}"
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