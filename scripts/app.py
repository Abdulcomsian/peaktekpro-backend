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

class PDFSignatureExtractor:
    def __init__(self, output_dir=None):
        """
        Initialize the PDF signature extractor
        
        Args:
            output_dir (str): Directory to save extracted signature images (optional)
        """
        self.output_dir = output_dir
        if self.output_dir:
            os.makedirs(self.output_dir, exist_ok=True)
    
    def detect_genuine_signature(self, image):
        """
        Detect genuine handwritten signatures, excluding slashes and special characters
        """
        try:
            # Convert to grayscale
            gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
            
            # Apply adaptive thresholding for better ink detection
            adaptive = cv2.adaptiveThreshold(gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
                                           cv2.THRESH_BINARY_INV, 15, 10)
            
            # Remove noise with morphological operations
            kernel = np.ones((2,2), np.uint8)
            cleaned = cv2.morphologyEx(adaptive, cv2.MORPH_CLOSE, kernel)
            cleaned = cv2.morphologyEx(cleaned, cv2.MORPH_OPEN, kernel)
            
            # Count non-zero pixels (ink)
            ink_pixels = cv2.countNonZero(cleaned)
            total_pixels = cleaned.size
            coverage = ink_pixels / total_pixels
            
            # Find contours
            contours, _ = cv2.findContours(cleaned, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
            
            if not contours or len(contours) < 2:
                logger.debug("Not enough contours for a genuine signature")
                return False, 0
            
            # Analyze contours for genuine signature characteristics
            valid_contours = 0
            curved_contours = 0
            straight_line_contours = 0
            total_contour_area = 0
            
            for contour in contours:
                area = cv2.contourArea(contour)
                perimeter = cv2.arcLength(contour, True)
                
                # Filter out very small contours (noise)
                if area < 30:
                    continue
                
                # Get bounding rectangle
                x, y, w, h = cv2.boundingRect(contour)
                aspect_ratio = w / h if h > 0 else 0
                
                # Detect straight lines (slashes, underscores, etc.)
                if aspect_ratio > 8 or (aspect_ratio > 5 and h < 10):
                    straight_line_contours += 1
                    continue
                
                # Detect vertical lines (like "I" or "|")
                if aspect_ratio < 0.2 and w < 8:
                    straight_line_contours += 1
                    continue
                
                # Calculate contour complexity
                if perimeter > 0:
                    # Hull ratio - genuine signatures have more irregular shapes
                    hull = cv2.convexHull(contour)
                    hull_area = cv2.contourArea(hull)
                    solidity = area / hull_area if hull_area > 0 else 0
                    
                    # Extent - how much of the bounding rectangle is filled
                    extent = area / (w * h) if (w * h) > 0 else 0
                    
                    # Approximation to detect curves vs straight lines
                    epsilon = 0.02 * perimeter
                    approx = cv2.approxPolyDP(contour, epsilon, True)
                    
                    # Genuine signatures typically have:
                    # - Irregular shapes (low solidity)
                    # - Curved lines (many approximation points)
                    # - Reasonable extent
                    if (area > 30 and area < (image.shape[0] * image.shape[1] * 0.6) and
                        0.2 < aspect_ratio < 6 and
                        solidity < 0.9 and  # Not too regular
                        extent > 0.1 and   # Not too sparse
                        len(approx) > 6):   # Has curves
                        valid_contours += 1
                        curved_contours += 1
                        total_contour_area += area
                    else:
                        # Check if it might be a simple character or line
                        if len(approx) <= 4:  # Very simple shape
                            straight_line_contours += 1
            
            # Calculate confidence based on signature characteristics
            confidence = 0
            
            # Reject if mostly straight lines or too few curved elements
            if straight_line_contours > curved_contours:
                logger.debug(f"Rejected: Too many straight lines ({straight_line_contours}) vs curves ({curved_contours})")
                return False, 0
            
            # Must have multiple curved contours for a genuine signature
            if curved_contours < 2:
                logger.debug(f"Rejected: Not enough curved contours ({curved_contours})")
                return False, 0
            
            # Coverage factor (signatures have moderate ink coverage)
            if 0.01 < coverage < 0.25:
                confidence += 0.3
            elif 0.005 < coverage < 0.01:
                confidence += 0.1
            else:
                logger.debug(f"Rejected: Invalid coverage ({coverage:.4f})")
                return False, 0
            
            # Curved contour factor (must have enough curved elements)
            if curved_contours >= 5:
                confidence += 0.4
            elif curved_contours >= 3:
                confidence += 0.3
            elif curved_contours >= 2:
                confidence += 0.2
            
            # Complexity factor
            if total_contour_area > 0:
                complexity = total_contour_area / total_pixels
                if 0.015 < complexity < 0.2:
                    confidence += 0.3
                elif 0.005 < complexity < 0.015:
                    confidence += 0.1
            
            logger.debug(f"Signature analysis - Coverage: {coverage:.4f}, Curved contours: {curved_contours}, "
                        f"Straight lines: {straight_line_contours}, Confidence: {confidence:.2f}")
            
            # Higher threshold for genuine signatures
            return confidence > 0.6, confidence
            
        except Exception as e:
            logger.error(f"Error in genuine signature detection: {str(e)}")
            return False, 0

    def find_signature_fields(self, page):
        """Find signature field labels on the page"""
        signature_fields = []
        try:
            # Extract text with position information
            text_dict = page.get_text("dict")
            
            for block in text_dict["blocks"]:
                if "lines" in block:
                    for line in block["lines"]:
                        for span in line["spans"]:
                            text = span["text"].strip().lower()
                            # Look for various signature field patterns
                            signature_patterns = [
                                "signature field",
                                "signature:",
                                "sign here",
                                "signature",
                                "signed by"
                            ]
                            
                            for pattern in signature_patterns:
                                if pattern in text:
                                    bbox = span["bbox"]
                                    signature_fields.append({
                                        "text": span["text"].strip(),
                                        "bbox": bbox,
                                        "x": bbox[0],
                                        "y": bbox[1],
                                        "width": bbox[2] - bbox[0],
                                        "height": bbox[3] - bbox[1]
                                    })
                                    logger.info(f"Found signature field: {span['text'].strip()}")
                                    break
        except Exception as e:
            logger.error(f"Error finding signature fields: {str(e)}")
        
        return signature_fields

    def find_rectangles_near_field(self, page, field_bbox):
        """Find rectangular signature areas near a signature field"""
        rectangles = []
        try:
            # Get page dimensions
            page_rect = page.rect
            
            # Define search area around the signature field
            search_padding = 100
            search_x0 = max(0, field_bbox[0] - search_padding)
            search_y0 = max(0, field_bbox[1] - search_padding)
            search_x1 = min(page_rect.width, field_bbox[2] + search_padding * 3)
            search_y1 = min(page_rect.height, field_bbox[3] + search_padding * 2)
            
            # Render the search area at high resolution
            search_rect = fitz.Rect(search_x0, search_y0, search_x1, search_y1)
            pix = page.get_pixmap(matrix=fitz.Matrix(2, 2), clip=search_rect)
            img_bytes = pix.tobytes("png")
            nparr = np.frombuffer(img_bytes, np.uint8)
            img_cv = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
            
            # Convert to grayscale for rectangle detection
            gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
            
            # Use edge detection to find rectangles
            edges = cv2.Canny(gray, 50, 150, apertureSize=3)
            
            # Find contours
            contours, _ = cv2.findContours(edges, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
            
            for contour in contours:
                # Approximate contour to polygon
                epsilon = 0.02 * cv2.arcLength(contour, True)
                approx = cv2.approxPolyDP(contour, epsilon, True)
                
                # Look for rectangular shapes (4 corners)
                if len(approx) == 4:
                    x, y, w, h = cv2.boundingRect(contour)
                    
                    # Filter rectangles by size (signature boxes are usually medium-sized)
                    if 50 < w < 300 and 30 < h < 150:
                        # Convert back to PDF coordinates
                        pdf_x = search_x0 + (x / 2)
                        pdf_y = search_y0 + (y / 2)
                        pdf_w = w / 2
                        pdf_h = h / 2
                        
                        rectangles.append({
                            'bbox': [pdf_x, pdf_y, pdf_x + pdf_w, pdf_y + pdf_h],
                            'width': pdf_w,
                            'height': pdf_h
                        })
                        
                        logger.debug(f"Found potential signature rectangle: {pdf_w:.1f}x{pdf_h:.1f}")
            
        except Exception as e:
            logger.error(f"Error finding rectangles: {str(e)}")
        
        return rectangles

    def crop_signature_region(self, page, bbox):
        """Crop a specific rectangular region from the page"""
        try:
            # Create a rectangle for cropping
            rect = fitz.Rect(bbox[0], bbox[1], bbox[2], bbox[3])
            
            # Render at high resolution for better signature detection
            pix = page.get_pixmap(matrix=fitz.Matrix(3, 3), clip=rect)
            img_bytes = pix.tobytes("png")
            nparr = np.frombuffer(img_bytes, np.uint8)
            img_cv = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
            
            return img_cv
        except Exception as e:
            logger.error(f"Error cropping signature region: {str(e)}")
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
        Extract genuine handwritten signatures from PDF, excluding slashes and special characters
        """
        signatures = []
        
        try:
            if not os.path.exists(pdf_path):
                raise FileNotFoundError(f"PDF file not found: {pdf_path}")
            
            doc = fitz.open(pdf_path)
            logger.info(f"Processing PDF with {len(doc)} pages")
            
            for page_num in range(len(doc)):
                page = doc.load_page(page_num)
                
                # Find signature field labels on this page
                signature_fields = self.find_signature_fields(page)
                logger.info(f"Page {page_num+1}: Found {len(signature_fields)} signature fields")
                
                if not signature_fields:
                    logger.info(f"No signature fields found on page {page_num+1}, skipping")
                    continue
                
                # For each signature field, look for rectangles nearby
                for field_idx, field in enumerate(signature_fields):
                    try:
                        # Find rectangles near this signature field
                        rectangles = self.find_rectangles_near_field(page, field["bbox"])
                        
                        if not rectangles:
                            # Fallback: create a default search area below the field
                            default_bbox = [
                                field["bbox"][0],
                                field["bbox"][3] + 5,
                                field["bbox"][2] + 150,
                                field["bbox"][3] + 80
                            ]
                            rectangles = [{'bbox': default_bbox, 'width': 150, 'height': 75}]
                        
                        # Check each rectangle for genuine signatures
                        for rect_idx, rect in enumerate(rectangles):
                            cropped_img = self.crop_signature_region(page, rect["bbox"])
                            
                            if cropped_img is not None:
                                has_signature, confidence = self.detect_genuine_signature(cropped_img)
                                
                                if has_signature:
                                    signature_data = {
                                        'page': page_num + 1,
                                        'type': 'genuine_signature',
                                        'field_label': field['text'],
                                        'identifier': f"page_{page_num+1}_field_{field_idx}_rect_{rect_idx}",
                                        'width': cropped_img.shape[1],
                                        'height': cropped_img.shape[0],
                                        'confidence': confidence
                                    }
                                    
                                    # Save image to disk if requested
                                    if save_images and self.output_dir:
                                        signature_filename = f"signature_page_{page_num+1}_field_{field_idx}_rect_{rect_idx}.png"
                                        full_path = os.path.join(self.output_dir, signature_filename)
                                        cv2.imwrite(full_path, cropped_img)
                                        signature_data['file_path'] = full_path
                                        signature_data['filename'] = signature_filename
                                    
                                    # Include base64 if requested
                                    if include_base64:
                                        signature_data['base64'] = self.image_to_base64(cropped_img)
                                    
                                    signatures.append(signature_data)
                                    logger.info(f"Found genuine signature for field '{field['text']}' on page {page_num+1}")
                                    break  # Found signature for this field, move to next field
                                else:
                                    logger.debug(f"No genuine signature detected in rectangle for field '{field['text']}'")
                            
                    except Exception as e:
                        logger.error(f"Error processing signature field '{field['text']}': {str(e)}")
            
            doc.close()
            logger.info(f"Extraction complete. Found {len(signatures)} genuine signatures")
            
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
    parser = argparse.ArgumentParser(description='Extract genuine handwritten signatures from PDF files')
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
        extractor = PDFSignatureExtractor(output_dir=args.output_dir)
        result = extractor.extract_signatures(
            pdf_path=args.pdf_path,
            include_base64=not args.no_base64,
            save_images=not args.no_save
        )
        
        # Output JSON result
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