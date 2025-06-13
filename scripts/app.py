import os
import sys
import json
import logging
import fitz  # PyMuPDF
import cv2
import numpy as np
from datetime import datetime

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Configuration
SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
UPLOAD_FOLDER = os.path.join(SCRIPT_DIR, '../../uploads')
SIGNATURE_FOLDER = os.path.join(SCRIPT_DIR, '../../signatures')

os.makedirs(UPLOAD_FOLDER, exist_ok=True)
os.makedirs(SIGNATURE_FOLDER, exist_ok=True)

def is_signature(image):
    """Signature detection logic"""
    try:
        gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
        thresh = cv2.adaptiveThreshold(gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
                                    cv2.THRESH_BINARY_INV, 11, 2)
        
        ink_pixels = cv2.countNonZero(thresh)
        total_pixels = thresh.size
        coverage = ink_pixels / total_pixels
        
        contours, _ = cv2.findContours(thresh, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
        if not contours:
            return False
            
        largest_contour = max(contours, key=cv2.contourArea)
        x, y, w, h = cv2.boundingRect(largest_contour)
        aspect_ratio = w / h
        
        return (0.001 < coverage < 0.5) and (0.3 < aspect_ratio < 8)
        
    except Exception as e:
        logger.error(f"Signature detection error: {str(e)}")
        return False

def extract_signatures(pdf_path):
    signatures = {}
    try:
        doc = fitz.open(pdf_path)
        
        for page_num in range(len(doc)):
            page = doc.load_page(page_num)
            image_list = page.get_images(full=True)
            
            # Process embedded images
            if image_list:
                for img_index, img in enumerate(image_list):
                    xref = img[0]
                    base_image = doc.extract_image(xref)
                    image_bytes = base_image["image"]
                    nparr = np.frombuffer(image_bytes, np.uint8)
                    img_cv = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
                    
                    if is_signature(img_cv):
                        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
                        signature_filename = f"sig_{timestamp}_p{page_num+1}_i{img_index}.png"
                        full_path = os.path.join(SIGNATURE_FOLDER, signature_filename)
                        cv2.imwrite(full_path, img_cv)
                        signatures[f"page_{page_num+1}_img_{img_index}"] = signature_filename
            
            # Fallback to rendered page
            if not image_list or not signatures:
                pix = page.get_pixmap(dpi=150)
                img_bytes = pix.tobytes("png")
                nparr = np.frombuffer(img_bytes, np.uint8)
                img_cv = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
                
                if is_signature(img_cv):
                    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
                    signature_filename = f"sig_{timestamp}_p{page_num+1}_rendered.png"
                    full_path = os.path.join(SIGNATURE_FOLDER, signature_filename)
                    cv2.imwrite(full_path, img_cv)
                    signatures[f"page_{page_num+1}"] = signature_filename
        
        doc.close()
        
    except Exception as e:
        logger.error(f"PDF processing error: {str(e)}")
        raise RuntimeError(f"PDF processing failed: {str(e)}")
    
    return signatures

if __name__ == '__main__':
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No PDF file provided"}))
        sys.exit(1)
    
    pdf_path = sys.argv[1]
    
    try:
        signatures = extract_signatures(pdf_path)
        print(json.dumps({
            "success": True,
            "signatures": signatures,
            "message": "Signatures extracted successfully"
        }))
    except Exception as e:
        print(json.dumps({
            "success": False,
            "error": str(e),
            "message": "Failed to extract signatures"
        }))
        sys.exit(1)