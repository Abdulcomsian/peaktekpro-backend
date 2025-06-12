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
from datetime import datetime

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

def detect_digital_signatures(doc):
    """Detect cryptographic digital signatures in the PDF"""
    digital_sigs = []
    
    try:
        # Check if document has any digital signatures
        if doc.is_signed:
            # Get signature information
            sig_dict = doc.get_sigflags()
            digital_sigs.append({
                'type': 'digital_signature',
                'status': 'document_signed',
                'flags': sig_dict
            })
    except Exception as e:
        logger.debug(f"Error checking digital signatures: {e}")
    
    return digital_sigs

def detect_form_signatures(page):
    """Detect signature fields in PDF forms with improved detection"""
    signatures = []
    
    try:
        widgets = page.widgets()
        
        for widget in widgets:
            widget_info = {
                'field_name': widget.field_name or '',
                'field_type': widget.field_type,
                'field_value': widget.field_value or '',
                'rect': list(widget.rect) if widget.rect else None
            }
            
            # Check for signature widget fields
            if widget.field_type == fitz.PDF_WIDGET_TYPE_SIGNATURE:
                # This is a dedicated signature field
                is_signed = False
                signature_data = None
                
                # Check if the signature field contains data
                if widget.field_value:
                    is_signed = True
                    signature_data = widget.field_value
                
                # Also check for appearance stream (visual representation)
                if hasattr(widget, '_annot') and widget._annot:
                    ap = widget._annot.get_ap()
                    if ap:
                        is_signed = True
                
                signatures.append({
                    'type': 'signature_field',
                    'field_name': widget_info['field_name'],
                    'is_signed': is_signed,
                    'has_appearance': ap is not None if hasattr(widget, '_annot') and widget._annot else False,
                    'bbox': widget_info['rect'],
                    'confidence': 'high' if is_signed else 'field_empty'
                })
            
            # Check text fields that might contain signatures
            elif widget.field_type in [fitz.PDF_WIDGET_TYPE_TEXT, fitz.PDF_WIDGET_TYPE_FREETEXT]:
                field_name_lower = widget_info['field_name'].lower()
                field_value = widget_info['field_value'].strip()
                
                # Look for signature-related field names
                signature_keywords = ['signature', 'sign', 'signed', 'authorized', 'approval', 
                                    'endorsed', 'witness', 'notary', 'initial']
                
                if any(keyword in field_name_lower for keyword in signature_keywords):
                    # Check if field has actual content
                    if field_value and len(field_value) > 1:
                        # Analyze the content
                        is_likely_signature = False
                        
                        # Check if it's not just placeholder text
                        placeholder_indicators = ['type here', 'sign here', 'your signature', 
                                                'your name', 'n/a', 'na', '--', '___']
                        
                        if not any(indicator in field_value.lower() for indicator in placeholder_indicators):
                            # Check for name-like patterns (signatures often contain names)
                            if len(field_value.split()) <= 4:  # Most signatures are 1-4 words
                                is_likely_signature = True
                        
                        if is_likely_signature:
                            signatures.append({
                                'type': 'text_signature_field',
                                'field_name': widget_info['field_name'],
                                'value': field_value,
                                'bbox': widget_info['rect'],
                                'confidence': 'medium'
                            })
            
            # Check for checkbox fields that might indicate signature presence
            elif widget.field_type == fitz.PDF_WIDGET_TYPE_CHECKBOX:
                field_name_lower = widget_info['field_name'].lower()
                if any(keyword in field_name_lower for keyword in ['signed', 'agree', 'acknowledge']):
                    if widget.field_value and widget.field_value.lower() in ['on', 'yes', 'true', '1']:
                        signatures.append({
                            'type': 'signature_checkbox',
                            'field_name': widget_info['field_name'],
                            'is_checked': True,
                            'bbox': widget_info['rect'],
                            'confidence': 'low'
                        })
        
        return signatures
        
    except Exception as e:
        logger.warning(f"Error detecting form signatures: {str(e)}")
        return []

def detect_annotation_signatures(page):
    """Detect signatures added as annotations (stamps, ink, etc.)"""
    signatures = []
    
    try:
        for annot in page.annots():
            annot_type = annot.type[0] if annot.type else None
            
            # Check for ink annotations (drawn signatures)
            if annot_type == fitz.PDF_ANNOT_INK:
                signatures.append({
                    'type': 'ink_annotation',
                    'subtype': 'drawn_signature',
                    'rect': list(annot.rect),
                    'confidence': 'high',
                    'author': annot.info.get('title', 'Unknown')
                })
            
            # Check for stamp annotations (signature stamps)
            elif annot_type == fitz.PDF_ANNOT_STAMP:
                content = annot.info.get('content', '').lower()
                subject = annot.info.get('subject', '').lower()
                
                if any(keyword in content + subject for keyword in ['signature', 'signed', 'approved']):
                    signatures.append({
                        'type': 'stamp_annotation',
                        'subtype': 'signature_stamp',
                        'rect': list(annot.rect),
                        'confidence': 'high',
                        'content': annot.info.get('content', '')
                    })
            
            # Check for text annotations that might indicate signature
            elif annot_type == fitz.PDF_ANNOT_TEXT:
                content = annot.info.get('content', '').lower()
                if any(keyword in content for keyword in ['signed', 'signature', 'approved by']):
                    signatures.append({
                        'type': 'text_annotation',
                        'subtype': 'signature_note',
                        'rect': list(annot.rect),
                        'confidence': 'low',
                        'content': annot.info.get('content', '')
                    })
    
    except Exception as e:
        logger.warning(f"Error detecting annotation signatures: {str(e)}")
    
    return signatures

def analyze_signature_images(page, strict_mode=True):
    """Analyze images that might be signatures with configurable strictness"""
    signatures = []
    
    try:
        # Get images from the page
        image_list = page.get_images(full=True)
        
        for img_index, img in enumerate(image_list):
            try:
                xref = img[0]
                base_image = page.parent.extract_image(xref)
                image_bytes = base_image["image"]
                
                # Convert to opencv format
                nparr = np.frombuffer(image_bytes, np.uint8)
                img_cv = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
                
                if img_cv is None:
                    continue
                
                h, w = img_cv.shape[:2]
                
                # Basic size filtering for signature-like dimensions
                if not (30 <= w <= 500 and 20 <= h <= 200):
                    continue
                
                # Check aspect ratio (signatures are typically wider than tall)
                aspect_ratio = w / h
                if not (1.5 <= aspect_ratio <= 8):
                    continue
                
                # Get image position on page
                # This requires checking where the image is used on the page
                img_rect = None
                for item in page.get_text("dict")["blocks"]:
                    if item.get("type") == 1:  # Image block
                        if item.get("xref") == xref:
                            img_rect = item.get("bbox")
                            break
                
                # Analyze image content
                gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
                
                # Check if it's mostly white/light background
                mean_val = np.mean(gray)
                if mean_val < 200:  # Too dark for typical signature
                    continue
                
                # Simple signature detection based on contrast
                _, binary = cv2.threshold(gray, 200, 255, cv2.THRESH_BINARY_INV)
                ink_ratio = np.count_nonzero(binary) / binary.size
                
                # Signatures typically have 1-15% ink coverage
                if 0.01 <= ink_ratio <= 0.15:
                    signatures.append({
                        'type': 'embedded_image',
                        'subtype': 'possible_signature',
                        'image_index': img_index,
                        'dimensions': f"{w}x{h}",
                        'aspect_ratio': round(aspect_ratio, 2),
                        'ink_coverage': round(ink_ratio, 3),
                        'rect': img_rect,
                        'confidence': 'medium' if strict_mode else 'low'
                    })
                    
            except Exception as e:
                logger.debug(f"Error processing image {img_index}: {e}")
                continue
    
    except Exception as e:
        logger.warning(f"Error analyzing signature images: {str(e)}")
    
    return signatures

def detect_text_signatures(page):
    """Detect signature-like text patterns"""
    signatures = []
    
    try:
        # Get all text blocks
        blocks = page.get_text("dict")["blocks"]
        
        for block in blocks:
            if block.get("type") == 0:  # Text block
                for line in block.get("lines", []):
                    text = ""
                    for span in line.get("spans", []):
                        text += span.get("text", "")
                    
                    text = text.strip()
                    if not text:
                        continue
                    
                    # Check for signature indicators
                    signature_patterns = [
                        r'/s/\s*\w+',  # /s/ followed by name
                        r'signed:\s*\w+',  # "Signed: Name"
                        r'signature:\s*\w+',  # "Signature: Name"
                        r'approved by:\s*\w+',  # "Approved by: Name"
                        r'authorized by:\s*\w+',  # "Authorized by: Name"
                    ]
                    
                    import re
                    for pattern in signature_patterns:
                        if re.search(pattern, text, re.IGNORECASE):
                            signatures.append({
                                'type': 'text_signature',
                                'text': text,
                                'bbox': line.get("bbox"),
                                'confidence': 'medium'
                            })
                            break
                    
                    # Check for date near potential signatures
                    date_patterns = [
                        r'\d{1,2}/\d{1,2}/\d{2,4}',
                        r'\d{1,2}-\d{1,2}-\d{2,4}',
                        r'\d{4}-\d{2}-\d{2}'
                    ]
                    
                    for pattern in date_patterns:
                        if re.search(pattern, text):
                            # This might be a signature date
                            bbox = line.get("bbox")
                            if bbox:
                                signatures.append({
                                    'type': 'signature_date',
                                    'text': text,
                                    'bbox': bbox,
                                    'confidence': 'low'
                                })
                            break
    
    except Exception as e:
        logger.warning(f"Error detecting text signatures: {str(e)}")
    
    return signatures

def process_pdf_file(pdf_path, debug_mode=False, strict_mode=True):
    """Process PDF file with comprehensive signature detection"""
    all_signatures = {}
    debug_info = {}
    
    try:
        if not os.path.exists(pdf_path):
            raise FileNotFoundError(f"PDF file not found: {pdf_path}")
        
        doc = fitz.open(pdf_path)
        
        # Check for digital signatures first (most reliable)
        digital_sigs = detect_digital_signatures(doc)
        for idx, sig in enumerate(digital_sigs):
            all_signatures[f"Digital-{idx}"] = sig
        
        # Process each page
        for page_num in range(len(doc)):
            page = doc.load_page(page_num)
            page_signatures = []
            
            # 1. Check form signature fields (most reliable for form-based PDFs)
            form_sigs = detect_form_signatures(page)
            for sig in form_sigs:
                if sig.get('confidence') != 'field_empty':  # Only count filled fields
                    sig['page'] = page_num + 1
                    page_signatures.append(sig)
            
            # 2. Check for annotation signatures (stamps, ink)
            annot_sigs = detect_annotation_signatures(page)
            for sig in annot_sigs:
                sig['page'] = page_num + 1
                page_signatures.append(sig)
            
            # 3. Check for signature images (less reliable)
            if not strict_mode or len(page_signatures) == 0:
                image_sigs = analyze_signature_images(page, strict_mode)
                for sig in image_sigs:
                    sig['page'] = page_num + 1
                    page_signatures.append(sig)
            
            # 4. Check for text signatures (least reliable)
            if not strict_mode:
                text_sigs = detect_text_signatures(page)
                for sig in text_sigs:
                    sig['page'] = page_num + 1
                    page_signatures.append(sig)
            
            # Add to results
            for idx, sig in enumerate(page_signatures):
                key = f"Page{page_num + 1}-{sig['type']}-{idx}"
                all_signatures[key] = sig
            
            if debug_mode:
                debug_info[f"page_{page_num + 1}"] = {
                    'form_fields': len(form_sigs),
                    'annotations': len(annot_sigs),
                    'images': len(image_sigs) if not strict_mode else 0,
                    'text_patterns': len(text_sigs) if not strict_mode else 0
                }
        
        doc.close()
        
        # Filter signatures by confidence if in strict mode
        if strict_mode:
            filtered_signatures = {}
            for key, sig in all_signatures.items():
                confidence = sig.get('confidence', 'unknown')
                if confidence in ['high', 'medium', 'document_signed']:
                    filtered_signatures[key] = sig
                elif confidence == 'field_empty':
                    continue  # Skip empty fields
            all_signatures = filtered_signatures
        
        # Count total signatures
        total_signatures = len(all_signatures)
        
        # Generate appropriate message
        if total_signatures == 0:
            message = "No signatures found - document appears to be unsigned"
        elif total_signatures == 1:
            message = "Found 1 signature - document is signed"
        else:
            message = f"Found {total_signatures} signatures - document is signed"
        
        # Add summary of signature types found
        signature_types = {}
        for sig in all_signatures.values():
            sig_type = sig.get('type', 'unknown')
            signature_types[sig_type] = signature_types.get(sig_type, 0) + 1
        
        result = {
            'success': True,
            'count': total_signatures,
            'signatures': all_signatures,
            'signature_types': signature_types,
            'message': message,
            'analysis_method': 'comprehensive',
            'strict_mode': strict_mode
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
    parser.add_argument('--debug', action='store_true', help='Debug mode - show detailed analysis')
    parser.add_argument('--strict', action='store_true', default=True, help='Strict mode - only high confidence signatures')
    parser.add_argument('--lenient', action='store_true', help='Lenient mode - include all possible signatures')
    
    args = parser.parse_args()
    
    # Determine strict mode
    strict_mode = not args.lenient
    
    if not os.path.exists(args.file):
        result = {
            'success': False,
            'error': f'File not found: {args.file}',
            'signatures': {},
            'count': 0
        }
    else:
        result = process_pdf_file(args.file, debug_mode=args.debug, strict_mode=strict_mode)
    
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
        
        # Get mode from request
        strict_mode = request.form.get('strict_mode', 'true').lower() == 'true'
        
        # Save file temporarily
        filename = secure_filename(file.filename)
        filepath = os.path.join(UPLOAD_FOLDER, filename)
        file.save(filepath)
        
        # Process file
        result = process_pdf_file(filepath, strict_mode=strict_mode)
        
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
        'message': 'PDF Signature Detection API is running',
        'version': '2.0'
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
    
    # Default behavior - show usage
    print("PDF Signature Detection Tool v2.0")
    print("Usage:")
    print("  Server mode: python app.py --server")
    print("  CLI mode: python app.py --file <pdf_path> [options]")
    print("\nOptions:")
    print("  --strict    : (default) Only detect high-confidence signatures")
    print("  --lenient   : Include all possible signatures")
    print("  --debug     : Show detailed analysis")
    print("  --output    : Save results to JSON file")
    print("  --quiet     : Suppress output")
    sys.exit(1)

if __name__ == '__main__':
    main()