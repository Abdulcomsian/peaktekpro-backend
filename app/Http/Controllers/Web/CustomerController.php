<?php

namespace App\Http\Controllers\Web;

// use PDF;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use DOMDocument;
use Carbon\Carbon;
use App\Models\CompanyJob;
use Illuminate\Http\Request;
use App\Models\AgreementContent;
use App\Models\CustomerAgreement;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    public function updateCustomerAgreement(Request $request, $id)
    {
        try {

            // dd($id);
            //Check Agreement
            $agreement = CustomerAgreement::where('company_job_id',$id)->first();
            if(!$agreement) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Agreement Not Found for this job'
                ], 422);
            }
            $user = Auth::user();
            // $companyId = $user->company_id;
            $companyId = 1;

            $agreement_content = AgreementContent::where('company_id',$companyId)->first();
            // dd($agreement_content,$agreement);
            //here I will parse the data before generating the pdf

            // // Parse the content using DOMDocument
            // $dom = new DOMDocument();
            // libxml_use_internal_errors(true); // Suppress HTML parsing warnings

            // $agreementContent = mb_convert_encoding($agreement_content->content, 'HTML-ENTITIES', 'UTF-8');

            // $dom->loadHTML('<div>' . $agreementContent  . '</div>'); // Wrap content in a div for parsing
            // libxml_clear_errors();

            // function parseContent($element)
            // {
            //     $parsed = [];
            //     foreach ($element->childNodes as $child) {
            //         $tagName = $child->nodeName;

            //         if (preg_match('/^h[1-6]$/', $tagName)) {
            //             // Heading
            //             $parsed[] = [
            //                 'type' => 'heading',
            //                 'level' => substr($tagName, 1),
            //                 'content' => trim($child->nodeValue),
            //             ];
            //         } elseif ($tagName === 'p') {
            //             // Paragraph
            //             $parsed[] = [
            //                 'type' => 'paragraph',
            //                 'content' => trim($child->nodeValue),
            //             ];
            //         } elseif ($tagName === 'ol' || $tagName === 'ul') {
            //             // Ordered or Unordered List
            //             $listType = $tagName === 'ol' ? 'orderedList' : 'unorderedList';
            //             $items = [];
            //             foreach ($child->childNodes as $li) {
            //                 if ($li->nodeName === 'li') {
            //                     // Recursively parse nested lists
            //                     $items[] = [
            //                         'content' => trim($li->nodeValue),
            //                         'subList' => parseContent($li),
            //                     ];
            //                 }
            //             }
            //             $parsed[] = [
            //                 'type' => $listType,
            //                 'items' => $items,
            //             ];
            //         }
            //     }
            //     return $parsed;
            // }

            // // Start parsing from the body or div element
            // $body = $dom->getElementsByTagName('div')->item(0);
            // $parsedContent = parseContent($body);

            // dd($)
            // return response()->json([
            //     'data'=> $parsedContent
            // ]);

            // $data = [
            //     'title' => 'Welcome to Techsolutionstuff'
            // ];
            //Generate PDF
            // $pdf = PDF::loadView('pdf.customer-agreement', ['data' => $agreement, 'content'=>$parsedContent]);
        
            // dd(['data' => $agreement,'content'=>$agreement_content]);

            // dd($agreement_content->content);
     
            $pdf = Pdf::loadView('pdf.customer',['data' => $agreement,'content'=>$agreement_content]);
            $pdf->setPaper('A4', 'portrait');
            // $pdf = Pdf::loadView('pdf.customer', $data);
            return $pdf->stream('customer_agreement.pdf');

            $pdf_fileName = time() . '.pdf';
            $pdf_filePath = 'customer_agreement_pdf/' . $pdf_fileName;
            // Check if the old PDF exists and delete it
            if ($agreement->sign_pdf_url) {
                $oldPdfPath = public_path($agreement->sign_pdf_url);
                if (file_exists($oldPdfPath)) {
                    unlink($oldPdfPath);
                }
            }
            // Save the new PDF
            Storage::put('public/' . $pdf_filePath, $pdf->output());
            //Save PDF Path
            $agreement->sign_pdf_url = '/storage/' . $pdf_filePath;
            $agreement->save();

            //Update Job Status
            $job = CompanyJob::find($agreement->company_job_id);
            $job->status_id = 2;
            $job->date = Carbon::now()->format('Y-m-d');
            $job->save();

            // dd($agreement);
            return redirect()->view('pdf.customer-agreement.blade.php',compact('agreement',$agreement));

            // return response()->json([
            //     'status' => 200,
            //     'message' => 'Agreement pdf Generated Successfully',
            //     'agreement' => $agreement
            // ], 200);
        
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
            }

    }
}
