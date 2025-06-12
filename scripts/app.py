#!/usr/bin/env python3
import os
import logging
from flask import Flask, request, jsonify
from werkzeug.utils import secure_filename
import fitz  # PyMuPDF
import cv2
import numpy as np
import base64

# Initialize Flask app
app = Flask(__name__)

# Configuration
app.config.update({
    'UPLOAD_FOLDER': 'uploads',
    'SIGNATURE_FOLDER': 'static/signatures',
    'ALLOWED_EXTENSIONS': {'pdf'},
    'MAX_CONTENT_LENGTH': 16 * 1024 * 1024  # 16MB max file size
})

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.StreamHandler(),
        logging.FileHandler('signature_extractor.log')
    ]
)
logger = logging.getLogger(__name__)

def setup_directories():
    """Ensure required directories exist"""
    try:
        os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)
        os.makedirs(app.config['SIGNATURE_FOLDER'], exist_ok=True)
        logger.info("Directories setup complete")
    except Exception as e:
        logger.error(f"Directory setup failed: {str(e)}")
        raise

def allowed_file(filename):
    """Check if the file has an allowed extension"""
    return '.' in filename and \
           filename.rsplit('.', 1)[1].lower() in app.config['ALLOWED_EXTENSIONS']

def image_to_base64(image):
    """Convert OpenCV image to base64 string"""
    try:
        _, buffer = cv2.imencode('.png', image)
        return base64.b64encode(buffer).decode('utf-8')
    except Exception as e:
        logger.error(f"Image to base64 conversion failed: {str(e)}")
        return None

def is_signature(image):
    """Precise signature detection with improved heuristics"""
    try:
        if image is None:
            return False

        # Convert to grayscale
        gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
        
        # Apply adaptive thresholding
        thresh = cv2.adaptiveThreshold(
            gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
            cv2.THRESH_BINARY_INV, 11, 2
        )
        
        # Calculate ink coverage
        ink_pixels = cv2.countNonZero(thresh)
        coverage = ink_pixels / thresh.size
        
        # Find contours
        contours, _ = cv2.findContours(thresh, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
        if not contours:
            return False
            
        # Analyze largest contour
        largest_contour = max(contours, key=cv2.contourArea)
        x, y, w, h = cv2.boundingRect(largest_contour)
        aspect_ratio = w / max(h, 1)  # Avoid division by zero
        
        # Signature validation criteria
        valid_coverage = 0.005 < coverage < 0.3
        valid_aspect = 0.5 < aspect_ratio < 6
        valid_size = w > 50 and h > 20
        
        logger.debug(f"Signature check - Coverage: {coverage:.3f}, Aspect: {aspect_ratio:.2f}, Size: {w}x{h}")
        
        return valid_coverage and valid_aspect and valid_size
        
    except Exception as e:
        logger.error(f"Signature detection error: {str(e)}")
        return False

def extract_signatures(pdf_path):
    """Extract signatures from PDF with improved detection"""
    signatures = []
    
    try:
        if not os.path.exists(pdf_path):
            raise FileNotFoundError(f"PDF not found: {pdf_path}")
        
        doc = fitz.open(pdf_path)
        logger.info(f"Processing PDF with {len(doc)} pages")
        
        for page_num in range(len(doc)):
            page = doc.load_page(page_num)
            found_signatures = False
            
            # Process embedded images first
            for img_index, img in enumerate(page.get_images(full=True)):
                try:
                    xref = img[0]
                    base_image = doc.extract_image(xref)
                    img_data = np.frombuffer(base_image["image"], np.uint8)
                    img_cv = cv2.imdecode(img_data, cv2.IMREAD_COLOR)
                    
                    if img_cv is not None and is_signature(img_cv):
                        # Save signature image
                        filename = f"signature_page_{page_num+1}_img{img_index}.png"
                        filepath = os.path.join(app.config['SIGNATURE_FOLDER'], filename)
                        cv2.imwrite(filepath, img_cv)
                        
                        # Prepare response data
                        signature_data = {
                            'page': page_num + 1,
                            'type': 'embedded',
                            'width': img_cv.shape[1],
                            'height': img_cv.shape[0],
                            'file_path': filepath,
                            'image_url': f"/static/signatures/{filename}",
                            'image_data': f"data:image/png;base64,{image_to_base64(img_cv)}"
                        }
                        signatures.append(signature_data)
                        found_signatures = True
                        logger.info(f"Found signature on page {page_num+1}, image {img_index}")
                        
                except Exception as e:
                    logger.error(f"Error processing image {img_index} on page {page_num+1}: {str(e)}")
            
            # Fallback to page rendering if no embedded signatures found
            if not found_signatures:
                try:
                    pix = page.get_pixmap(dpi=150)
                    img_data = np.frombuffer(pix.tobytes("png"), np.uint8)
                    img_cv = cv2.imdecode(img_data, cv2.IMREAD_COLOR)
                    
                    if img_cv is not None and is_signature(img_cv):
                        filename = f"signature_page_{page_num+1}_rendered.png"
                        filepath = os.path.join(app.config['SIGNATURE_FOLDER'], filename)
                        cv2.imwrite(filepath, img_cv)
                        
                        signature_data = {
                            'page': page_num + 1,
                            'type': 'rendered',
                            'width': img_cv.shape[1],
                            'height': img_cv.shape[0],
                            'file_path': filepath,
                            'image_url': f"/static/signatures/{filename}",
                            'image_data': f"data:image/png;base64,{image_to_base64(img_cv)}"
                        }
                        signatures.append(signature_data)
                        logger.info(f"Found rendered signature on page {page_num+1}")
                        
                except Exception as e:
                    logger.error(f"Error rendering page {page_num+1}: {str(e)}")
        
        doc.close()
        return signatures
        
    except Exception as e:
        logger.error(f"PDF processing failed: {str(e)}", exc_info=True)
        raise

@app.route('/api/extract-signatures', methods=['POST'])
def handle_extraction():
    """API endpoint for signature extraction"""
    try:
        if 'pdf_file' not in request.files:
            return jsonify({'success': False, 'error': 'No file provided'}), 400
        
        file = request.files['pdf_file']
        if file.filename == '':
            return jsonify({'success': False, 'error': 'No file selected'}), 400
        
        if not allowed_file(file.filename):
            return jsonify({'success': False, 'error': 'Invalid file type'}), 400
        
        filename = secure_filename(file.filename)
        filepath = os.path.join(app.config['UPLOAD_FOLDER'], filename)
        file.save(filepath)
        
        signatures = extract_signatures(filepath)
        
        # Convert local paths to full URLs
        base_url = request.host_url.rstrip('/')
        for sig in signatures:
            sig['image_url'] = base_url + sig['image_url']
        
        return jsonify({
            'success': True,
            'count': len(signatures),
            'signatures': signatures
        })
        
    except Exception as e:
        logger.error(f"API error: {str(e)}", exc_info=True)
        return jsonify({'success': False, 'error': str(e)}), 500

@app.route('/api/health', methods=['GET'])
def health_check():
    return jsonify({'status': 'healthy', 'version': '1.0.0'})

if __name__ == '__main__':
    try:
        setup_directories()
        app.run(host='0.0.0.0', port=5001, debug=True)
    except Exception as e:
        logger.critical(f"Application failed to start: {str(e)}")
        raise