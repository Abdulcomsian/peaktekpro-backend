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
    
    def is_signature(self, image):
        """
        More strict signature detection to avoid false positives
        """
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
            aspect_ratio = w / h
            
            # Much more strict heuristics to avoid false positives
            logger.debug(f"Signature detection - Coverage: {coverage:.4f}, Aspect Ratio: {aspect_ratio:.2f}")
            
            # More restrictive coverage range and aspect ratio
            is_valid_coverage = 0.005 < coverage < 0.3  # Tighter range
            is_valid_aspect = 0.5 < aspect_ratio < 6     # Tighter range
            is_reasonable_size = w > 20 and h > 10       # Minimum size requirements
            
            return is_valid_coverage and is_valid_aspect and is_reasonable_size
            
        except Exception as e:
            logger.error(f"Error in signature detection: {str(e)}")
            return False

    def find_signature_fields(self, page):
        """Find signature field labels on the page - looking for 'signature field X' pattern"""
        signature_fields = []
        try:
            # Extract text with position information
            text_dict = page.get_text("dict")
            
            for block in text_dict["blocks"]:
                if "lines" in block:
                    for line in block["lines"]:
                        for span in line["spans"]:
                            text = span["text"].strip().lower()
                            # Look specifically for "signature field" followed by a number
                            if "signature field" in text and any(char.isdigit() for char in text):
                                bbox = span["bbox"]
                                signature_fields.append({
                                    "text": span["text"].strip(),
                                    "bbox": bbox,
                                    "x": bbox[0],
                                    "y": bbox[1],
                                    "width": bbox[2] - bbox[0],
                                    "height": bbox[3] - bbox[1]
                                })
                                logger.debug(f"Found signature field: {span['text'].strip()}")
        except Exception as e:
            logger.error(f"Error finding signature fields: {str(e)}")
        
        return signature_fields

    def crop_signature_region(self, page, field_bbox, padding=50):
        """Crop a region below/next to the signature field label"""
        try:
            # Define search area below the signature field label
            x0 = max(0, field_bbox[0] - padding)
            y0 = field_bbox[3]  # Start below the label
            x1 = field_bbox[2] + padding * 2
            y1 = field_bbox[3] + padding * 2  # Look below the label
            
            # Create a rectangle for cropping
            rect = fitz.Rect(x0, y0, x1, y1)
            
            # Render just this region at higher resolution
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
        Extract signatures from PDF by looking specifically near signature field labels
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
                
                # Look for signatures only near the signature field labels
                for field_idx, field in enumerate(signature_fields):
                    try:
                        # Crop the region near this signature field
                        cropped_img = self.crop_signature_region(page, field["bbox"])
                        
                        if cropped_img is not None and self.is_signature(cropped_img):
                            signature_data = {
                                'page': page_num + 1,
                                'type': 'field_signature',
                                'field_label': field['text'],
                                'identifier': f"page_{page_num+1}_field_{field_idx}",
                                'width': cropped_img.shape[1],
                                'height': cropped_img.shape[0],
                                'confidence': 0.9  # High confidence since we found it near a field
                            }
                            
                            # Save image to disk if requested
                            if save_images and self.output_dir:
                                signature_filename = f"signature_page_{page_num+1}_field_{field_idx}.png"
                                full_path = os.path.join(self.output_dir, signature_filename)
                                cv2.imwrite(full_path, cropped_img)
                                signature_data['file_path'] = full_path
                                signature_data['filename'] = signature_filename
                            
                            # Include base64 if requested
                            if include_base64:
                                signature_data['base64'] = self.image_to_base64(cropped_img)
                            
                            signatures.append(signature_data)
                            logger.info(f"Found signature for field '{field['text']}' on page {page_num+1}")
                        else:
                            logger.debug(f"No signature found for field '{field['text']}' on page {page_num+1}")
                            
                    except Exception as e:
                        logger.error(f"Error processing signature field '{field['text']}': {str(e)}")
            
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
    parser = argparse.ArgumentParser(description='Extract signatures from PDF files near signature field labels')
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