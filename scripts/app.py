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
        Enhanced signature detection for canvas-drawn signatures within rectangles
        """
        try:
            height, width = image.shape[:2]
            
            # More lenient size requirements for canvas signatures
            if width > 500 or height > 250:
                logger.debug(f"Rejected: Image too large ({width}x{height})")
                return False, 0
            
            if width < 30 or height < 15:
                logger.debug(f"Rejected: Image too small ({width}x{height})")
                return False, 0
            
            # Convert to grayscale
            gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
            
            # Use adaptive thresholding for better canvas signature detection
            adaptive_thresh = cv2.adaptiveThreshold(gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C, cv2.THRESH_BINARY_INV, 11, 2)
            
            # Also try simple threshold as fallback
            _, binary = cv2.threshold(gray, 180, 255, cv2.THRESH_BINARY_INV)
            
            # Combine both methods
            combined = cv2.bitwise_or(adaptive_thresh, binary)
            
            # Remove small noise
            kernel = np.ones((2,2), np.uint8)
            cleaned = cv2.morphologyEx(combined, cv2.MORPH_OPEN, kernel)
            
            # Count ink pixels
            ink_pixels = cv2.countNonZero(cleaned)
            total_pixels = cleaned.size
            coverage = ink_pixels / total_pixels
            
            # More lenient coverage for canvas signatures
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
            border_lines = 0
            valid_contours = []
            
            for contour in contours:
                area = cv2.contourArea(contour)
                
                # Skip tiny contours (noise)
                if area < 20:
                    continue
                
                # Get bounding rectangle
                x, y, w, h = cv2.boundingRect(contour)
                aspect_ratio = w / h if h > 0 else 0
                
                # Check if this is likely a border/rectangle
                contour_perimeter = cv2.arcLength(contour, True)
                if contour_perimeter > 0:
                    # Calculate how much of the image perimeter this contour covers
                    image_perimeter = 2 * (width + height)
                    perimeter_ratio = contour_perimeter / image_perimeter
                    
                    # If contour covers most of the image perimeter, it's likely a border
                    if perimeter_ratio > 0.7:
                        border_lines += 1
                        continue
                
                # Check for very long thin lines (borders)
                if (aspect_ratio > 15 or aspect_ratio < 0.067) and area > 100:
                    border_lines += 1
                    continue
                
                # Check if contour is at the edge (likely border)
                edge_threshold = 5
                if (x <= edge_threshold or y <= edge_threshold or 
                    x + w >= width - edge_threshold or y + h >= height - edge_threshold):
                    if w > width * 0.7 or h > height * 0.7:
                        border_lines += 1
                        continue
                
                # This looks like a potential signature stroke
                valid_contours.append(contour)
                
                # Analyze contour complexity for signature characteristics
                epsilon = 0.02 * contour_perimeter
                approx = cv2.approxPolyDP(contour, epsilon, True)
                
                # Signature strokes should have some complexity
                if len(approx) > 6:  # Has curves
                    signature_strokes += 1
                elif len(approx) > 3 and area > 50:  # Moderate complexity
                    signature_strokes += 0.5
            
            # Must have signature-like strokes
            if signature_strokes < 1:
                logger.debug(f"Rejected: No signature strokes (found {signature_strokes})")
                return False, 0
            
            # Calculate confidence based on multiple factors
            confidence = 0.4  # Base confidence
            
            # Bonus for good coverage
            if 0.01 < coverage < 0.2:
                confidence += 0.3
            elif 0.005 < coverage < 0.3:
                confidence += 0.2
            
            # Bonus for signature strokes
            if signature_strokes >= 3:
                confidence += 0.3
            elif signature_strokes >= 2:
                confidence += 0.2
            elif signature_strokes >= 1:
                confidence += 0.1
            
            # Penalty for too many borders
            if border_lines > signature_strokes:
                confidence -= 0.2
            
            logger.debug(f"Signature analysis - Coverage: {coverage:.4f}, Signature strokes: {signature_strokes}, "
                        f"Border lines: {border_lines}, Confidence: {confidence:.2f}")
            
            return confidence > 0.6, confidence
            
        except Exception as e:
            logger.error(f"Error in signature detection: {str(e)}")
            return False, 0

    def find_signature_field_labels(self, page):
        """Find signature field labels with more focused pattern matching"""
        signature_fields = []
        try:
            # Get page dimensions to exclude footer area
            page_rect = page.rect
            footer_threshold = page_rect.height * 0.85  # Bottom 15% is likely footer
            
            text_dict = page.get_text("dict")
            
            for block in text_dict["blocks"]:
                if "lines" in block:
                    for line in block["lines"]:
                        for span in line["spans"]:
                            # Skip text in footer area
                            if span["bbox"][1] > footer_threshold:
                                continue
                            
                            text = span["text"].strip().lower()
                            original_text = span["text"].strip()
                            
                            # Check for your specific signature field patterns
                            is_signature_field = False
                            
                            # Direct matches for your fields
                            if any(pattern in text for pattern in [
                                "customer signature",
                                "company representative signature", 
                                "company rep signature",
                                "signature field"
                            ]):
                                is_signature_field = True
                            
                            # More generic patterns
                            elif any(pattern in text for pattern in [
                                "signature:",
                                "sign here",
                                "please sign"
                            ]):
                                is_signature_field = True
                            
                            # Check for standalone "signature" with validation
                            elif text == "signature" or text.endswith(":signature") or text.endswith(" signature"):
                                is_signature_field = True
                            
                            if is_signature_field:
                                bbox = span["bbox"]
                                signature_fields.append({
                                    "text": original_text,
                                    "bbox": bbox
                                })
                                logger.info(f"Found signature field: '{original_text}' at {bbox}")
        
        except Exception as e:
            logger.error(f"Error finding signature fields: {str(e)}")
        
        return signature_fields

    def find_rectangle_around_point(self, page, x, y, search_radius=100):
        """Find rectangle drawings around a specific point"""
        try:
            # Get all drawings/paths on the page
            drawings = page.get_drawings()
            
            for drawing in drawings:
                for item in drawing["items"]:
                    if item[0] == "re":  # Rectangle
                        rect = fitz.Rect(item[1])
                        # Check if the point is near this rectangle
                        if (abs(rect.x0 - x) < search_radius or abs(rect.x1 - x) < search_radius) and \
                           (abs(rect.y0 - y) < search_radius or abs(rect.y1 - y) < search_radius):
                            return rect
            return None
        except:
            return None

    def extract_signature_area_with_rectangle(self, page, field_bbox):
        """Extract signature area, looking for rectangles around the field"""
        try:
            # First, try to find a rectangle near the field label
            field_center_x = (field_bbox[0] + field_bbox[2]) / 2
            field_center_y = (field_bbox[1] + field_bbox[3]) / 2
            
            # Look for rectangles to the right and below the field label
            search_points = [
                (field_bbox[2] + 50, field_center_y),     # Right of label
                (field_center_x, field_bbox[3] + 30),     # Below label
                (field_bbox[2] + 100, field_bbox[3] + 20) # Diagonal from label
            ]
            
            signature_rect = None
            for search_x, search_y in search_points:
                signature_rect = self.find_rectangle_around_point(page, search_x, search_y)
                if signature_rect:
                    logger.debug(f"Found rectangle at {signature_rect}")
                    break
            
            # If we found a rectangle, use it
            if signature_rect:
                # Add small padding inside the rectangle
                padding = 5
                x0 = signature_rect.x0 + padding
                y0 = signature_rect.y0 + padding  
                x1 = signature_rect.x1 - padding
                y1 = signature_rect.y1 - padding
            else:
                # Fallback: create search areas near the field label
                logger.debug("No rectangle found, using fallback area")
                
                # Try area to the right of the label
                x0 = field_bbox[2] + 20
                y0 = field_bbox[1] - 10
                x1 = x0 + 150
                y1 = y0 + 60
                
                # If that goes off page, try below the label
                if x1 > page.rect.width:
                    x0 = field_bbox[0]
                    y0 = field_bbox[3] + 10
                    x1 = x0 + 150
                    y1 = y0 + 50
            
            # Ensure we don't go outside page bounds
            x0 = max(0, x0)
            y0 = max(0, y0)
            x1 = min(page.rect.width, x1)
            y1 = min(page.rect.height, y1)
            
            # Create rectangle and render
            rect = fitz.Rect(x0, y0, x1, y1)
            
            # Render at higher resolution for better quality
            pix = page.get_pixmap(matrix=fitz.Matrix(2.5, 2.5), clip=rect)
            img_bytes = pix.tobytes("png")
            nparr = np.frombuffer(img_bytes, np.uint8)
            img_cv = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
            
            logger.debug(f"Extracted area: {rect}, Image size: {img_cv.shape if img_cv is not None else 'None'}")
            return img_cv
            
        except Exception as e:
            logger.error(f"Error extracting signature area: {str(e)}")
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
        Extract signatures from areas near signature field labels
        """
        signatures = []
        
        try:
            if not os.path.exists(pdf_path):
                raise FileNotFoundError(f"PDF file not found: {pdf_path}")
            
            doc = fitz.open(pdf_path)
            logger.info(f"Processing PDF with {len(doc)} pages")
            
            for page_num in range(len(doc)):
                page = doc.load_page(page_num)
                
                # Find signature field labels (excluding footer)
                signature_fields = self.find_signature_field_labels(page)
                logger.info(f"Page {page_num+1}: Found {len(signature_fields)} signature fields")
                
                # Process each field individually
                for field_idx, field in enumerate(signature_fields):
                    try:
                        # Extract area around this specific field (looking for rectangles)
                        signature_img = self.extract_signature_area_with_rectangle(page, field["bbox"])
                        
                        if signature_img is not None:
                            has_signature, confidence = self.is_genuine_handwritten_signature(signature_img)
                            
                            logger.info(f"Field '{field['text']}' - Has signature: {has_signature}, Confidence: {confidence:.2f}")
                            
                            if has_signature:
                                signature_data = {
                                    'page': page_num + 1,
                                    'type': 'handwritten_signature',
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
                                logger.info(f"SUCCESSFULLY extracted signature for '{field['text']}' on page {page_num+1}")
                            else:
                                logger.warning(f"No signature detected for '{field['text']}' on page {page_num+1} (confidence: {confidence:.2f})")
                        else:
                            logger.warning(f"Could not extract image for field '{field['text']}'")
                        
                    except Exception as e:
                        logger.error(f"Error processing field '{field['text']}': {str(e)}")
            
            doc.close()
            logger.info(f"Extraction complete. Found {len(signatures)} signatures total")
            
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
    parser = argparse.ArgumentParser(description='Extract precise handwritten signatures from PDF files')
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