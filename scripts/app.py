#!/usr/bin/env python3
import os
import sys
import logging
import json
import tempfile
import argparse
from flask import Flask, request, jsonify
from werkzeug.utils import secure_filename
import fitz  # PyMuPDF
import cv2
import numpy as np

# Configure logging to be less verbose
logging.basicConfig(level=logging.WARNING, format='%(levelname)s: %(message)s')
logger = logging.getLogger(__name__)

app = Flask(__name__)

# Use temp directory to avoid permission issues
TEMP_DIR = tempfile.gettempdir()
UPLOAD_FOLDER = os.path.join(TEMP_DIR, 'pdf_uploads')
SIGNATURE_FOLDER = os.path.join(TEMP_DIR, 'signatures')

# Create directories
os.makedirs(UPLOAD_FOLDER, exist_ok=True)
os.makedirs(SIGNATURE_FOLDER, exist_ok=True)

def allowed_file(filename):
    return '.' in filename and filename.rsplit('.', 1)[1].lower() == 'pdf'

def is_signature(image):
    """Detect if image contains a signature"""
    try:
        if image is None or image.size == 0:
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
        
        # Signature heuristics
        return (0.001 < coverage < 0.5) and (0.3 < aspect_ratio < 8)
        
    except Exception:
        return False

def process_pdf_file(pdf_path):
    """Process PDF file and return signature detection results"""
    signatures = {}
    
    try:
        if not os.path.exists(pdf_path):
            raise FileNotFoundError(f"PDF file not found: {pdf_path}")
            
        doc = fitz.open(pdf_path)
        
        for page_num in range(len(doc)):
            page = doc.load_page(page_num)
            
            # Get embedded images
            image_list = page.get_images(full=True)
            
            # Check embedded images
            for img_index, img in enumerate(image_list):
                try:
                    xref = img[0]
                    base_image = doc.extract_image(xref)
                    image_bytes = base_image["image"]
                    nparr = np.frombuffer(image_bytes, np.uint8)
                    img_cv = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
                    
                    if img_cv is not None and is_signature(img_cv):
                        signature_key = f"Page {page_num+1}-Img{img_index}"
                        signatures[signature_key] = {
                            'type': 'embedded_image',
                            'page': page_num + 1,
                            'confidence': 'high'
                        }
                except Exception:
                    continue
            
            # If no embedded images, check rendered page
            if not image_list:
                try:
                    pix = page.get_pixmap(dpi=150)
                    img_bytes = pix.tobytes("png")
                    nparr = np.frombuffer(img_bytes, np.uint8)
                    img_cv = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
                    
                    if img_cv is not None and is_signature(img_cv):
                        signature_key = f"Page {page_num+1}"
                        signatures[signature_key] = {
                            'type': 'page_content',
                            'page': page_num + 1,
                            'confidence': 'medium'
                        }
                except Exception:
                    continue
        
        doc.close()
        
        return {
            'success': True,
            'signatures': signatures,
            'count': len(signatures),
            'message': f'Found {len(signatures)} signature(s)' if signatures else 'No signatures found'
        }
        
    except Exception as e:
        return {
            'success': False,
            'error': str(e),
            'signatures': {},
            'count': 0
        }

# Command line processing function
def process_command_line():
    """Process PDF from command line arguments"""
    parser = argparse.ArgumentParser(description='PDF Signature Detection')
    parser.add_argument('--file', required=True, help='Path to PDF file')
    parser.add_argument('--output', help='Output JSON file path')
    
    args = parser.parse_args()
    
    if not os.path.exists(args.file):
        result = {
            'success': False,
            'error': f'File not found: {args.file}',
            'signatures': {},
            'count': 0
        }
    else:
        result = process_pdf_file(args.file)
    
    # Output result
    if args.output:
        with open(args.output, 'w') as f:
            json.dump(result, f, indent=2)
    else:
        print(json.dumps(result))
    
    # Exit with appropriate code
    sys.exit(0 if result['success'] else 1)

# Flask API routes
@app.route('/api/detect-signatures', methods=['POST'])
def detect_signatures():
    """API endpoint to detect signatures in PDF"""
    try:
        if 'pdf_file' not in request.files:
            return jsonify({
                'success': False,
                'error': 'No PDF file provided'
            }), 400
        
        file = request.files['pdf_file']
        if file.filename == '' or not allowed_file(file.filename):
            return jsonify({
                'success': False,
                'error': 'Invalid file'
            }), 400
        
        # Save file temporarily
        filename = secure_filename(file.filename)
        filepath = os.path.join(UPLOAD_FOLDER, filename)
        file.save(filepath)
        
        # Process file
        result = process_pdf_file(filepath)
        
        # Clean up
        try:
            os.remove(filepath)
        except:
            pass
        
        return jsonify(result)
        
    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/api/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({
        'success': True,
        'message': 'PDF Signature Detection API is running'
    })

def main():
    """Main function to handle both server and command-line modes"""
    
    # Check if this is being run as a command-line tool
    if len(sys.argv) > 1 and '--file' in sys.argv:
        process_command_line()
        return
    
    # Check if this is being run as a server
    if len(sys.argv) > 1 and '--server' in sys.argv:
        print("Starting PDF Signature Detection API server...")
        app.run(host='0.0.0.0', port=5001, debug=False)
        return
    
    # Default behavior - try to determine context
    # If no arguments, assume server mode
    if len(sys.argv) == 1:
        print("Starting PDF Signature Detection API server...")
        try:
            app.run(host='0.0.0.0', port=5001, debug=False)
        except Exception as e:
            print(f"Server failed to start: {e}")
            sys.exit(1)
    else:
        print("Usage:")
        print("  Server mode: python pdf_signature_api.py --server")
        print("  CLI mode: python pdf_signature_api.py --file <pdf_path> [--output <json_path>]")
        sys.exit(1)

if __name__ == '__main__':
    main()