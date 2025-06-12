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
    """Detect if image contains an actual signature (very strict detection)"""
    try:
        if image is None or image.size == 0:
            return False
            
        h, w = image.shape[:2]
        
        # Skip very small or very large images (signatures are usually medium-sized)
        if w < 50 or h < 20 or w > 500 or h > 200:
            return False
            
        # Skip images that are too square (signatures are usually wider than tall)
        aspect_ratio = w / h
        if aspect_ratio < 1.5 or aspect_ratio > 8:
            return False
            
        # Convert to grayscale
        gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
        
        # Calculate background color (most common color)
        hist = cv2.calcHist([gray], [0], None, [256], [0, 256])
        background_color = np.argmax(hist)
        
        # If background is not white-ish (240-255), it's likely a logo/graphic
        if background_color < 240:
            return False
            
        # Apply adaptive thresholding
        thresh = cv2.adaptiveThreshold(gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
                                    cv2.THRESH_BINARY_INV, 11, 2)
        
        # Count non-zero pixels (ink)
        ink_pixels = cv2.countNonZero(thresh)
        total_pixels = thresh.size
        coverage = ink_pixels / total_pixels if total_pixels > 0 else 0
        
        # Signatures typically have very specific coverage ranges
        # Too low = noise, too high = text/graphics
        if coverage < 0.01 or coverage > 0.25:
            return False
            
        # Find contours
        contours, _ = cv2.findContours(thresh, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
        if not contours:
            return False
            
        # Filter out very small contours (noise)
        significant_contours = [c for c in contours if cv2.contourArea(c) > 20]
        if len(significant_contours) < 2:  # Signatures usually have multiple strokes
            return False
            
        # Check for logo-like patterns (geometric shapes)
        for contour in significant_contours:
            # Check if contour is too geometric (rectangles, circles)
            epsilon = 0.02 * cv2.arcLength(contour, True)
            approx = cv2.approxPolyDP(contour, epsilon, True)
            
            # If too many contours are geometric shapes, it's likely a logo
            if len(approx) <= 4:  # Rectangle or triangle
                return False
                
        # Check stroke characteristics
        total_area = sum(cv2.contourArea(c) for c in significant_contours)
        total_perimeter = sum(cv2.arcLength(c, True) for c in significant_contours)
        
        if total_area > 0 and total_perimeter > 0:
            # Signature strokes are usually more irregular than geometric shapes
            complexity_ratio = total_perimeter / np.sqrt(total_area)
            
            # Signatures have higher complexity (more irregular strokes)
            if complexity_ratio < 10:  # Too simple, likely geometric
                return False
                
        # Check for text-like patterns
        # Text usually has uniform height and spacing
        if len(significant_contours) > 5:
            bounding_rects = [cv2.boundingRect(c) for c in significant_contours]
            heights = [rect[3] for rect in bounding_rects]
            
            # If heights are too uniform, it's likely text
            if len(heights) > 1:
                height_std = np.std(heights)
                height_mean = np.mean(heights)
                if height_mean > 0:
                    height_variation = height_std / height_mean
                    if height_variation < 0.3:  # Too uniform = text
                        return False
        
        # Additional checks for common false positives
        
        # Check color distribution - logos often have uniform colors
        unique_colors = len(np.unique(gray))
        if unique_colors < 10:  # Too few colors, likely simple graphic
            return False
            
        # Check edge density - signatures have more varied edges
        edges = cv2.Canny(gray, 50, 150)
        edge_density = np.count_nonzero(edges) / edges.size
        
        if edge_density < 0.05 or edge_density > 0.4:  # Wrong edge density
            return False
            
        return True
        
    except Exception as e:
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

def analyze_image_details(image, image_index=0):
    """Analyze image details for debugging"""
    try:
        if image is None:
            return {"error": "Image is None"}
            
        h, w = image.shape[:2]
        gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
        
        # Basic metrics
        hist = cv2.calcHist([gray], [0], None, [256], [0, 256])
        background_color = np.argmax(hist)
        
        thresh = cv2.adaptiveThreshold(gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
                                    cv2.THRESH_BINARY_INV, 11, 2)
        ink_pixels = cv2.countNonZero(thresh)
        total_pixels = thresh.size
        coverage = ink_pixels / total_pixels if total_pixels > 0 else 0
        
        contours, _ = cv2.findContours(thresh, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
        significant_contours = [c for c in contours if cv2.contourArea(c) > 20]
        
        unique_colors = len(np.unique(gray))
        edges = cv2.Canny(gray, 50, 150)
        edge_density = np.count_nonzero(edges) / edges.size
        
        return {
            "image_index": image_index,
            "dimensions": f"{w}x{h}",
            "aspect_ratio": round(w/h, 2) if h > 0 else 0,
            "background_color": int(background_color),
            "ink_coverage": round(coverage, 4),
            "num_contours": len(significant_contours),
            "unique_colors": unique_colors,
            "edge_density": round(edge_density, 4),
            "likely_signature": is_signature(image)
        }
    except Exception as e:
        return {"error": str(e)}

def process_pdf_file(pdf_path, debug_mode=False):
    """Process PDF file and return accurate signature detection results"""
    signatures = {}
    debug_info = {}
    
    try:
        if not os.path.exists(pdf_path):
            raise FileNotFoundError(f"PDF file not found: {pdf_path}")
            
        doc = fitz.open(pdf_path)
        total_signatures = 0
        
        for page_num in range(len(doc)):
            page = doc.load_page(page_num)
            page_signatures = 0
            page_debug = []
            
            # 1. Check for filled form fields (actual signature fields with content)
            filled_fields = detect_filled_signature_fields(page)
            for field_idx, field in enumerate(filled_fields):
                signature_key = f"Page {page_num+1}-Field{field_idx}"
                signatures[signature_key] = {
                    'type': field['type'],
                    'page': page_num + 1,
                    'confidence': field['confidence'],
                    'field_name': field.get('field_name', 'Unknown'),
                    'content': field.get('value', 'Filled')
                }
                page_signatures += 1
            
            # 2. Check for embedded signature images (actual signature files)
            image_list = page.get_images(full=True)
            for img_index, img in enumerate(image_list):
                try:
                    xref = img[0]
                    base_image = doc.extract_image(xref)
                    image_bytes = base_image["image"]
                    nparr = np.frombuffer(image_bytes, np.uint8)
                    img_cv = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
                    
                    if img_cv is not None:
                        # Get image analysis for debugging
                        if debug_mode:
                            analysis = analyze_image_details(img_cv, img_index)
                            page_debug.append(analysis)
                        
                        # Only count as signature if it passes strict tests
                        if is_signature(img_cv):
                            signature_key = f"Page {page_num+1}-Image{img_index}"
                            signatures[signature_key] = {
                                'type': 'embedded_signature_image',
                                'page': page_num + 1,
                                'confidence': 'very_high',
                                'image_index': img_index
                            }
                            page_signatures += 1
                except Exception:
                    continue
            
            # 3. Only check for drawn signatures if no form fields or images found
            if page_signatures == 0:
                drawn_signatures = analyze_signature_areas(page)
                for area_idx, area in enumerate(drawn_signatures):
                    signature_key = f"Page {page_num+1}-Drawn{area_idx}"
                    signatures[signature_key] = {
                        'type': area['type'],
                        'page': page_num + 1,
                        'confidence': area['confidence'],
                        'bbox': area['bbox'],
                        'area': area['area']
                    }
                    page_signatures += 1
            
            if debug_mode and page_debug:
                debug_info[f"page_{page_num+1}"] = page_debug
            
            total_signatures += page_signatures
        
        doc.close()
        
        # Determine message based on findings
        if total_signatures == 0:
            message = "No signatures found - document appears to be unsigned"
        elif total_signatures == 1:
            message = "Found 1 signature - document is signed"
        else:
            message = f"Found {total_signatures} signatures - document is signed"
        
        result = {
            'success': True,
            'count': total_signatures,
            'signatures': signatures,
            'message': message,
            'analysis_method': 'strict_detection'
        }
        
        if debug_mode:
            result['debug_info'] = debug_info
            
        return result
        
    except Exception as e:
        return {
            'success': False,
            'error': str(e),
            'signatures': {},
            'count': 0,
            'message': 'Error processing PDF'
        }

# Command line processing function
def process_command_line():
    """Process PDF from command line arguments"""
    parser = argparse.ArgumentParser(description='PDF Signature Detection')
    parser.add_argument('--file', required=True, help='Path to PDF file')
    parser.add_argument('--output', help='Output JSON file path')
    parser.add_argument('--output-dir', help='Output directory for signature images')
    parser.add_argument('--no-base64', action='store_true', help='Do not include base64 images')
    parser.add_argument('--no-save', action='store_true', help='Do not save signature images')
    parser.add_argument('--quiet', action='store_true', help='Quiet mode')
    parser.add_argument('--debug', action='store_true', help='Debug mode - show image analysis details')
    
    args = parser.parse_args()
    
    if not os.path.exists(args.file):
        result = {
            'success': False,
            'error': f'File not found: {args.file}',
            'signatures': {},
            'count': 0
        }
    else:
        result = process_pdf_file(args.file, debug_mode=args.debug)
    
    # Output result
    if args.output:
        with open(args.output, 'w') as f:
            json.dump(result, f, indent=2)
    else:
        print(json.dumps(result, indent=2 if args.debug else None))
    
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
        print("  Server mode: python app.py --server")
        print("  CLI mode: python app.py --file <pdf_path> [--output <json_path>]")
        sys.exit(1)

if __name__ == '__main__':
    main()