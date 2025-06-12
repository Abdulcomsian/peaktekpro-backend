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
    """Detect if image contains an actual signature (not just text labels)"""
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
            
        # Filter out very small contours (noise)
        significant_contours = [c for c in contours if cv2.contourArea(c) > 50]
        if not significant_contours:
            return False
            
        # Get largest contour
        largest_contour = max(significant_contours, key=cv2.contourArea)
        x, y, w, h = cv2.boundingRect(largest_contour)
        aspect_ratio = w / h if h > 0 else 0
        
        # Calculate how many separate contours we have (signatures tend to have multiple strokes)
        num_contours = len(significant_contours)
        
        # Check for text patterns vs signature patterns
        # Text usually has very regular spacing and uniform height
        if num_contours > 1:
            # Check if contours are too uniform (indicates text)
            heights = [cv2.boundingRect(c)[3] for c in significant_contours]
            height_variance = np.var(heights) if len(heights) > 1 else 0
            
            # If heights are too uniform, it's likely text
            if height_variance < 2 and len(heights) > 3:
                return False
        
        # Stricter signature heuristics to avoid text detection
        # Signatures typically have:
        # - Less regular coverage (not uniform like text)
        # - More varied stroke patterns
        # - Different aspect ratios than typical text
        
        # Reject if coverage is too low (likely just noise) or too high (likely text block)
        if coverage < 0.005 or coverage > 0.3:
            return False
            
        # Reject if aspect ratio is too extreme (very wide text labels)
        if aspect_ratio < 0.5 or aspect_ratio > 6:
            return False
            
        # Additional check: analyze stroke patterns
        # Signatures tend to have more curved/irregular strokes than text
        perimeter = cv2.arcLength(largest_contour, True)
        area = cv2.contourArea(largest_contour)
        
        if area > 0:
            # Compactness ratio - signatures tend to be less compact than text
            compactness = (perimeter * perimeter) / (4 * np.pi * area)
            # Text letters are usually more compact
            if compactness < 1.5:  # Too compact, likely text
                return False
        
        return True
        
    except Exception:
        return False

def detect_filled_signature_fields(page):
    """Detect actual filled signature fields (not just labels)"""
    filled_fields = []
    
    try:
        # Get form fields/widgets
        widgets = page.widgets()
        
        for widget in widgets:
            if widget.field_type == fitz.PDF_WIDGET_TYPE_SIGNATURE:
                # This is an actual signature field
                field_value = widget.field_value
                if field_value and len(field_value.strip()) > 0:
                    filled_fields.append({
                        'type': 'signature_field',
                        'field_name': widget.field_name or 'Unknown',
                        'has_content': True,
                        'bbox': list(widget.rect),
                        'confidence': 'high'
                    })
            elif widget.field_type in [fitz.PDF_WIDGET_TYPE_TEXT, fitz.PDF_WIDGET_TYPE_FREETEXT]:
                # Check if this text field might be signature-related and has content
                field_name = (widget.field_name or '').lower()
                field_value = widget.field_value or ''
                
                # Only consider fields that are clearly signature fields AND have actual content
                if (any(keyword in field_name for keyword in ['signature', 'sign', 'signed_by']) 
                    and field_value.strip() 
                    and len(field_value.strip()) > 2):  # Must have meaningful content
                    
                    filled_fields.append({
                        'type': 'text_signature_field',
                        'field_name': widget.field_name,
                        'value': field_value.strip(),
                        'bbox': list(widget.rect),
                        'confidence': 'medium'
                    })
        
        return filled_fields
        
    except Exception as e:
        logger.warning(f"Error detecting filled fields: {str(e)}")
        return []

def analyze_signature_areas(page):
    """Analyze page for actual signature drawings/marks (not text labels)"""
    signature_areas = []
    
    try:
        # Render page to image
        pix = page.get_pixmap(dpi=200)  # Higher DPI for better analysis
        img_data = pix.tobytes("png")
        nparr = np.frombuffer(img_data, np.uint8)
        img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
        
        if img is None:
            return []
        
        # Convert to grayscale
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        
        # Get text regions to exclude them
        text_blocks = page.get_text("dict")
        text_regions = []
        
        for block in text_blocks.get("blocks", []):
            if "lines" in block:
                for line in block["lines"]:
                    bbox = line.get("bbox", [0, 0, 0, 0])
                    text_content = ""
                    for span in line.get("spans", []):
                        text_content += span.get("text", "")
                    
                    # Skip regions that contain signature labels
                    if any(label in text_content.lower() for label in 
                          ['signature:', 'sign:', 'printed name:', 'date signed:', 'customer signature', 'company representative']):
                        text_regions.append({
                            'bbox': bbox,
                            'text': text_content
                        })
        
        # Apply threshold to find dark areas (potential signatures)
        _, thresh = cv2.threshold(gray, 230, 255, cv2.THRESH_BINARY_INV)
        
        # Find contours
        contours, _ = cv2.findContours(thresh, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
        
        for contour in contours:
            area = cv2.contourArea(contour)
            if area > 100:  # Minimum area threshold
                x, y, w, h = cv2.boundingRect(contour)
                
                # Skip if this region overlaps with known text labels
                is_text_region = False
                for text_region in text_regions:
                    tx, ty, tx2, ty2 = text_region['bbox']
                    # Convert PDF coordinates to image coordinates
                    tx_img = int(tx * img.shape[1] / pix.width)
                    ty_img = int(ty * img.shape[0] / pix.height)
                    tx2_img = int(tx2 * img.shape[1] / pix.width)
                    ty2_img = int(ty2 * img.shape[0] / pix.height)
                    
                    # Check for overlap
                    if not (x + w < tx_img or x > tx2_img or y + h < ty_img or y > ty2_img):
                        is_text_region = True
                        break
                
                if is_text_region:
                    continue
                
                aspect_ratio = w / h if h > 0 else 0
                
                # Check if it looks like a signature (not text)
                if 1.0 < aspect_ratio < 8 and 30 < w < 300 and 10 < h < 100:
                    # Extract the region and test if it's actually a signature
                    roi = img[y:y+h, x:x+w]
                    if is_signature(roi):
                        signature_areas.append({
                            'type': 'drawn_signature',
                            'bbox': [x, y, w, h],
                            'area': area,
                            'aspect_ratio': aspect_ratio,
                            'confidence': 'high'
                        })
        
        return signature_areas
        
    except Exception as e:
        logger.warning(f"Error analyzing signature areas: {str(e)}")
        return []
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