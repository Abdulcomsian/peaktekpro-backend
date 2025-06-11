#!/usr/bin/env python3
import os
import sys
import json
import logging
import argparse
import base64
import re
from pathlib import Path
import fitz  # PyMuPDF
import cv2
import numpy as np
from collections import defaultdict

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
        
        # Common signature field keywords (case insensitive)
        self.signature_keywords = [
            'signature', 'sign', 'signed', 'signatory', 'signer',
            'authorized', 'approval', 'approve', 'endorsed', 'endorsement',
            'witness', 'initial', 'initials', 'name', 'title', 'date',
            'president', 'director', 'manager', 'officer', 'representative',
            'by:', 'signed by', 'signature of', 'name:', 'title:', 'date:'
        ]
    
    def is_signature(self, image):
        """Balanced signature detection - not too strict, not too lenient"""
        try:
            if image is None or image.size == 0:
                return False
            
            height, width = image.shape[:2]
            
            # Basic size filtering - allow smaller signatures
            if width < 30 or height < 15 or width > 3000 or height > 2000:
                logger.debug(f"Size filter: {width}x{height}")
                return False
            
            # Convert to grayscale
            gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
            
            # Check if image is completely white/empty
            if np.mean(gray) > 248:
                logger.debug("Image too bright/empty")
                return False
            
            # Apply multiple thresholding techniques
            # Method 1: Adaptive threshold
            thresh1 = cv2.adaptiveThreshold(gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
                                          cv2.THRESH_BINARY_INV, 11, 2)
            
            # Method 2: OTSU threshold
            _, thresh2 = cv2.threshold(gray, 0, 255, cv2.THRESH_BINARY_INV + cv2.THRESH_OTSU)
            
            # Method 3: Simple threshold
            _, thresh3 = cv2.threshold(gray, 200, 255, cv2.THRESH_BINARY_INV)
            
            # Use the threshold that gives reasonable coverage
            best_thresh = None
            best_coverage = 0
            
            for thresh in [thresh1, thresh2, thresh3]:
                ink_pixels = cv2.countNonZero(thresh)
                coverage = ink_pixels / thresh.size
                if 0.002 < coverage < 0.4:  # Reasonable range
                    if coverage > best_coverage:
                        best_coverage = coverage
                        best_thresh = thresh
            
            if best_thresh is None:
                logger.debug("No suitable threshold found")
                return False
            
            # Find contours
            contours, _ = cv2.findContours(best_thresh, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
            if not contours:
                return False
            
            # Filter meaningful contours
            meaningful_contours = []
            for contour in contours:
                area = cv2.contourArea(contour)
                if area > 20:  # Minimum area for meaningful content
                    meaningful_contours.append(contour)
            
            if not meaningful_contours:
                logger.debug("No meaningful contours")
                return False
            
            # Get the largest contour for aspect ratio check
            largest_contour = max(meaningful_contours, key=cv2.contourArea)
            x, y, w, h = cv2.boundingRect(largest_contour)
            aspect_ratio = w / h
            
            # More flexible aspect ratio (signatures can be various shapes)
            if aspect_ratio < 0.2 or aspect_ratio > 10:
                logger.debug(f"Aspect ratio out of range: {aspect_ratio:.2f}")
                return False
            
            # Check for signature characteristics
            total_contour_area = sum(cv2.contourArea(c) for c in meaningful_contours)
            image_area = width * height
            content_ratio = total_contour_area / image_area
            
            # Flexible content ratio
            if content_ratio < 0.001 or content_ratio > 0.8:
                logger.debug(f"Content ratio out of range: {content_ratio:.4f}")
                return False
            
            # Additional checks for signature-like properties
            # 1. Check for curved/irregular shapes (signatures are usually not geometric)
            hull_area = cv2.contourArea(cv2.convexHull(largest_contour))
            if hull_area > 0:
                solidity = cv2.contourArea(largest_contour) / hull_area
                # Signatures are usually less solid than geometric shapes
                if solidity > 0.95 and len(meaningful_contours) < 3:
                    logger.debug(f"Too geometric (solidity: {solidity:.3f})")
                    return False
            
            logger.debug(f"Signature detected - Coverage: {best_coverage:.4f}, "
                        f"Aspect: {aspect_ratio:.2f}, Contours: {len(meaningful_contours)}, "
                        f"Content ratio: {content_ratio:.4f}")
            return True
            
        except Exception as e:
            logger.error(f"Error in signature detection: {str(e)}")
            return False

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
                            if text and any(keyword in text for keyword in self.signature_keywords):
                                bbox = span["bbox"]
                                signature_fields.append({
                                    "text": span["text"].strip(),
                                    "bbox": bbox,
                                    "x": bbox[0],
                                    "y": bbox[1],
                                    "width": bbox[2] - bbox[0],
                                    "height": bbox[3] - bbox[1]
                                })
        except Exception as e:
            logger.error(f"Error finding signature fields: {str(e)}")
        
        return signature_fields

    def find_nearby_signature_field(self, signature_bbox, signature_fields, max_distance=100):
        """Find the closest signature field label to a signature"""
        if not signature_fields:
            return None
        
        sig_center_x = signature_bbox[0] + signature_bbox[2] / 2
        sig_center_y = signature_bbox[1] + signature_bbox[3] / 2
        
        closest_field = None
        min_distance = float('inf')
        
        for field in signature_fields:
            field_center_x = field["x"] + field["width"] / 2
            field_center_y = field["y"] + field["height"] / 2
            
            distance = np.sqrt((sig_center_x - field_center_x)**2 + (sig_center_y - field_center_y)**2)
            
            if distance < min_distance and distance <= max_distance:
                min_distance = distance
                closest_field = field
        
        return closest_field

    def crop_signature_region(self, page, image_bbox, padding=20):
        """Crop a specific region from the page"""
        try:
            # Add padding to the bounding box
            x0 = max(0, image_bbox[0] - padding)
            y0 = max(0, image_bbox[1] - padding)
            x1 = image_bbox[2] + padding
            y1 = image_bbox[3] + padding
            
            # Create a rectangle for cropping
            rect = fitz.Rect(x0, y0, x1, y1)
            
            # Render just this region
            pix = page.get_pixmap(matrix=fitz.Matrix(2, 2), clip=rect)
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
        Extract signatures from PDF with field label detection
        
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
                
                # Find signature field labels on this page
                signature_fields = self.find_signature_fields(page)
                logger.debug(f"Page {page_num+1}: Found {len(signature_fields)} signature fields")
                
                # Get list of images on the page
                image_list = page.get_images(full=True)
                logger.debug(f"Page {page_num+1} has {len(image_list)} images")
                
                # Process embedded images
                for img_index, img in enumerate(image_list):
                    try:
                        xref = img[0]
                        base_image = doc.extract_image(xref)
                        image_bytes = base_image["image"]
                        
                        # Skip very small images (likely icons)
                        if len(image_bytes) < 500:
                            continue
                        
                        nparr = np.frombuffer(image_bytes, np.uint8)
                        img_cv = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
                        
                        if img_cv is not None and self.is_signature(img_cv):
                            # Get image position on page
                            img_rects = page.get_image_rects(xref)
                            img_bbox = img_rects[0] if img_rects else None
                            
                            # Find nearby signature field
                            nearby_field = None
                            if img_bbox and signature_fields:
                                nearby_field = self.find_nearby_signature_field(img_bbox, signature_fields)
                            
                            signature_data = {
                                'page': page_num + 1,
                                'type': 'embedded',
                                'index': img_index,
                                'width': img_cv.shape[1],
                                'height': img_cv.shape[0],
                                'identifier': f"page_{page_num+1}_img_{img_index}",
                                'file_size_bytes': len(image_bytes)
                            }
                            
                            # Add signature field information if found
                            if nearby_field:
                                # Calculate distance for display
                                sig_center_x = img_bbox[0] + (img_bbox[2] - img_bbox[0]) / 2
                                sig_center_y = img_bbox[1] + (img_bbox[3] - img_bbox[1]) / 2
                                field_center_x = nearby_field["x"] + nearby_field["width"] / 2
                                field_center_y = nearby_field["y"] + nearby_field["height"] / 2
                                distance = np.sqrt((sig_center_x - field_center_x)**2 + (sig_center_y - field_center_y)**2)
                                
                                signature_data['signature_field'] = {
                                    'label': nearby_field['text'],
                                    'distance': round(distance, 2)
                                }
                            
                            # Add position information if available
                            if img_bbox:
                                signature_data['position'] = {
                                    'x': img_bbox[0],
                                    'y': img_bbox[1],
                                    'width': img_bbox[2] - img_bbox[0],
                                    'height': img_bbox[3] - img_bbox[1]
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
                            logger.info(f"Found signature on page {page_num+1}, image {img_index}")
                            
                    except Exception as e:
                        logger.error(f"Error processing embedded image {img_index} on page {page_num+1}: {str(e)}")
                
                # If no embedded signatures found, try looking for signature regions based on fields
                page_has_sigs = any(sig['page'] == page_num + 1 and sig['type'] == 'embedded' for sig in signatures)
                
                if not page_has_sigs and signature_fields:
                    # Try to extract signatures near signature fields
                    for field_idx, field in enumerate(signature_fields):
                        try:
                            # Define search area around the signature field
                            search_x = field["x"] - 50
                            search_y = field["y"] - 30
                            search_width = max(200, field["width"] + 100)
                            search_height = max(60, field["height"] + 60)
                            
                            search_bbox = [search_x, search_y, search_x + search_width, search_y + search_height]
                            
                            # Crop this region
                            cropped_img = self.crop_signature_region(page, search_bbox)
                            
                            if cropped_img is not None and self.is_signature(cropped_img):
                                signature_data = {
                                    'page': page_num + 1,
                                    'type': 'field_region',
                                    'field_index': field_idx,
                                    'width': cropped_img.shape[1],
                                    'height': cropped_img.shape[0],
                                    'identifier': f"page_{page_num+1}_field_{field_idx}",
                                    'signature_field': {
                                        'label': field['text'],
                                        'distance': 0  # Direct association
                                    },
                                    'position': {
                                        'x': search_bbox[0],
                                        'y': search_bbox[1],
                                        'width': search_bbox[2] - search_bbox[0],
                                        'height': search_bbox[3] - search_bbox[1]
                                    }
                                }
                                
                                # Save image to disk if requested
                                if save_images and self.output_dir:
                                    signature_filename = f"signature_page_{page_num+1}_field{field_idx}.png"
                                    full_path = os.path.join(self.output_dir, signature_filename)
                                    cv2.imwrite(full_path, cropped_img)
                                    signature_data['file_path'] = full_path
                                    signature_data['filename'] = signature_filename
                                
                                # Include base64 if requested
                                if include_base64:
                                    signature_data['base64'] = self.image_to_base64(cropped_img)
                                
                                signatures.append(signature_data)
                                logger.info(f"Found signature near field '{field['text']}' on page {page_num+1}")
                                
                        except Exception as e:
                            logger.error(f"Error processing signature field region: {str(e)}")
                
                # Final fallback: render entire page if we have signature fields but no signatures
                if not any(sig['page'] == page_num + 1 for sig in signatures) and signature_fields:
                    try:
                        pix = page.get_pixmap(dpi=150)
                        img_bytes = pix.tobytes("png")
                        nparr = np.frombuffer(img_bytes, np.uint8)
                        img_cv = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
                        
                        if img_cv is not None and self.is_signature(img_cv):
                            signature_data = {
                                'page': page_num + 1,
                                'type': 'rendered_page',
                                'width': img_cv.shape[1],
                                'height': img_cv.shape[0],
                                'identifier': f"page_{page_num+1}_rendered",
                                'signature_fields_on_page': len(signature_fields)
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
    parser = argparse.ArgumentParser(description='Extract signatures from PDF files with field detection')
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