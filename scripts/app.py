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
    """Detect if image contains an actual handwritten signature (very strict)"""
    try:
        if image is None or image.size == 0:
            return False
            
        h, w = image.shape[:2]
        
        # Signatures must be reasonable size
        if w < 50 or h < 25 or w > 400 or h > 150:
            return False
            
        # Convert to grayscale
        gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
        
        # Calculate background color distribution
        hist = cv2.calcHist([gray], [0], None, [256], [0, 256])
        
        # Check if background is predominantly white/light
        light_pixels = np.sum(hist[230:256])  # Very light pixels
        total_pixels = gray.size
        light_ratio = light_pixels / total_pixels
        
        # Signatures should have mostly white background
        if light_ratio < 0.7:  # At least 70% light background
            return False
            
        # Apply multiple thresholding methods to detect ink
        thresh1 = cv2.adaptiveThreshold(gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
                                       cv2.THRESH_BINARY_INV, 11, 2)
        
        # Also try simple threshold for very dark ink
        _, thresh2 = cv2.threshold(gray, 200, 255, cv2.THRESH_BINARY_INV)
        
        # Combine both methods
        combined_thresh = cv2.bitwise_or(thresh1, thresh2)
        
        # Count ink pixels
        ink_pixels = cv2.countNonZero(combined_thresh)
        coverage = ink_pixels / total_pixels
        
        # Handwritten signatures have specific ink coverage
        if coverage < 0.008 or coverage > 0.3:  # Very specific range
            return False
            
        # Find contours (strokes)
        contours, _ = cv2.findContours(combined_thresh, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
        if not contours:
            return False
            
        # Filter meaningful contours
        significant_contours = [c for c in contours if cv2.contourArea(c) > 20]
        if len(significant_contours) < 2:  # Signatures need multiple strokes
            return False
            
        # Check for geometric/logo patterns
        geometric_count = 0
        for contour in significant_contours:
            # Approximate contour to polygon
            epsilon = 0.02 * cv2.arcLength(contour, True)
            approx = cv2.approxPolyDP(contour, epsilon, True)
            
            # Count geometric shapes (rectangles, triangles, etc.)
            if len(approx) <= 8:  # Too geometric
                geometric_count += 1
        
        geometric_ratio = geometric_count / len(significant_contours)
        if geometric_ratio > 0.5:  # Too many geometric shapes
            return False
            
        # Analyze stroke characteristics for handwriting
        stroke_complexity = []
        for contour in significant_contours:
            if cv2.contourArea(contour) > 30:
                # Calculate complexity metrics
                perimeter = cv2.arcLength(contour, True)
                area = cv2.contourArea(contour)
                
                if area > 0:
                    # Compactness - handwriting is less compact than printed shapes
                    compactness = (perimeter * perimeter) / (4 * np.pi * area)
                    stroke_complexity.append(compactness)
        
        if stroke_complexity:
            avg_complexity = np.mean(stroke_complexity)
            # Handwritten strokes are more complex (irregular)
            if avg_complexity < 2.0:  # Too simple/geometric
                return False
        
        # Check for text-like patterns
        if len(significant_contours) > 3:
            bounding_rects = [cv2.boundingRect(c) for c in significant_contours]
            
            # Check height uniformity (text has uniform height)
            heights = [rect[3] for rect in bounding_rects]
            if len(heights) > 2:
                height_variation = np.std(heights) / np.mean(heights) if np.mean(heights) > 0 else 0
                if height_variation < 0.25:  # Too uniform = text
                    return False
            
            # Check horizontal alignment (text is horizontally aligned)
            y_positions = [rect[1] for rect in bounding_rects]
            if len(y_positions) > 2:
                y_variation = np.std(y_positions)
                if y_variation < 5:  # Too aligned = text
                    return False
        
        # Advanced signature characteristics
        
        # 1. Density variation across image (signatures have varied density)
        if w > 30 and h > 20:
            # Check density in different regions
            regions = [
                combined_thresh[0:h//2, 0:w//2],           # Top-left
                combined_thresh[0:h//2, w//2:w],           # Top-right
                combined_thresh[h//2:h, 0:w//2],           # Bottom-left
                combined_thresh[h//2:h, w//2:w]            # Bottom-right
            ]
            
            densities = []
            for region in regions:
                if region.size > 0:
                    region_density = np.count_nonzero(region) / region.size
                    densities.append(region_density)
            
            if len(densities) > 1:
                density_std = np.std(densities)
                # Signatures should have some density variation
                if density_std < 0.005:  # Too uniform
                    return False
        
        # 2. Edge complexity (signatures have complex edges)
        edges = cv2.Canny(gray, 50, 150)
        edge_density = np.count_nonzero(edges) / edges.size
        
        if edge_density < 0.03 or edge_density > 0.5:  # Wrong edge complexity
            return False
        
        # 3. Aspect ratio check (signatures are usually wider than tall)
        overall_aspect = w / h
        if overall_aspect < 1.2 or overall_aspect > 8:  # Signatures are typically wide
            return False
        
        # 4. Check for template/form elements by analyzing regularity
        # Templates often have very regular spacing and shapes
        if len(significant_contours) > 5:
            areas = [cv2.contourArea(c) for c in significant_contours]
            if len(areas) > 2:
                area_variation = np.std(areas) / np.mean(areas) if np.mean(areas) > 0 else 0
                if area_variation < 0.3:  # Too uniform = template
                    return False
        
        # 5. Final handwriting verification
        # Check for curved strokes (handwriting has curves)
        curved_strokes = 0
        for contour in significant_contours:
            if len(contour) > 10:  # Enough points to analyze
                # Check curvature by comparing contour to its convex hull
                hull = cv2.convexHull(contour)
                hull_area = cv2.contourArea(hull)
                contour_area = cv2.contourArea(contour)
                
                if hull_area > 0:
                    solidity = contour_area / hull_area
                    if solidity < 0.8:  # More irregular = more likely handwritten
                        curved_strokes += 1
        
        # Require some curved/irregular strokes
        if len(significant_contours) > 0:
            curved_ratio = curved_strokes / len(significant_contours)
            if curved_ratio < 0.3:  # Not enough irregular strokes
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
    """Analyze page for actual signature drawings/marks with improved detection"""
    signature_areas = []
    
    try:
        # Render page to image with higher resolution for better detection
        pix = page.get_pixmap(dpi=300)
        img_data = pix.tobytes("png")
        nparr = np.frombuffer(img_data, np.uint8)
        img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
        
        if img is None:
            return []
        
        # Convert to grayscale
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        
        # Get text regions to exclude them - be more comprehensive
        text_blocks = page.get_text("dict")
        text_regions = []
        
        for block in text_blocks.get("blocks", []):
            if "lines" in block:
                for line in block["lines"]:
                    bbox = line.get("bbox", [0, 0, 0, 0])
                    text_content = ""
                    for span in line.get("spans", []):
                        text_content += span.get("text", "")
                    
                    # Expand the list of text patterns to exclude
                    text_indicators = [
                        'signature:', 'sign:', 'printed name:', 'date signed:', 
                        'customer signature', 'company representative', 'name:',
                        'date:', 'title:', 'witness:', 'notary:', 'acknowledgment',
                        'terms:', 'conditions:', 'agreement', 'contract', 'policy',
                        'insurance', 'claim', 'customer', 'company', 'page', 'of'
                    ]
                    
                    if any(indicator in text_content.lower() for indicator in text_indicators):
                        # Expand the text region slightly to avoid nearby signature detection
                        expanded_bbox = [
                            bbox[0] - 20, bbox[1] - 10,
                            bbox[2] + 20, bbox[3] + 10
                        ]
                        text_regions.append({
                            'bbox': expanded_bbox,
                            'text': text_content
                        })
        
        # Multiple threshold approaches for different types of signatures
        signature_candidates = []
        
        # Method 1: Standard threshold for dark ink signatures
        _, thresh1 = cv2.threshold(gray, 230, 255, cv2.THRESH_BINARY_INV)
        contours1, _ = cv2.findContours(thresh1, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
        signature_candidates.extend(contours1)
        
        # Method 2: Adaptive threshold for varying lighting
        thresh2 = cv2.adaptiveThreshold(gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
                                       cv2.THRESH_BINARY_INV, 15, 8)
        contours2, _ = cv2.findContours(thresh2, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
        signature_candidates.extend(contours2)
        
        # Method 3: Edge-based detection for light signatures
        edges = cv2.Canny(gray, 30, 100)
        contours3, _ = cv2.findContours(edges, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
        signature_candidates.extend(contours3)
        
        # Remove duplicates and analyze candidates
        processed_areas = set()
        
        for contour in signature_candidates:
            area = cv2.contourArea(contour)
            if area < 200 or area > 50000:  # Filter by area
                continue
                
            x, y, w, h = cv2.boundingRect(contour)
            
            # Skip if already processed this area
            area_key = (x//10, y//10, w//10, h//10)  # Rough grouping
            if area_key in processed_areas:
                continue
            processed_areas.add(area_key)
            
            # Skip if this region overlaps with known text
            is_text_region = False
            for text_region in text_regions:
                tx, ty, tx2, ty2 = text_region['bbox']
                # Convert PDF coordinates to image coordinates
                scale_x = img.shape[1] / pix.width
                scale_y = img.shape[0] / pix.height
                tx_img = int(tx * scale_x)
                ty_img = int(ty * scale_y)
                tx2_img = int(tx2 * scale_x)
                ty2_img = int(ty2 * scale_y)
                
                # Check for overlap with expanded margin
                margin = 30
                if not (x + w < tx_img - margin or x > tx2_img + margin or 
                       y + h < ty_img - margin or y > ty2_img + margin):
                    is_text_region = True
                    break
            
            if is_text_region:
                continue
            
            # Check aspect ratio and size constraints
            aspect_ratio = w / h if h > 0 else 0
            if aspect_ratio < 0.3 or aspect_ratio > 15:  # Very lenient aspect ratio
                continue
                
            if w < 40 or h < 15 or w > 400 or h > 200:  # Size constraints
                continue
            
            # Extract the region and test if it contains signature-like content
            roi = img[max(0, y-5):min(img.shape[0], y+h+5), 
                     max(0, x-5):min(img.shape[1], x+w+5)]
            
            if roi.size > 0 and is_signature(roi):
                signature_areas.append({
                    'type': 'drawn_signature',
                    'bbox': [x, y, w, h],
                    'area': area,
                    'aspect_ratio': aspect_ratio,
                    'confidence': 'medium'
                })
        
        return signature_areas
        
    except Exception as e:
        logger.warning(f"Error analyzing signature areas: {str(e)}")
        return []

def analyze_image_details(image, image_index=0):
    """Analyze image details for debugging with detailed rejection reasons"""
    try:
        if image is None:
            return {"error": "Image is None"}
            
        h, w = image.shape[:2]
        gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
        
        # Basic metrics
        hist = cv2.calcHist([gray], [0], None, [256], [0, 256])
        light_pixels = np.sum(hist[230:256])
        light_ratio = light_pixels / gray.size
        
        # Thresholding
        thresh1 = cv2.adaptiveThreshold(gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
                                       cv2.THRESH_BINARY_INV, 11, 2)
        _, thresh2 = cv2.threshold(gray, 200, 255, cv2.THRESH_BINARY_INV)
        combined_thresh = cv2.bitwise_or(thresh1, thresh2)
        
        ink_pixels = cv2.countNonZero(combined_thresh)
        coverage = ink_pixels / gray.size
        
        # Contour analysis
        contours, _ = cv2.findContours(combined_thresh, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
        significant_contours = [c for c in contours if cv2.contourArea(c) > 20]
        
        # Geometric analysis
        geometric_count = 0
        for contour in significant_contours:
            epsilon = 0.02 * cv2.arcLength(contour, True)
            approx = cv2.approxPolyDP(contour, epsilon, True)
            if len(approx) <= 8:
                geometric_count += 1
        
        geometric_ratio = geometric_count / len(significant_contours) if significant_contours else 0
        
        # Stroke complexity
        avg_complexity = 0
        if significant_contours:
            complexities = []
            for contour in significant_contours:
                if cv2.contourArea(contour) > 30:
                    perimeter = cv2.arcLength(contour, True)
                    area = cv2.contourArea(contour)
                    if area > 0:
                        compactness = (perimeter * perimeter) / (4 * np.pi * area)
                        complexities.append(compactness)
            avg_complexity = np.mean(complexities) if complexities else 0
        
        # Edge analysis
        edges = cv2.Canny(gray, 50, 150)
        edge_density = np.count_nonzero(edges) / edges.size
        
        # Aspect ratio
        aspect_ratio = w / h if h > 0 else 0
        
        # Curvature analysis
        curved_strokes = 0
        for contour in significant_contours:
            if len(contour) > 10:
                hull = cv2.convexHull(contour)
                hull_area = cv2.contourArea(hull)
                contour_area = cv2.contourArea(contour)
                if hull_area > 0:
                    solidity = contour_area / hull_area
                    if solidity < 0.8:
                        curved_strokes += 1
        
        curved_ratio = curved_strokes / len(significant_contours) if significant_contours else 0
        
        # Detailed rejection analysis
        rejection_reasons = []
        rejection_details = {}
        
        if w < 50 or h < 25 or w > 400 or h > 150:
            rejection_reasons.append("size_out_of_range")
            rejection_details["size_check"] = f"Size {w}x{h} not in range 50-400 x 25-150"
            
        if light_ratio < 0.7:
            rejection_reasons.append("background_not_light_enough")
            rejection_details["light_ratio"] = f"Light background ratio {light_ratio:.3f} < 0.7"
            
        if coverage < 0.008 or coverage > 0.3:
            rejection_reasons.append("ink_coverage_out_of_range")
            rejection_details["coverage"] = f"Ink coverage {coverage:.4f} not in range 0.008-0.3"
            
        if len(significant_contours) < 2:
            rejection_reasons.append("too_few_strokes")
            rejection_details["strokes"] = f"Only {len(significant_contours)} strokes, need 2+"
            
        if geometric_ratio > 0.5:
            rejection_reasons.append("too_geometric")
            rejection_details["geometric"] = f"Geometric ratio {geometric_ratio:.3f} > 0.5"
            
        if avg_complexity < 2.0 and avg_complexity > 0:
            rejection_reasons.append("strokes_too_simple")
            rejection_details["complexity"] = f"Average stroke complexity {avg_complexity:.2f} < 2.0"
            
        if edge_density < 0.03 or edge_density > 0.5:
            rejection_reasons.append("edge_density_wrong")
            rejection_details["edge_density"] = f"Edge density {edge_density:.4f} not in range 0.03-0.5"
            
        if aspect_ratio < 1.2 or aspect_ratio > 8:
            rejection_reasons.append("aspect_ratio_wrong")
            rejection_details["aspect_ratio"] = f"Aspect ratio {aspect_ratio:.2f} not in range 1.2-8"
            
        if curved_ratio < 0.3:
            rejection_reasons.append("not_enough_curved_strokes")
            rejection_details["curved_ratio"] = f"Curved stroke ratio {curved_ratio:.3f} < 0.3"
        
        # Check for text patterns
        text_like = False
        if len(significant_contours) > 3:
            bounding_rects = [cv2.boundingRect(c) for c in significant_contours]
            heights = [rect[3] for rect in bounding_rects]
            y_positions = [rect[1] for rect in bounding_rects]
            
            if len(heights) > 2:
                height_variation = np.std(heights) / np.mean(heights) if np.mean(heights) > 0 else 0
                if height_variation < 0.25:
                    text_like = True
                    rejection_reasons.append("text_like_uniform_height")
                    rejection_details["height_variation"] = f"Height variation {height_variation:.3f} < 0.25"
                    
            if len(y_positions) > 2:
                y_variation = np.std(y_positions)
                if y_variation < 5:
                    text_like = True
                    rejection_reasons.append("text_like_horizontal_alignment")
                    rejection_details["y_variation"] = f"Y variation {y_variation:.1f} < 5"
        
        return {
            "image_index": image_index,
            "dimensions": f"{w}x{h}",
            "aspect_ratio": round(aspect_ratio, 2),
            "light_background_ratio": round(light_ratio, 3),
            "ink_coverage": round(coverage, 4),
            "num_contours": len(significant_contours),
            "geometric_ratio": round(geometric_ratio, 3),
            "avg_stroke_complexity": round(avg_complexity, 2),
            "edge_density": round(edge_density, 4),
            "curved_stroke_ratio": round(curved_ratio, 3),
            "text_like": text_like,
            "likely_signature": is_signature(image),
            "rejection_reasons": rejection_reasons,
            "rejection_details": rejection_details
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
            'analysis_method': 'balanced_detection'
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