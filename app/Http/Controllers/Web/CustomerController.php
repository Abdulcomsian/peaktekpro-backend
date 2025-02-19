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
