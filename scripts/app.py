#!/usr/bin/env python3
"""
Test script to debug signature detection issues
Run this to see exactly what the detection algorithm finds
"""

import sys
import json
import fitz
import cv2
import numpy as np

def debug_pdf_analysis(pdf_path):
    """Debug what the PDF contains"""
    print(f"=== DEBUGGING PDF: {pdf_path} ===\n")
    
    try:
        doc = fitz.open(pdf_path)
        print(f"PDF has {len(doc)} pages\n")
        
        for page_num in range(len(doc)):
            page = doc.load_page(page_num)
            print(f"--- PAGE {page_num + 1} ---")
            
            # 1. Check form fields
            widgets = page.widgets()
            print(f"Form fields found: {len(widgets)}")
            
            for i, widget in enumerate(widgets):
                field_type_names = {
                    0: "Unknown",
                    1: "Button", 
                    2: "Text",
                    3: "Choice",
                    4: "Signature"
                }
                
                field_type_name = field_type_names.get(widget.field_type, f"Type_{widget.field_type}")
                field_value = widget.field_value or ""
                field_name = widget.field_name or f"Field_{i}"
                
                print(f"  Field {i+1}: {field_name}")
                print(f"    Type: {field_type_name}")
                print(f"    Value: '{field_value}'")
                print(f"    Has content: {bool(field_value and field_value.strip())}")
                print(f"    Bbox: {list(widget.rect)}")
                print()
            
            # 2. Check embedded images
            image_list = page.get_images(full=True)
            print(f"Embedded images found: {len(image_list)}")
            
            for i, img in enumerate(image_list):
                try:
                    xref = img[0]
                    base_image = doc.extract_image(xref)
                    image_bytes = base_image["image"]
                    nparr = np.frombuffer(image_bytes, np.uint8)
                    img_cv = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
                    
                    if img_cv is not None:
                        h, w = img_cv.shape[:2]
                        print(f"  Image {i+1}: {w}x{h} pixels")
                        
                        # Quick signature test
                        gray = cv2.cvtColor(img_cv, cv2.COLOR_BGR2GRAY)
                        thresh = cv2.adaptiveThreshold(gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
                                                    cv2.THRESH_BINARY_INV, 11, 2)
                        ink_pixels = cv2.countNonZero(thresh)
                        total_pixels = thresh.size
                        coverage = ink_pixels / total_pixels if total_pixels > 0 else 0
                        
                        print(f"    Ink coverage: {coverage:.4f}")
                        print(f"    Likely signature: {0.005 < coverage < 0.3}")
                    
                except Exception as e:
                    print(f"  Image {i+1}: Error analyzing - {e}")
                print()
            
            # 3. Check text content
            text_dict = page.get_text("dict")
            text_blocks = text_dict.get("blocks", [])
            
            signature_related_text = []
            for block in text_blocks:
                if "lines" in block:
                    for line in block["lines"]:
                        for span in line.get("spans", []):
                            text = span.get("text", "").strip()
                            if text and any(keyword in text.lower() for keyword in 
                                          ['signature', 'sign', 'printed name', 'date signed']):
                                bbox = line.get("bbox", [0, 0, 0, 0])
                                signature_related_text.append({
                                    'text': text,
                                    'bbox': bbox
                                })
            
            print(f"Signature-related text found: {len(signature_related_text)}")
            for item in signature_related_text:
                print(f"  Text: '{item['text']}'")
                print(f"  Position: {item['bbox']}")
            print()
            
        doc.close()
        
    except Exception as e:
        print(f"Error analyzing PDF: {e}")

def test_signature_detection(pdf_path):
    """Test the actual signature detection"""
    print(f"\n=== RUNNING SIGNATURE DETECTION ===\n")
    
    # Import the detection function
    try:
        from pdf_signature_api import process_pdf_file
        result = process_pdf_file(pdf_path)
        print("Detection Result:")
        print(json.dumps(result, indent=2))
    except ImportError:
        print("Could not import pdf_signature_api. Make sure the script is in the same directory.")
    except Exception as e:
        print(f"Error running detection: {e}")

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python test_signature_detection.py <pdf_file>")
        sys.exit(1)
    
    pdf_file = sys.argv[1]
    
    # Run debug analysis
    debug_pdf_analysis(pdf_file)
    
    # Run signature detection
    test_signature_detection(pdf_file)

# ================================
# Expected Output for Unsigned PDF
# ================================

"""
=== DEBUGGING PDF: unsigned_document.pdf ===

PDF has 2 pages

--- PAGE 1 ---
Form fields found: 0
Embedded images found: 0
Signature-related text found: 3
  Text: 'Customer Signature:'
  Position: [50, 100, 150, 120]
  Text: 'Company Representative Signature:'
  Position: [50, 150, 250, 170]
  Text: 'Customer Signature:'
  Position: [50, 200, 150, 220]

--- PAGE 2 ---
Form fields found: 0
Embedded images found: 0
Signature-related text found: 0

=== RUNNING SIGNATURE DETECTION ===

Detection Result:
{
  "success": true,
  "count": 0,
  "signatures": {},
  "message": "No signatures found - document appears to be unsigned",
  "analysis_method": "comprehensive_detection"
}
"""