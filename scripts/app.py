#!/usr/bin/env python3
import os
import sys
import json
import logging
import argparse
import base64
from pathlib import Path
import fitz  # PyMuPDF
import cv2
import numpy as np

# Configure logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

class PreciseSignatureExtractor:
    def __init__(self, output_dir=None):
        """
        Initialize the precise signature extractor
        """
        self.output_dir = output_dir
        if self.output_dir:
            os.makedirs(self.output_dir, exist_ok=True)
    
    def is_genuine_handwritten_signature(self, image):
        """
        Detect only genuine handwritten signatures within a single rectangle
        """
        try:
            height, width = image.shape[:2]
            
            # Skip if image is too small
            if width < 30 or height < 15:
                logger.debug(f"Rejected: Image too small ({width}x{height})")
                return False, 0
            
            # Convert to grayscale
            gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
            
            # Use adaptive thresholding to better detect signatures
            binary = cv2.adaptiveThreshold(gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C, cv2.THRESH_BINARY_INV, 11, 2)
            
            # Remove small noise
            kernel = np.ones((2,2), np.uint8)
            cleaned = cv2.morphologyEx(binary, cv2.MORPH_CLOSE, kernel)
            cleaned = cv2.morphologyEx(cleaned, cv2.MORPH_OPEN, kernel)
            
            # Count ink pixels
            ink_pixels = cv2.countNonZero(cleaned)
            total_pixels = cleaned.size
            coverage = ink_pixels / total_pixels
            
            # Signatures should have some ink but not too much
            if coverage < 0.005 or coverage > 0.4:
                logger.debug(f"Rejected: Invalid coverage ({coverage:.4f})")
                return False, 0
            
            # Find contours
            contours, _ = cv2.findContours(cleaned, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
            
            if len(contours) < 1:
                logger.debug("Rejected: No contours found")
                return False, 0
            
            # Analyze contours for signature characteristics
            signature_strokes = 0
            total_signature_area = 0
            
            for contour in contours:
                area = cv2.contourArea(contour)
                
                # Skip tiny contours (noise)
                if area < 20:
                    continue
                
                # Get bounding rectangle
                x, y, w, h = cv2.boundingRect(contour)
                aspect_ratio = w / h if h > 0 else 0
                
                # Skip very long thin lines (likely borders)
                if aspect_ratio > 15 or aspect_ratio < 0.05:
                    continue
                
                # Calculate contour perimeter and complexity
                perimeter = cv2.arcLength(contour, True)
                if perimeter > 0:
                    # Check if this looks like a signature stroke
                    compactness = (4 * np.pi * area) / (perimeter * perimeter)
                    
                    # Signature strokes are usually not too compact (not circles/squares)
                    if compactness < 0.8 and area > 30:
                        signature_strokes += 1
                        total_signature_area += area
            
            # Must have at least one signature stroke
            if signature_strokes < 1:
                logger.debug(f"Rejected: No signature strokes found")
                return False, 0
            
            # Calculate confidence based on coverage and stroke count
            confidence = 0.4  # Base confidence
            
            # Bonus for good coverage
            if 0.01 < coverage < 0.2:
                confidence += 0.3
            elif 0.005 < coverage < 0.01:
                confidence += 0.2
            
            # Bonus for multiple strokes
            if signature_strokes >= 2:
                confidence += 0.3
            elif signature_strokes >= 1:
                confidence += 0.2
            
            logger.debug(f"Signature analysis - Coverage: {coverage:.4f}, Strokes: {signature_strokes}, Confidence: {confidence:.2f}")
            
            return confidence > 0.6, confidence
            
        except Exception as e:
            logger.error(f"Error in signature detection: {str(e)}")
            return False, 0

    def find_signature_field_labels(self, page):
        """Find signature field labels including 'signature field 1', 'signature field 2', etc."""
        signature_fields = []
        try:
            # Get page dimensions to exclude footer area
            page_rect = page.rect
            footer_threshold = page_rect.height * 0.9  # Bottom 10% is likely footer
            
            text_dict = page.get_text("dict")
            
            for block in text_dict["blocks"]:
                if "lines" in block:
                    for line in block["lines"]:
                        for span in line["spans"]:
                            # Skip text in footer area
                            if span["bbox"][1] > footer_threshold:
                                continue
                            
                            text = span["text"].strip().lower()
                            
                            # Look for signature field patterns
                            signature_patterns = [
                                "signature field 1",
                                "signature field 2", 
                                "signature field 3",
                                "signature field 4",
                                "customer signature",
                                "company representative signature",
                                "authorized signature"
                            ]
                            
                            for pattern in signature_patterns:
                                if pattern in text:
                                    bbox = span["bbox"]
                                    signature_fields.append({
                                        "text": span["text"].strip(),
                                        "bbox": bbox
                                    })
                                    logger.info(f"Found signature field: {span['text'].strip()}")
                                    break
        
        except Exception as e:
            logger.error(f"Error finding signature fields: {str(e)}")
        
        return signature_fields

    def find_signature_rectangles_near_field(self, page, field_bbox):
        """Find actual signature rectangles/boxes near the field label"""
        rectangles = []
        try:
            # Define a larger search area around the field
            search_padding = 200
            x0 = max(0, field_bbox[0] - search_padding)
            y0 = max(0, field_bbox[1] - search_padding)
            x1 = min(page.rect.width, field_bbox[2] + search_padding)
            y1 = min(page.rect.height, field_bbox[3] + search_padding)
            
            # Render the search area
            search_rect = fitz.Rect(x0, y0, x1, y1)
            pix = page.get_pixmap(matrix=fitz.Matrix(2, 2), clip=search_rect)
            img_bytes = pix.tobytes("png")
            nparr = np.frombuffer(img_bytes, np.uint8)
            img_cv = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
            
            # Convert to grayscale and find edges
            gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
            edges = cv2.Canny(gray, 50, 150)
            
            # Find contours
            contours, _ = cv2.findContours(edges, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
            
            for contour in contours:
                # Get bounding rectangle
                x, y, w, h = cv2.boundingRect(contour)
                
                # Look for signature-sized rectangles
                if 80 < w < 250 and 40 < h < 120:
                    # Convert back to PDF coordinates
                    pdf_x = x0 + (x / 2)
                    pdf_y = y0 + (y / 2)
                    pdf_w = w / 2
                    pdf_h = h / 2
                    
                    rectangles.append({
                        'bbox': [pdf_x, pdf_y, pdf_x + pdf_w, pdf_y + pdf_h],
                        'width': pdf_w,
                        'height': pdf_h
                    })
                    
                    logger.debug(f"Found signature rectangle: {pdf_w:.1f}x{pdf_h:.1f}")
            
            return rectangles
            
        except Exception as e:
            logger.error(f"Error finding rectangles: {str(e)}")
            return []

    def crop_signature_area(self, page, bbox):
        """Crop a signature area from the page"""
        try:
            rect = fitz.Rect(bbox[0], bbox[1], bbox[2], bbox[3])
            pix = page.get_pixmap(matrix=fitz.Matrix(3, 3), clip=rect)
            img_bytes = pix.tobytes("png")
            nparr = np.frombuffer(img_bytes, np.uint8)
            img_cv = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
            return img_cv
        except Exception as e:
            logger.error(f"Error cropping signature area: {str(e)}")
            return None

    def image_to_base64(self, image):
        """Convert OpenCV image to base64 string"""
        try:
            _, buffer = cv2.imencode('.png', image)
            img_base64 = base64.b64encode(buffer).decode('utf-8')
            return img_base64
        except Exception as e:
            logger.error(f"Error converting image to base64: {str(e)}")
            return None

    def extract_signatures(self, pdf_path, include_base64=True, save_images=True):
        """
        Extract signatures from rectangles near signature field labels
        """
        signatures = []
        
        try:
            if not os.path.exists(pdf_path):
                raise FileNotFoundError(f"PDF file not found: {pdf_path}")
            
            doc = fitz.open(pdf_path)
            logger.info(f"Processing PDF with {len(doc)} pages")
            
            for page_num in range(len(doc)):
                page = doc.load_page(page_num)
                
                # Find signature field labels
                signature_fields = self.find_signature_field_labels(page)
                logger.info(f"Page {page_num+1}: Found {len(signature_fields)} signature fields")
                
                # Process each field individually
                for field_idx, field in enumerate(signature_fields):
                    try:
                        # Find rectangles near this field
                        rectangles = self.find_signature_rectangles_near_field(page, field["bbox"])
                        
                        if not rectangles:
                            # Fallback: create search areas around the field
                            search_areas = [
                                # Right of the field
                                [field["bbox"][2] + 10, field["bbox"][1] - 10, 
                                 field["bbox"][2] + 150, field["bbox"][3] + 40],
                                # Below the field
                                [field["bbox"][0], field["bbox"][3] + 5,
                                 field["bbox"][0] + 150, field["bbox"][3] + 55]
                            ]
                            rectangles = [{'bbox': area, 'width': 140, 'height': 50} for area in search_areas]
                        
                        # Check each rectangle for signatures
                        found_signature = False
                        for rect_idx, rect in enumerate(rectangles):
                            if found_signature:
                                break
                                
                            signature_img = self.crop_signature_area(page, rect["bbox"])
                            
                            if signature_img is not None:
                                has_signature, confidence = self.is_genuine_handwritten_signature(signature_img)
                                
                                if has_signature:
                                    signature_data = {
                                        'page': page_num + 1,
                                        'type': 'canvas_signature',
                                        'field_label': field['text'],
                                        'identifier': f"page_{page_num+1}_field_{field_idx}",
                                        'width': signature_img.shape[1],
                                        'height': signature_img.shape[0],
                                        'confidence': confidence
                                    }
                                    
                                    # Save image to disk if requested
                                    if save_images and self.output_dir:
                                        signature_filename = f"signature_page_{page_num+1}_field_{field_idx}.png"
                                        full_path = os.path.join(self.output_dir, signature_filename)
                                        cv2.imwrite(full_path, signature_img)
                                        signature_data['file_path'] = full_path
                                        signature_data['filename'] = signature_filename
                                    
                                    # Include base64 if requested
                                    if include_base64:
                                        signature_data['base64'] = self.image_to_base64(signature_img)
                                    
                                    signatures.append(signature_data)
                                    logger.info(f"Found signature for '{field['text']}' on page {page_num+1}")
                                    found_signature = True
                        
                        if not found_signature:
                            logger.debug(f"No signature found for '{field['text']}' on page {page_num+1}")
                        
                    except Exception as e:
                        logger.error(f"Error processing field '{field['text']}': {str(e)}")
            
            doc.close()
            logger.info(f"Extraction complete. Found {len(signatures)} signatures")
            
        except Exception as e:
            logger.error(f"Error processing PDF: {str(e)}", exc_info=True)
            raise RuntimeError(f"PDF processing error: {str(e)}")
        
        return {
            'success': True,
            'pdf_path': pdf_path,
            'total_signatures': len(signatures),
            'signatures': signatures
        }

def main():
    parser = argparse.ArgumentParser(description='Extract canvas signatures from PDF files')
    parser.add_argument('pdf_path', help='Path to the PDF file')
    parser.add_argument('--output-dir', '-o', help='Directory to save signature images')
    parser.add_argument('--no-base64', action='store_true', help='Don\'t include base64 encoded images')
    parser.add_argument('--no-save', action='store_true', help='Don\'t save signature images to disk')
    parser.add_argument('--quiet', '-q', action='store_true', help='Suppress info messages')
    parser.add_argument('--debug', '-d', action='store_true', help='Enable debug logging')
    
    args = parser.parse_args()
    
    if args.quiet:
        logging.getLogger().setLevel(logging.ERROR)
    elif args.debug:
        logging.getLogger().setLevel(logging.DEBUG)
    
    try:
        extractor = PreciseSignatureExtractor(output_dir=args.output_dir)
        result = extractor.extract_signatures(
            pdf_path=args.pdf_path,
            include_base64=not args.no_base64,
            save_images=not args.no_save
        )
        
        print(json.dumps(result, indent=2))
        
    except Exception as e:
        error_result = {
            'success': False,
            'error': str(e),
            'pdf_path': args.pdf_path
        }
        print(json.dumps(error_result, indent=2))
        sys.exit(1)

if __name__ == '__main__':
    main()