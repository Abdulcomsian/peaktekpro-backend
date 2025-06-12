#!/usr/bin/env python3
"""
Accurate PDF Signature Detection focusing on actual filled content vs empty fields
"""
import os
import sys
import fitz  # PyMuPDF
import re
import json

def is_field_actually_filled(field_value, field_name=''):
    """
    Determine if a form field contains actual user-entered content
    vs just placeholder text or field labels
    """
    if not field_value:
        return False
    
    # Clean the value
    value = str(field_value).strip()
    
    # Empty or too short
    if len(value) < 2:
        return False
    
    # Common empty field indicators
    empty_patterns = [
        r'^[\s_\-\.]+$',  # Just underscores, spaces, dashes, dots
        r'^_{2,}$',       # Multiple underscores
        r'^\s*$',         # Just whitespace
        r'^\.{3,}$',      # Multiple dots
        r'^-{2,}$',       # Multiple dashes
    ]
    
    for pattern in empty_patterns:
        if re.match(pattern, value):
            return False
    
    # Check if value is just the field name/label
    if field_name:
        # Remove common separators and compare
        clean_field_name = field_name.lower().replace('_', ' ').replace(':', '').strip()
        clean_value = value.lower().replace('_', ' ').replace(':', '').strip()
        
        # If the value is just the field name, it's not filled
        if clean_value == clean_field_name:
            return False
        
        # Check if value is just field label keywords
        label_keywords = [
            'signature', 'customer signature', 'company representative signature',
            'printed name', 'date signed', 'date', 'name', 'sign here',
            'type here', 'your signature', 'your name', 'n/a', 'na'
        ]
        
        if clean_value in label_keywords:
            return False
    
    # If we get here, the field likely contains actual content
    return True

def detect_pdf_signatures_accurately(pdf_path, verbose=False):
    """
    Accurately detect signatures in PDF forms
    Returns detailed information about signature fields and their fill status
    """
    try:
        doc = fitz.open(pdf_path)
        
        results = {
            'filename': os.path.basename(pdf_path),
            'total_pages': len(doc),
            'signature_fields': [],
            'filled_signatures': [],
            'empty_signature_fields': [],
            'other_evidence': [],
            'is_signed': False
        }
        
        for page_num in range(len(doc)):
            page = doc.load_page(page_num)
            
            # Check form fields
            for widget in page.widgets():
                field_info = {
                    'page': page_num + 1,
                    'field_name': widget.field_name or 'Unnamed',
                    'field_type': widget.field_type,
                    'field_value': widget.field_value or '',
                    'rect': list(widget.rect) if widget.rect else None
                }
                
                # Handle signature fields
                if widget.field_type == fitz.PDF_WIDGET_TYPE_SIGNATURE:
                    field_info['field_type_name'] = 'Signature'
                    
                    # Check if actually signed
                    is_signed = False
                    
                    # Method 1: Check field value
                    if is_field_actually_filled(widget.field_value, widget.field_name):
                        is_signed = True
                    
                    # Method 2: Check for digital signature data
                    try:
                        xref = widget.xref
                        if xref > 0:
                            obj = doc.xref_object(xref)
                            if '/V' in obj and obj['/V']:
                                # Has a value dictionary entry
                                v_str = str(obj['/V']).strip()
                                if v_str and v_str not in ['<<>>', '{}', '']:
                                    is_signed = True
                    except:
                        pass
                    
                    # Method 3: Check for appearance
                    try:
                        if hasattr(widget, '_annot') and widget._annot:
                            ap = widget._annot.get_ap()
                            if ap:
                                is_signed = True
                    except:
                        pass
                    
                    field_info['is_signed'] = is_signed
                    results['signature_fields'].append(field_info)
                    
                    if is_signed:
                        results['filled_signatures'].append(field_info)
                    else:
                        results['empty_signature_fields'].append(field_info)
                
                # Handle text fields that might contain signatures
                elif widget.field_type in [fitz.PDF_WIDGET_TYPE_TEXT, fitz.PDF_WIDGET_TYPE_FREETEXT]:
                    field_name_lower = (widget.field_name or '').lower()
                    
                    # Check if it's a signature-related text field
                    is_signature_related = any(kw in field_name_lower for kw in [
                        'signature', 'signed', 'printed name', 'print name',
                        'date signed', 'signing date'
                    ])
                    
                    if is_signature_related:
                        field_info['field_type_name'] = 'Text (Signature-related)'
                        
                        # Check if it has real content
                        is_filled = is_field_actually_filled(widget.field_value, widget.field_name)
                        field_info['is_filled'] = is_filled
                        
                        results['signature_fields'].append(field_info)
                        
                        if is_filled:
                            # Additional validation for signature text fields
                            value = str(widget.field_value).strip()
                            
                            # For printed name fields, check if it looks like a name
                            if 'printed name' in field_name_lower:
                                # Simple name validation - at least 2 characters, not all numbers
                                if len(value) >= 2 and not value.isdigit():
                                    results['filled_signatures'].append(field_info)
                                else:
                                    results['empty_signature_fields'].append(field_info)
                            
                            # For date fields, check if it looks like a date
                            elif 'date' in field_name_lower:
                                # Simple date pattern check
                                date_patterns = [
                                    r'\d{1,2}[/-]\d{1,2}[/-]\d{2,4}',  # MM/DD/YYYY or MM-DD-YYYY
                                    r'\d{4}[/-]\d{1,2}[/-]\d{1,2}',    # YYYY-MM-DD
                                    r'\d{1,2}\s+\w+\s+\d{2,4}',        # DD Month YYYY
                                ]
                                
                                is_date = any(re.search(pattern, value) for pattern in date_patterns)
                                if is_date:
                                    results['filled_signatures'].append(field_info)
                                else:
                                    results['empty_signature_fields'].append(field_info)
                            
                            # For other signature fields
                            else:
                                results['filled_signatures'].append(field_info)
                        else:
                            results['empty_signature_fields'].append(field_info)
            
            # Check for annotation-based signatures
            for annot in page.annots():
                annot_type = annot.type[0] if annot.type else None
                
                if annot_type == fitz.PDF_ANNOT_INK:
                    # Ink annotation (drawn signature)
                    results['other_evidence'].append({
                        'page': page_num + 1,
                        'type': 'drawn_signature',
                        'description': 'Ink/Drawing annotation found'
                    })
                    results['filled_signatures'].append({
                        'page': page_num + 1,
                        'type': 'drawn_signature',
                        'is_signed': True
                    })
                
                elif annot_type == fitz.PDF_ANNOT_STAMP:
                    # Check if it's a signature stamp
                    content = annot.info.get('content', '').lower()
                    subject = annot.info.get('subject', '').lower()
                    
                    if any(kw in content + subject for kw in ['sign', 'signature', 'approved']):
                        results['other_evidence'].append({
                            'page': page_num + 1,
                            'type': 'signature_stamp',
                            'content': annot.info.get('content', '')
                        })
                        results['filled_signatures'].append({
                            'page': page_num + 1,
                            'type': 'signature_stamp',
                            'is_signed': True
                        })
        
        doc.close()
        
        # Determine if document is signed
        results['is_signed'] = len(results['filled_signatures']) > 0
        
        # Add summary
        results['summary'] = {
            'total_signature_fields': len(results['signature_fields']),
            'filled_signatures': len(results['filled_signatures']),
            'empty_signature_fields': len(results['empty_signature_fields']),
            'other_evidence': len(results['other_evidence'])
        }
        
        if verbose:
            print_results(results)
        
        return results
        
    except Exception as e:
        print(f"Error processing PDF: {str(e)}")
        import traceback
        traceback.print_exc()
        return None

def print_results(results):
    """Pretty print the results"""
    print(f"\n{'='*60}")
    print(f"PDF SIGNATURE ANALYSIS: {results['filename']}")
    print(f"{'='*60}")
    
    print(f"\nDocument Status: {'SIGNED' if results['is_signed'] else 'UNSIGNED'}")
    
    print(f"\nSummary:")
    print(f"  - Total signature fields: {results['summary']['total_signature_fields']}")
    print(f"  - Filled signatures: {results['summary']['filled_signatures']}")
    print(f"  - Empty signature fields: {results['summary']['empty_signature_fields']}")
    print(f"  - Other evidence: {results['summary']['other_evidence']}")
    
    if results['filled_signatures']:
        print(f"\nFilled Signatures:")
        for sig in results['filled_signatures']:
            print(f"  - Page {sig['page']}: {sig.get('field_name', sig.get('type', 'Unknown'))}")
            if 'field_value' in sig and sig['field_value']:
                print(f"    Value: '{sig['field_value']}'")
    
    if results['empty_signature_fields']:
        print(f"\nEmpty Signature Fields:")
        for sig in results['empty_signature_fields']:
            print(f"  - Page {sig['page']}: {sig.get('field_name', 'Unknown')}")
    
    print(f"\n{'='*60}\n")

def compare_two_pdfs(pdf1_path, pdf2_path):
    """Compare two PDFs for testing"""
    print("\n" + "="*80)
    print("COMPARING TWO PDFS")
    print("="*80)
    
    results = []
    for pdf_path in [pdf1_path, pdf2_path]:
        print(f"\nAnalyzing: {pdf_path}")
        result = detect_pdf_signatures_accurately(pdf_path, verbose=True)
        if result:
            results.append(result)
    
    if len(results) == 2:
        print("\n" + "="*80)
        print("COMPARISON RESULTS")
        print("="*80)
        
        for i, result in enumerate(results, 1):
            status = "SIGNED" if result['is_signed'] else "UNSIGNED"
            print(f"\nPDF {i}: {result['filename']} - {status}")
            print(f"  Filled signatures: {len(result['filled_signatures'])}")

def main():
    if len(sys.argv) < 2:
        print("Usage: python accurate_detector.py <pdf_file> [<pdf_file2>]")
        sys.exit(1)
    
    if len(sys.argv) == 3:
        compare_two_pdfs(sys.argv[1], sys.argv[2])
    else:
        result = detect_pdf_signatures_accurately(sys.argv[1], verbose=True)
        
        # Save result to JSON
        if result:
            output_file = 'signature_analysis.json'
            with open(output_file, 'w') as f:
                json.dump(result, f, indent=2)
            print(f"Results saved to: {output_file}")

if __name__ == '__main__':
    main()