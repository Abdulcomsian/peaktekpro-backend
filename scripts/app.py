import os
import logging
import base64
import tempfile
from flask import Flask, request, jsonify
from werkzeug.utils import secure_filename
import fitz  # PyMuPDF
import cv2
import numpy as np
from PIL import Image
import io

app = Flask(__name__)

# Configure logging
logging.basicConfig(level=logging.DEBUG)
logger = logging.getLogger(__name__)

class SignatureDetector:
    def __init__(self, temp_dir=None):
        self.temp_dir = temp_dir or tempfile.gettempdir()
        
    def is_signature_image(self, image):
        """Detect if an image contains a signature using computer vision"""
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
            
            # Signature heuristics
            logger.debug(f"Image analysis - Coverage: {coverage:.4f}, Aspect Ratio: {aspect_ratio:.2f}")
            return (0.001 < coverage < 0.5) and (0.3 < aspect_ratio < 8)
            
        except Exception as e:
            logger.error(f"Error in signature image detection: {str(e)}")
            return False
    
    def detect_filled_signature_fields(self, page):
        """Detect if signature fields are filled with text or drawings"""
        try:
            # Get form fields
            widgets = page.widgets()
            filled_fields = []
            
            for widget in widgets:
                if widget.field_type == fitz.PDF_WIDGET_TYPE_SIGNATURE:
                    # Check if signature field has content
                    if widget.field_value:
                        filled_fields.append({
                            'type': 'signature_field',
                            'field_name': widget.field_name,
                            'has_content': True,
                            'bbox': list(widget.rect)
                        })
                elif widget.field_type == fitz.PDF_WIDGET_TYPE_TEXT:
                    # Check text fields that might be signature-related
                    field_name = widget.field_name.lower() if widget.field_name else ""
                    if any(keyword in field_name for keyword in ['signature', 'sign', 'name']):
                        has_content = bool(widget.field_value and widget.field_value.strip())
                        if has_content:
                            filled_fields.append({
                                'type': 'text_field',
                                'field_name': widget.field_name,
                                'value': widget.field_value,
                                'bbox': list(widget.rect)
                            })
            
            return filled_fields
            
        except Exception as e:
            logger.error(f"Error detecting filled fields: {str(e)}")
            return []
    
    def detect_signature_areas(self, page):
        """Detect signature-like areas by analyzing the page content"""
        try:
            # Render page to image
            pix = page.get_pixmap(dpi=150)
            img_data = pix.tobytes("png")
            nparr = np.frombuffer(img_data, np.uint8)
            img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
            
            # Convert to grayscale
            gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
            
            # Apply threshold to find dark areas (potential signatures)
            _, thresh = cv2.threshold(gray, 240, 255, cv2.THRESH_BINARY_INV)
            
            # Find contours
            contours, _ = cv2.findContours(thresh, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
            
            signature_areas = []
            for contour in contours:
                area = cv2.contourArea(contour)
                if area > 100:  # Minimum area threshold
                    x, y, w, h = cv2.boundingRect(contour)
                    aspect_ratio = w / h if h > 0 else 0
                    
                    # Check if it looks like a signature
                    if 0.5 < aspect_ratio < 10 and 10 < w < 500 and 5 < h < 100:
                        # Extract the region
                        roi = img[y:y+h, x:x+w]
                        if self.is_signature_image(roi):
                            # Convert to base64 for API response
                            _, buffer = cv2.imencode('.png', roi)
                            img_base64 = base64.b64encode(buffer).decode('utf-8')
                            
                            signature_areas.append({
                                'type': 'signature_area',
                                'bbox': [x, y, x+w, y+h],
                                'confidence': 'medium',
                                'image_base64': img_base64
                            })
            
            return signature_areas
            
        except Exception as e:
            logger.error(f"Error detecting signature areas: {str(e)}")
            return []
    
    def extract_embedded_images(self, page, doc):
        """Extract and analyze embedded images in the page"""
        try:
            image_list = page.get_images(full=True)
            signatures = []
            
            for img_index, img in enumerate(image_list):
                try:
                    xref = img[0]
                    base_image = doc.extract_image(xref)
                    image_bytes = base_image["image"]
                    
                    # Convert to OpenCV format
                    nparr = np.frombuffer(image_bytes, np.uint8)
                    img_cv = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
                    
                    if img_cv is not None and self.is_signature_image(img_cv):
                        # Convert to base64
                        _, buffer = cv2.imencode('.png', img_cv)
                        img_base64 = base64.b64encode(buffer).decode('utf-8')
                        
                        signatures.append({
                            'type': 'embedded_image',
                            'image_index': img_index,
                            'confidence': 'high',
                            'image_base64': img_base64
                        })
                        
                except Exception as e:
                    logger.error(f"Error processing embedded image {img_index}: {str(e)}")
                    continue
            
            return signatures
            
        except Exception as e:
            logger.error(f"Error extracting embedded images: {str(e)}")
            return []
    
    def analyze_pdf(self, pdf_path):
        """Main method to analyze PDF for signatures"""
        try:
            doc = fitz.open(pdf_path)
            results = {
                'total_pages': len(doc),
                'signatures_found': 0,
                'pages': []
            }
            
            for page_num in range(len(doc)):
                page = doc.load_page(page_num)
                page_results = {
                    'page_number': page_num + 1,
                    'filled_fields': [],
                    'signature_areas': [],
                    'embedded_images': []
                }
                
                # Detect filled signature fields
                page_results['filled_fields'] = self.detect_filled_signature_fields(page)
                
                # Detect signature areas by image analysis
                page_results['signature_areas'] = self.detect_signature_areas(page)
                
                # Extract embedded images
                page_results['embedded_images'] = self.extract_embedded_images(page, doc)
                
                # Count total signatures found on this page
                page_signatures = (len(page_results['filled_fields']) + 
                                 len(page_results['signature_areas']) + 
                                 len(page_results['embedded_images']))
                
                page_results['signatures_count'] = page_signatures
                results['signatures_found'] += page_signatures
                results['pages'].append(page_results)
            
            doc.close()
            return results
            
        except Exception as e:
            logger.error(f"Error analyzing PDF: {str(e)}")
            raise

# Initialize detector
detector = SignatureDetector()

@app.route('/api/detect-signatures', methods=['POST'])
def detect_signatures():
    """API endpoint to detect signatures in PDF"""
    try:
        # Check if file is provided
        if 'pdf_file' not in request.files:
            return jsonify({
                'success': False,
                'error': 'No PDF file provided'
            }), 400
        
        file = request.files['pdf_file']
        if file.filename == '':
            return jsonify({
                'success': False,
                'error': 'No file selected'
            }), 400
        
        # Validate file type
        if not file.filename.lower().endswith('.pdf'):
            return jsonify({
                'success': False,
                'error': 'Invalid file type. Only PDF files are allowed.'
            }), 400
        
        # Save file temporarily
        filename = secure_filename(file.filename)
        temp_path = os.path.join(detector.temp_dir, filename)
        file.save(temp_path)
        
        try:
            # Analyze PDF
            results = detector.analyze_pdf(temp_path)
            
            # Clean up temporary file
            os.remove(temp_path)
            
            return jsonify({
                'success': True,
                'data': results,
                'message': f'Found {results["signatures_found"]} signature(s) in {results["total_pages"]} page(s)'
            })
            
        except Exception as e:
            # Clean up temporary file on error
            if os.path.exists(temp_path):
                os.remove(temp_path)
            raise
            
    except Exception as e:
        logger.error(f"API error: {str(e)}", exc_info=True)
        return jsonify({
            'success': False,
            'error': f'Processing error: {str(e)}'
        }), 500

@app.route('/api/detect-signatures-base64', methods=['POST'])
def detect_signatures_base64():
    """API endpoint to detect signatures in PDF sent as base64"""
    try:
        data = request.get_json()
        if not data or 'pdf_base64' not in data:
            return jsonify({
                'success': False,
                'error': 'No base64 PDF data provided'
            }), 400
        
        # Decode base64 PDF
        try:
            pdf_bytes = base64.b64decode(data['pdf_base64'])
        except Exception as e:
            return jsonify({
                'success': False,
                'error': 'Invalid base64 data'
            }), 400
        
        # Save to temporary file
        temp_path = os.path.join(detector.temp_dir, f"temp_{os.getpid()}.pdf")
        with open(temp_path, 'wb') as f:
            f.write(pdf_bytes)
        
        try:
            # Analyze PDF
            results = detector.analyze_pdf(temp_path)
            
            # Clean up temporary file
            os.remove(temp_path)
            
            return jsonify({
                'success': True,
                'data': results,
                'message': f'Found {results["signatures_found"]} signature(s) in {results["total_pages"]} page(s)'
            })
            
        except Exception as e:
            # Clean up temporary file on error
            if os.path.exists(temp_path):
                os.remove(temp_path)
            raise
            
    except Exception as e:
        logger.error(f"API error: {str(e)}", exc_info=True)
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

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5001, debug=True)