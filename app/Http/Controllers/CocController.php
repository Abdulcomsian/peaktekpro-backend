<?php

namespace App\Http\Controllers;

use App\Models\Coc;
use App\Models\CompanyJob;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Jobs\CocInsuranceJob;
use PDF;
use Illuminate\Support\Facades\Storage;

class CocController extends Controller
{
    public function storeCoc(Request $request, $jobId)
    {
        //Validate Rules
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:25',
            'street' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zip_code' => 'required',
            'insurance' => 'nullable',
            'claim_number' => 'nullable',
            'policy_number' => 'nullable',
            'company_representative' => 'required',
            'company_printed_name' => 'required',
            'company_signed_date' => 'required|date_format:m/d/Y',
            'job_total' => 'required',
            'customer_paid_upgrades' => 'required',
            'deductible' => 'required',
            'acv_check' => 'required',
            'rcv_check' => 'required',
            'supplemental_items' => 'required',
            'awarded_to' => 'required|string',
            'released_to' => 'required|string',
            'conclusion' => 'required|string',
            'sincerely' => 'required|string',
            'status' => 'nullable'
        ]);

        try {

            //Check Job
            $job = CompanyJob::whereId($jobId)->with('summary')->first();
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            //Update QC Inspection
            $coc = Coc::updateOrCreate([
                'company_job_id' => $jobId,
            ],[
                'company_job_id' => $jobId,
                'email' => $request->email,
                'name' => $request->name,
                'phone' => $request->phone,
                'street' => $request->street,
                'city' => $request->city,
                'state' => $request->state,
                'zip_code' => $request->zip_code,
                'insurance' => $request->insurance,
                'claim_number' => $request->claim_number,
                'policy_number' => $request->policy_number,
                'company_representative' => $request->company_representative,
                'company_printed_name' => $request->company_printed_name,
                'company_signed_date' => $request->company_signed_date,
                'job_total' => $request->job_total,
                'customer_paid_upgrades' => $request->customer_paid_upgrades,
                'deductible' => $request->deductible,
                'acv_check' => $request->acv_check,
                'rcv_check' => $request->rcv_check,
                'supplemental_items' => $request->supplemental_items,
                'awarded_to' => $request->awarded_to,
                'released_to' => $request->released_to,
                'conclusion' => $request->conclusion,
                'sincerely' => $request->sincerely,
                'status' => $request->status
            ]);
            
            //Update Status
            if(isset($request->status) && $request->status == true) {
                $job->status_id = 13;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();
                
                //Generate PDF
                $pdf = PDF::loadView('pdf.coc', ['coc' => $coc, 'job' => $job]);
                $pdf_fileName = time() . '.pdf';
                $pdf_filePath = 'coc_pdf/' . $pdf_fileName;
                // Check if the old PDF exists and delete it
                if ($coc->pdf_url) {
                    $oldPdfPath = public_path($coc->pdf_url);
                    if (file_exists($oldPdfPath)) {
                        unlink($oldPdfPath);
                    }
                }
                // Save the new PDF
                Storage::put('public/' . $pdf_filePath, $pdf->output());
    
                //Save PDF Path
                $coc->pdf_url = '/storage/' . $pdf_filePath;
                $coc->save();
            }

            return response()->json([
                'status' => 200,
                'message' => 'COC Added Successfully',
                'data' => $coc
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function updateStatusCoc(Request $request, $jobId)
    {
        //Validate Rules
        $this->validate($request, [
            'status' => 'nullable'
        ]);

        try {

            //Check Job
            $job = CompanyJob::whereId($jobId)->with('summary')->first();
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            //Update QC Inspection
            $coc = Coc::updateOrCreate([
                'company_job_id' => $jobId,
            ],[
                'company_job_id' => $jobId,
                'status' => $request->status
            ]);
            
            //Update Status
            if(isset($request->status) && $request->status == true) {
                $job->status_id = 13;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();
            }

            return response()->json([
                'status' => 200,
                'message' => 'COC Status Updated Successfully',
                'data' => $coc
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }


    public function getCoc($jobId)
    {
        try {

            //Check Job
            $job = CompanyJob::whereId($jobId)->with('summary')->first();
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            $get_coc = Coc::where('company_job_id', $jobId)->first();
            if(is_null($get_coc)) {

                // Create a new stdClass object
                $coc = new \stdClass();
                $coc->homeowner_name = $job->name;
                $coc->homeowner_email = $job->email;
                $coc->homeowner_address = $job->address;
                $coc->insurance = !is_null($job->summary) ? $job->summary->insurance : '';
                $coc->insurance_email = !is_null($job->summary) ? $job->summary->email : '';
                $coc->policy_number = !is_null($job->summary) ? $job->summary->policy_number : '';
                $coc->claim_number = !is_null($job->summary) ? $job->summary->claim_number : '';

                return response()->json([
                    'status' => 200,
                    'message' => 'COC Not Found',
                    'data' => $coc
                ], 200);
            }
            
            //Update COC
            $get_coc->homeowner_name = $job->name;
            $get_coc->homeowner_email = $job->email;
            $get_coc->homeowner_address = $job->address;
            $get_coc->insurance = !is_null($job->summary) ? $job->summary->insurance : '';
            $get_coc->insurance_email = !is_null($job->summary) ? $job->summary->email : '';
            $get_coc->policy_number = !is_null($job->summary) ? $job->summary->policy_number : '';
            $get_coc->claim_number = !is_null($job->summary) ? $job->summary->claim_number : '';

            return response()->json([
                'status' => 200,
                'message' => 'COC Found Successfully',
                'data' => $get_coc
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    
    public function CocInsuranceEmail(Request $request, $id)
    {
        $this->validate($request, [
            'coc_insurance_email_sent' => 'nullable',
            'send_to' => 'required|email',
            'subject' => 'required|string',
            'email_body' => 'required|string',
            'attachments' => 'nullable|array',
        ]);
        
        try {
            
            //Check COC
            $coc = Coc::where('id', $id)->first();
            if(!$coc) {
                return response()->json([
                    'status' => 422,
                    'message' => 'COC Not Found'
                ], 422);
            }
            
            //Send Email
            if(isset($request->attachments)) {
                $attachments = $request->file('attachments');
            } else {
                $attachments = [];
            }
            
            dispatch(new CocInsuranceJob($request->send_to,$request->subject,$request->email_body,$attachments));
            
            //Update COC
            $coc->coc_insurance_email_sent = $request->coc_insurance_email_sent;
            $coc->save();
            
            return response()->json([
                'status' => 200,
                'message' => 'Email Sent successfully',
                'data' => []
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
