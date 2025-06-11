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
        """Enhanced signature detection with stricter criteria"""
        try:
            # Check if image is valid
            if image is None or image.size == 0:
                return False
            
            # Get image dimensions
            height, width = image.shape[:2]
            
            # Skip very small or very large images
            if width < 50 or height < 20 or width > 2000 or height > 1000:
                logger.debug(f"Image size filter failed: {width}x{height}")
                return False
            
            # Convert to grayscale
            gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
            
            # Check if image is mostly white/empty
            mean_intensity = np.mean(gray)
            if mean_intensity > 250:  # Very bright/white image
                logger.debug(f"Image too bright (mean intensity: {mean_intensity:.2f})")
                return False
            
            # Apply adaptive thresholding
            thresh = cv2.adaptiveThreshold(gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
                                        cv2.THRESH_BINARY_INV, 11, 2)
            
            # Count non-zero pixels (ink)
            ink_pixels = cv2.countNonZero(thresh)
            total_pixels = thresh.size
            coverage = ink_pixels / total_pixels
            
            # Apply stricter coverage criteria
            if coverage < 0.005 or coverage > 0.3:  # Too sparse or too dense
                logger.debug(f"Coverage filter failed: {coverage:.4f}")
                return False
            
            # Find contours
            contours, _ = cv2.findContours(thresh, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
            if not contours:
                logger.debug("No contours found")
                return False
            
            # Filter contours by area
            significant_contours = [c for c in contours if cv2.contourArea(c) > 50]
            if not significant_contours:
                logger.debug("No significant contours found")
                return False
            
            # Get largest contour
            largest_contour = max(significant_contours, key=cv2.contourArea)
            contour_area = cv2.contourArea(largest_contour)
            
            # Check if contour area is reasonable
            if contour_area < 100:
                logger.debug(f"Contour area too small: {contour_area}")
                return False
            
            # Get bounding rectangle
            x, y, w, h = cv2.boundingRect(largest_contour)
            aspect_ratio = w / h
            
            # Apply aspect ratio filter (signatures are usually wider than tall)
            if aspect_ratio < 0.5 or aspect_ratio > 6:
                logger.debug(f"Aspect ratio filter failed: {aspect_ratio:.2f}")
                return False
            
            # Check if contour fills reasonable portion of bounding box
            bbox_area = w * h
            fill_ratio = contour_area / bbox_area
            if fill_ratio < 0.1:  # Too sparse within bounding box
                logger.debug(f"Fill ratio too low: {fill_ratio:.3f}")
                return False
            
            # Additional check: number of contours (signatures usually have multiple strokes)
            if len(significant_contours) < 2:
                logger.debug(f"Too few contours: {len(significant_contours)}")
                return False
            
            # Check for text-like patterns (which are NOT signatures)
            # Text usually has more uniform spacing and regular patterns
            contour_heights = [cv2.boundingRect(c)[3] for c in significant_contours]
            if len(contour_heights) > 5:  # Many contours might indicate text
                height_std = np.std(contour_heights)
                height_mean = np.mean(contour_heights)
                if height_std / height_mean < 0.3:  # Very uniform heights = likely text
                    logger.debug("Rejected as potential text (uniform heights)")
                    return False
            
            logger.debug(f"Signature detected - Coverage: {coverage:.4f}, Aspect Ratio: {aspect_ratio:.2f}, "
                        f"Contours: {len(significant_contours)}, Fill Ratio: {fill_ratio:.3f}")
            return True
            
        except Exception as e:
            logger.error(f"Error in signature detection: {str(e)}")
            return False

    def preprocess_image(self, image):
        """Preprocess image to improve signature detection"""
        try:
            # Convert to grayscale
            gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
            
            # Apply Gaussian blur to reduce noise
            blurred = cv2.GaussianBlur(gray, (3, 3), 0)
            
            # Enhance contrast
            clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
            enhanced = clahe.apply(blurred)
            
            # Convert back to BGR for consistent processing
            return cv2.cvtColor(enhanced, cv2.COLOR_GRAY2BGR)
            
        except Exception as e:
            logger.error(f"Error in image preprocessing: {str(e)}")
            return image

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
        Extract signatures from PDF with improved detection
        
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
                page_signatures_found = 0
                
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
                            
                            # Skip very small images
                            if len(image_bytes) < 1000:  # Less than 1KB
                                logger.debug(f"Skipping small image: {len(image_bytes)} bytes")
                                continue
                            
                            nparr = np.frombuffer(image_bytes, np.uint8)
                            img_cv = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
                            
                            if img_cv is not None:
                                # Preprocess image
                                processed_img = self.preprocess_image(img_cv)
                                
                                if self.is_signature(processed_img):
                                    signature_data = {
                                        'page': page_num + 1,
                                        'type': 'embedded',
                                        'index': img_index,
                                        'width': img_cv.shape[1],
                                        'height': img_cv.shape[0],
                                        'identifier': f"page_{page_num+1}_img_{img_index}",
                                        'file_size_bytes': len(image_bytes)
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
                                    page_signatures_found += 1
                                    logger.info(f"Found signature on page {page_num+1}, image {img_index}")
                                    
                        except Exception as e:
                            logger.error(f"Error processing embedded image {img_index} on page {page_num+1}: {str(e)}")
                
                # Only try rendering the page if no embedded signatures were found
                # and the page has potential signature content
                if page_signatures_found == 0:
                    try:
                        # Use higher DPI for better quality
                        pix = page.get_pixmap(dpi=200)
                        img_bytes = pix.tobytes("png")
                        nparr = np.frombuffer(img_bytes, np.uint8)
                        img_cv = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
                        
                        if img_cv is not None:
                            # Preprocess the rendered page
                            processed_img = self.preprocess_image(img_cv)
                            
                            if self.is_signature(processed_img):
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
                                logger.info(f"Found rendered signature on page {page_num+1}")
                                
                    except Exception as e:
                        logger.error(f"Error processing rendered page {page_num+1}: {str(e)}")
            
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
    parser = argparse.ArgumentParser(description='Extract signatures from PDF files')
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