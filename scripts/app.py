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
        """More robust signature detection using multiple features"""
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
            
            # More lenient signature heuristics
            logger.debug(f"Signature detection - Coverage: {coverage:.4f}, Aspect Ratio: {aspect_ratio:.2f}")
            return (0.001 < coverage < 0.5) and (0.3 < aspect_ratio < 8)
            
        except Exception as e:
            logger.error(f"Error in signature detection: {str(e)}")
            return False

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
        Extract signatures from PDF
        
        Args:
            pdf_path (str): Path to the PDF file
            include_base64 (bool): Whether to include base64 encoded images in the response
            save_images (bool): Whether to save signature images to disk
            
        Returns:
            dict: Dictionary containing signature information
        """
        signatures = []
        
        try:
            if not os.path.exists(pdf_path):
                raise FileNotFoundError(f"PDF file not found: {pdf_path}")
            
            doc = fitz.open(pdf_path)
            logger.info(f"Processing PDF with {len(doc)} pages")
            
            for page_num in range(len(doc)):
                page = doc.load_page(page_num)
                
                # Get list of images on the page
                image_list = page.get_images(full=True)
                logger.debug(f"Page {page_num+1} has {len(image_list)} images")
                
                # First try extracting embedded images
                if image_list:
                    for img_index, img in enumerate(image_list):
                        try:
                            xref = img[0]
                            base_image = doc.extract_image(xref)
                            image_bytes = base_image["image"]
                            nparr = np.frombuffer(image_bytes, np.uint8)
                            img_cv = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
                            
                            if img_cv is not None and self.is_signature(img_cv):
                                signature_data = {
                                    'page': page_num + 1,
                                    'type': 'embedded',
                                    'index': img_index,
                                    'width': img_cv.shape[1],
                                    'height': img_cv.shape[0],
                                    'identifier': f"page_{page_num+1}_img_{img_index}"
                                }
                                
                                # Save image to disk if requested
                                if save_images and self.output_dir:
                                    signature_filename = f"signature_page_{page_num+1}_img{img_index}.png"
                                    full_path = os.path.join(self.output_dir, signature_filename)
                                    cv2.imwrite(full_path, img_cv)
                                    signature_data['file_path'] = full_path
                                    signature_data['filename'] = signature_filename
                                
                                # Include base64 if requested
                                if include_base64:
                                    signature_data['base64'] = self.image_to_base64(img_cv)
                                
                                signatures.append(signature_data)
                                
                        except Exception as e:
                            logger.error(f"Error processing embedded image {img_index} on page {page_num+1}: {str(e)}")
                
                # If no embedded signatures found, try rendering the page
                page_has_embedded_sigs = any(sig['page'] == page_num + 1 and sig['type'] == 'embedded' for sig in signatures)
                
                if not page_has_embedded_sigs:
                    try:
                        pix = page.get_pixmap(dpi=150)
                        img_bytes = pix.tobytes("png")
                        nparr = np.frombuffer(img_bytes, np.uint8)
                        img_cv = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
                        
                        if img_cv is not None and self.is_signature(img_cv):
                            signature_data = {
                                'page': page_num + 1,
                                'type': 'rendered',
                                'width': img_cv.shape[1],
                                'height': img_cv.shape[0],
                                'identifier': f"page_{page_num+1}_rendered"
                            }
                            
                            # Save image to disk if requested
                            if save_images and self.output_dir:
                                signature_filename = f"signature_page_{page_num+1}_rendered.png"
                                full_path = os.path.join(self.output_dir, signature_filename)
                                cv2.imwrite(full_path, img_cv)
                                signature_data['file_path'] = full_path
                                signature_data['filename'] = signature_filename
                            
                            # Include base64 if requested
                            if include_base64:
                                signature_data['base64'] = self.image_to_base64(img_cv)
                            
                            signatures.append(signature_data)
                            
                    except Exception as e:
                        logger.error(f"Error processing rendered page {page_num+1}: {str(e)}")
            
            doc.close()
            logger.info(f"Extracted {len(signatures)} signatures")
            
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
    parser = argparse.ArgumentParser(description='Extract signatures from PDF files')
    parser.add_argument('pdf_path', help='Path to the PDF file')
    parser.add_argument('--output-dir', '-o', help='Directory to save signature images')
    parser.add_argument('--no-base64', action='store_true', help='Don\'t include base64 encoded images')
    parser.add_argument('--no-save', action='store_true', help='Don\'t save signature images to disk')
    parser.add_argument('--quiet', '-q', action='store_true', help='Suppress info messages')
    
    args = parser.parse_args()
    
    if args.quiet:
        logging.getLogger().setLevel(logging.ERROR)
    
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