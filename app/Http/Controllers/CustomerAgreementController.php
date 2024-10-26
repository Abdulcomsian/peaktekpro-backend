<?php

namespace App\Http\Controllers;

use PDF;
use Carbon\Carbon;
use App\Jobs\SignEmailJob;
use App\Models\CompanyJob;
use App\Mail\SignEmailMail;
use Illuminate\Http\Request;
use App\Models\CompanyJobSummary;
use App\Models\CustomerAgreement;
use App\Models\ProjectDesignTitle;
use App\Events\JobStatusUpdateEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class CustomerAgreementController extends Controller
{
    public function customerAgreement(Request $request, $id)
    {
        try {

            //Validate Request
            $this->validate($request, [
                'street' => 'nullable',
                'city' => 'nullable',
                'state' => 'nullable',
                'zip_code' => 'nullable',
                'insurance' => 'nullable',
                'claim_number' => 'nullable',
                'policy_number' => 'nullable',
                'company_signature' => 'nullable',
                'company_printed_name' => 'nullable',
                'company_date' => 'nullable|date_format:m/d/Y',
                'customer_signature' => 'nullable',
                'customer_printed_name' => 'nullable',
                'customer_date' => 'nullable|date_format:m/d/Y',
                'agreement_date' => 'nullable|date_format:m/d/Y',
                'customer_name' => 'nullable|string',
                'status' => 'nullable'
            ]);

            //Check Job
            $job = CompanyJob::find($id);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            //Update Agreement
            $agreement = CustomerAgreement::updateOrCreate([
                'company_job_id' => $id,
            ],[
                'company_job_id' => $id,
                'street' => $request->street,
                'city' => $request->city,
                'state' => $request->state,
                'zip_code' => $request->zip_code,
                'insurance' => $request->insurance,
                'claim_number' => $request->claim_number,
                'policy_number' => $request->policy_number,
                'company_signature' => $request->company_signature,
                'company_printed_name' => $request->company_printed_name,
                'company_date' => $request->company_date,
                'customer_signature' => $request->customer_signature,
                'customer_printed_name' => $request->customer_printed_name,
                'customer_date' => $request->customer_date,
                'agreement_date' => $request->agreement_date,
                'customer_name' => $request->customer_name,
                'status' => $request->status,
            ]);

            // Store values in project_design_titles because it will used in design meeting pdf making
            $projectDesignTitle = ProjectDesignTitle::updateOrCreate(
                ['company_job_id' => $id],
                [
            'company_job_id' => $id,
            'first_name' => explode(' ', $request->customer_name)[0],
            'last_name' => explode(' ', $request->customer_name)[1] ?? '',
            'company_name' => $job->name,
            'address' => $request->street,
            'city' => $request->city,
            'state' => $request->state,
            'zip' => $request->zip_code,
            'report_type' => 'Design Meeting', 
            'date' => now()->format('Y-m-d'),
        ]);

        $projectDesignTitle->save();
            
            //Update Status
            if(isset($request->status) && $request->status == true) {
                $job->status_id = 4;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();    
            }

            return response()->json([
                'status' => 200,
                'message' => 'Agreement Created Successfully',
                'agreement' => $agreement
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function customerAgreementStatus(Request $request, $id)
    {
        try {
            //Validate Request
            $this->validate($request, [
                'status' => 'nullable'
            ]);

            //Check Job
            $job = CompanyJob::find($id);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            //Update Agreement
            $agreement = CustomerAgreement::updateOrCreate([
                'company_job_id' => $id,
            ],[
                'company_job_id' => $id,
                'status' => $request->status,
            ]);
            
            //Update Status
            if(isset($request->status) && $request->status == true) {
                $job->status_id = 4;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();    
            }

            return response()->json([
                'status' => 200,
                'message' => 'Agreement Status Updated Successfully',
                'agreement' => $agreement
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getCustomerAgreement($id)
    {
        try {

            //Check Agreement
            $agreement = CustomerAgreement::find($id);
            if(!$agreement) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Agreement Not Found'
                ], 422);
            }

            //Get Job
            $job = CompanyJob::find($agreement->company_job_id);
            if($job) {
                $agreement->name = $job->name;
                $agreement->email = $job->email;
                $agreement->phone = $job->phone;
                if($agreement->isComplete()) {
                    $agreement->is_complete = true;
                } else {
                    $agreement->is_complete = false;
                }
            }

            return response()->json([
                'status' => 200,
                'message' => 'Agreement Found Successfully',
                'agreement' => $agreement
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    //when the user sign then the pdf generate
    public function updateCustomerAgreement(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'sign_image' => 'nullable',
        ]);
        try {

            //Check Agreement
            $agreement = CustomerAgreement::find($id);
            if(!$agreement) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Agreement Not Found'
                ], 422);
            }

            // Get base64 image data
            $base64Image = $request->input('sign_image');
            $data = substr($base64Image, strpos($base64Image, ',') + 1);
            $decodedImage = base64_decode($data);

            // Generate a unique filename
            $filename = 'image_' . time() . '.png';
            // Check if the old image exists and delete it
            if ($agreement->sign_image_url) {
                $oldImagePath = public_path($agreement->sign_image_url);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            // Save the new image
            Storage::disk('public')->put('agreement_signature/' . $filename, $decodedImage);
            $imageUrl = '/storage/agreement_signature/' . $filename;

            //Save Image Path
            $agreement->sign_image_url = $imageUrl;
            $agreement->save();

            //Generate PDF
            $pdf = PDF::loadView('pdf.customer-agreement', ['data' => $agreement]);
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

            //Fire an Event
            event(new JobStatusUpdateEvent('Refresh Pgae'));

            return response()->json([
                'status' => 200,
                'message' => 'Signature Image Added Successfully',
                'agreement' => $agreement
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }

    }

    public function signCustomerAgreementByEmail(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'url' => 'nullable',
        ]);

        try {

            //Check Agreement
            $agreement = CustomerAgreement::find($id);
            if(!$agreement) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Agreement Not Found'
                ], 422);
            }

            //Send Email
            $encrypted_url = Crypt::encryptString($request->url);
            $customer = CompanyJob::find($agreement->company_job_id);
            // dispatch(new SignEmailJob($customer,$encrypted_url));
            Mail::to($customer->email)->send(new SignEmailMail($customer,$encrypted_url));

            return response()->json([
                'status' => 200,
                'message' => 'Email Sent Successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function checkCustomerAgreement($jobId)
    {
        try {
            //Check Job
            $job = CompanyJob::find($jobId);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            //Check Agreement
            $agreement = CustomerAgreement::where('company_job_id', $jobId)->first();

            ////
            $job_summary = CompanyJobSummary::select('id','insurance','policy_number','email','insurance_representative','claim_number')
            ->where('company_job_id', $job->id)->first();
            // if(!$job_summary) {
            //     return response()->json([
            //         'status' => 200,
            //         'message' => 'Job Summary Not Yet Created',
            //     ], 200);
            // }

            ///
            if(!$agreement) {

                //Job Information
                $job_info = new \stdClass();
                $job_info->name = $job->name;
                $job_info->email = $job->email;
                $job_info->phone = $job->phone;
                $job->is_complete = false;
                
                return response()->json([
                    'status' => 200,
                    'message' => 'Customer Agreement Not Found',
                    'data' => [
                        'agreement' => $job_info,
                        'jobsummary' =>  $job_summary
                    ]
                    
                ], 200);
            }

            //Get Job
            $agreement->name = $job->name;
            $agreement->email = $job->email;
            $agreement->phone = $job->phone;
            if($agreement->isComplete()) {
                $agreement->is_complete = true;
            } else {
                $agreement->is_complete = false;
            }

            return response()->json([
                'status' => 200,
                'message' => 'Customer Agreement Found Successfully',
                'data' => [ 
                   'agreement' => $agreement,
                'jobsummary' => $job_summary,
                ]
                
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getSignCustomerAgreement($jobId)
    {
        try {

            //Check Job
            $job = CompanyJob::find($jobId);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            //Get Agreement
            $agreement = CustomerAgreement::find($job->id);

            return response()->json([
                'status' => 200,
                'message' => 'Agreement Found Successfully',
                'agreement' => $agreement
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function signCustomerByEmail(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'sign_image' => 'nullable',
        ]);

        try {

            //Check Agreement
            $agreement = CustomerAgreement::find($id);
            if(!$agreement) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Agreement Not Found'
                ], 422);
            }

            // Get base64 image data
            $base64Image = $request->input('sign_image');
            $data = substr($base64Image, strpos($base64Image, ',') + 1);
            $decodedImage = base64_decode($data);

            // Generate a unique filename
            $filename = 'image_' . time() . '.png';
            // Check if the old image exists and delete it
            if ($agreement->sign_image_url) {
                $oldImagePath = public_path($agreement->sign_image_url);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            // Save the new image
            Storage::disk('public')->put('agreement_signature/' . $filename, $decodedImage);
            $imageUrl = '/storage/agreement_signature/' . $filename;

            //Save Image Path
            $agreement->sign_image_url = $imageUrl;
            $agreement->save();

            //Generate PDF
            $pdf = PDF::loadView('pdf.customer-agreement', ['data' => $agreement]);
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
            $job->save();

            //Fire an Event
            event(new JobStatusUpdateEvent('Refresh Pgae'));

            return response()->json([
                'status' => 200,
                'message' => 'Signature Image Added Successfully',
                'agreement' => $agreement
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
