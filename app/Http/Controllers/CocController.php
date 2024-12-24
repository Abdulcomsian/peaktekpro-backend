<?php

namespace App\Http\Controllers;

use PDF;
use Carbon\Carbon;
use App\Models\Coc;
// use App\Notifications\CustomNotifiable;
use App\CustomNotifiable;
use App\Models\CompanyJob;
use Illuminate\Http\Request;
use App\Jobs\CocInsuranceJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CocInsuranceNotification;
use Exception;

class CocController extends Controller
{
    public function storeCoc(Request $request, $jobId)
    {
        //Validate Rules
        $this->validate($request, [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:25',
            'street' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'zip_code' => 'nullable',
            'insurance' => 'nullable',
            'claim_number' => 'nullable',
            'policy_number' => 'nullable',
            'company_representative' => 'nullable',
            'company_printed_name' => 'nullable',
            'company_signed_date' => 'nullable|date_format:m/d/Y',
            'job_total' => 'nullable',
            'customer_paid_upgrades' => 'nullable',
            'deductible' => 'nullable',
            'acv_check' => 'nullable',
            'rcv_check' => 'nullable',
            'supplemental_items' => 'nullable',
            'awarded_to' => 'nullable|string',
            'released_to' => 'nullable|string',
            'conclusion' => 'nullable|string',
            'sincerely' => 'nullable|string',
            'status' => 'nullable',
            'notes' => 'nullable',
            'customer_signature' => 'nullable',
            'company_representative_signature' => 'nullable'
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
                // 'status' => $request->status,
                'notes' => $request->notes,

            ]);

            //handle signatures
             // Handle Base64 Signatures
             if ($request->customer_signature) {
                $coc->customer_signature = $this->saveBase64Image($request->customer_signature, 'coc_signature');

            }
            if ($request->company_representative_signature) {
                $coc->company_representative_signature = $this->saveBase64Image($request->company_representative_signature, 'company_representative_signature');
            }
            
            //Update Status
            // if(isset($request->status) && $request->status == true) {
            //     $job->status_id = 13;
            //     $job->date = Carbon::now()->format('Y-m-d');
            //     $job->save();
                
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
            // }

            return response()->json([
                'status' => 200,
                'message' => 'COC Added Successfully',
                'data' => [
                    'id'=> $coc->id,
                    'company_job_id' => $coc->company_job_id,
                    'name' => $coc->name,
                    'email' => $coc->email,
                    'phone' => $coc->phone,
                    'street' => $coc->street,
                    'city' => $coc->city,
                    'state' => $coc->state,
                    'zip_code' => $coc->zip_code,
                    'insurance' => $coc->insurance,
                    'claim_number' => $coc->claim_number,
                    'status' => $coc->status,
                    'policy_number' => $coc->policy_number,
                    'company_repreesentative' => $coc->company_repreesentative,
                    'company_signature'=> $coc->company_representative_signature,
                    'compnay_printed_name' => $coc->compnay_printed_name,
                    'company_signed_date' => $coc->company_signed_date,
                    'job_total' => $coc->job_total,
                    'customer_paid_upgrades' => $coc->customer_paid_upgrades,
                    'deductible' => $coc->deductible,
                    'acv_check' => $coc->acv_check,
                    'rcv_check' => $coc->rcv_check,
                    'supplemental_items' => $coc->supplemental_items,
                    "awarded_to" =>$coc->awarded_to,
                    "released_to" => $coc->released_to,
                    "conclusion" => $coc->conclusion,
                    "sincerely" => $coc->sincerely,
                    "pdf_url" => $coc->pdf_url,
                    "notes" => $coc->notes,
                    "customer_signature" => $coc->customer_signature,
                    "coc_insurance_email_sent" => $coc->coc_insurance_email_sent,
                    "created_at" =>$coc->created_at,
                    "updated_at" =>  $coc->updated_at,
                    "current_stage" => $coc->current_stage,

                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    private function saveBase64Image($base64Image, $directory)
    {
        $data = substr($base64Image, strpos($base64Image, ',') + 1);
        $decodedImage = base64_decode($data);

        // Generate a unique filename
        $filename = 'image_' . time() . '.png';

        // Save the new image
        Storage::disk('public')->put($directory . '/' . $filename, $decodedImage);

        return '/storage/' . $directory . '/' . $filename;
    }

    public function getCoc($jobId)
    {
        try {
            // Check Job
            $job = CompanyJob::whereId($jobId)->with('summary', 'aggrement', 'readyBuild')->first();

            if (!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            $get_coc = Coc::where('company_job_id', $jobId)->first();
            
            // Create a new stdClass object to hold the data
            $coc = new \stdClass();
            $coc->homeowner_name = !is_null($job->readyBuild) ? $job->readyBuild->home_owner : '';
            $coc->homeowner_email = !is_null($job->readyBuild) ? $job->readyBuild->home_owner_email : '';
            $coc->homeowner_address = $job->address;
            $coc->insurance = !is_null($job->aggrement) ? $job->aggrement->insurance : '';
            $coc->insurance_email = !is_null($job->summary) ? $job->summary->email : '';
            $coc->policy_number = !is_null($job->aggrement) ? $job->aggrement->policy_number : '';
            $coc->claim_number = !is_null($job->aggrement) ? $job->aggrement->claim_number : '';

            // Check if aggrement is not null before accessing its properties
            if (!is_null($job->aggrement)) {
                $coc->street = !is_null($job->aggrement->street) ? $job->aggrement->street : '';
                $coc->city = !is_null($job->aggrement->city) ? $job->aggrement->city : '';
                $coc->state = !is_null($job->aggrement->state) ? $job->aggrement->state : '';
                $coc->zip_code = !is_null($job->aggrement->zip_code) ? $job->aggrement->zip_code : '';
            } else {
                $coc->street = '';
                $coc->city = '';
                $coc->state = '';
                $coc->zip_code = '';
            }

            if (is_null($get_coc)) {
                // If COC doesn't exist, return new object
                return response()->json([
                    'status' => 200,
                    'message' => 'COC Not Found',
                    'data' => $coc
                ], 200);
            }

            // If COC exists, update its values
            $get_coc->name = $coc->homeowner_name;
            $get_coc->email = $coc->homeowner_email;
            // $get_coc->homeowner_address = $coc->homeowner_address;
            $get_coc->insurance = $coc->insurance;
            // $get_coc->insurance_email = $coc->insurance_email;
            $get_coc->policy_number = $coc->policy_number;
            $get_coc->claim_number = $coc->claim_number;
            $get_coc->street = $coc->street;
            $get_coc->city = $coc->city;
            $get_coc->state = $coc->state;
            $get_coc->zip_code = $coc->zip_code;

            // Save the updated COC
            // $get_coc->save();

            // return response()->json([
            //     'status' => 200,
            //     'message' => 'COC Found Successfully',
            //     'data' => $get_coc
            // ], 200);
            return response()->json([
                'status' => 200,
                'message' => 'COC Found Successfully',
                'data' => [
                    'id' => $get_coc->id,
                    'homeowner_name' => $get_coc->name,
                    'homeowner_email' => $get_coc->email,
                    // 'homeowner_address' => $get_coc->homeowner_address, 
                    'homeowner_address' => $job->address,
                    'insurance' => $get_coc->insurance,
                    'insurance_email' =>  !is_null($job->summary) ? $job->summary->email : '', 
                    'policy_number' => $get_coc->policy_number,
                    'claim_number' => $get_coc->claim_number,
                    'street' => $get_coc->street,
                    'city' => $get_coc->city,
                    'state' => $get_coc->state,
                    'zip_code' => $get_coc->zip_code,
                    'status' => $get_coc->status,
                    'company_representative' => $get_coc->company_representative,
                    'company_printed_name' => $get_coc->company_printed_name,
                    'company_signed_date' => $get_coc->company_signed_date,
                    'job_total' => $get_coc->job_total,
                    'customer_paid_upgrades' => $get_coc->customer_paid_upgrades,
                    'deductible' => $get_coc->deductible,
                    'acv_check' => $get_coc->acv_check,
                    'rcv_check' => $get_coc->rcv_check,
                    'supplemental_items' => $get_coc->supplemental_items,
                    'awarded_to' => $get_coc->awarded_to,
                    'released_to' => $get_coc->released_to,
                    'conclusion' => $get_coc->conclusion,
                    'sincerely' => $get_coc->sincerely,
                    'pdf_url' => $get_coc->pdf_url,
                    'coc_insurance_email_sent' => $get_coc->coc_insurance_email_sent,
                    'created_at' =>$get_coc->created_at,
                    'updated_at' => $get_coc->updated_at,
                    'notes' => $get_coc->notes,
                    'customer_signature' => $get_coc->customer_signature,
                    'company_signature'=> $get_coc->company_representative_signature
                ]
            ], 200);
            

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile()], 500);
        }
    }

    
    public function updateStatusCoc(Request $request, $jobId)
    {
        //Validate Rules
        $this->validate($request, [
            'status' => 'nullable|boolean'
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

                   //current stage save
                $coc->current_stage="yes";
                $coc->save();
            } elseif(isset($request->status) && $request->status == false) {
                $job->status_id = 12;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();

                   //current stage save
                $coc->current_stage="no";
                $coc->save();
            }

            return response()->json([
                'status' => 200,
                'message' => 'COC Status Updated Successfully',
                'data' => $coc,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function CocInsuranceEmail(Request $request, $id)
    {
        $this->validate($request, [
            'coc_insurance_email_sent' => 'nullable',
            'send_to' => 'nullable|array',
            'send_to.*' => 'nullable|email',
            'subject' => 'nullable|string',
            'email_body' => 'nullable|string',
            'attachments' => 'nullable|array',
        ]);
    
        try {
            // Check COC
            $coc = Coc::where('id', $id)->first();
            if (!$coc) {
                return response()->json([
                    'status' => 422,
                    'message' => 'COC Not Found'
                ], 422);
            }
    
            // Prepare attachments
            $attachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('temp'); 
                    $attachments[] = $path; 
                }
            }
    
            // Create an instance of the custom notifiable
            // $notifiable = new \App\CustomNotifiable($request->send_to);
            // // Create the notification
            // $notification = new CocInsuranceNotification($request->subject, $request->email_body, $attachments);
            // // Send the notification
            // Notification::send($notifiable, $notification);
    
            foreach ($request->send_to as $recipient) {
                // Create an instance of the custom notifiable for each recipient
                $notifiable = new \App\CustomNotifiable($recipient);
    
                // Create the notification
                $notification = new CocInsuranceNotification($request->subject, $request->email_body, $attachments);
    
                // Send the notification
                Notification::send($notifiable, $notification);
            }
            // Update COC
            $coc->coc_insurance_email_sent = $request->coc_insurance_email_sent;
            $coc->save();
    
            // Clean up temporary files
            foreach ($attachments as $filePath) {
                Storage::delete($filePath); 
            }
    
            return response()->json([
                'status' => 200,
                'message' => 'Email Sent successfully',
                'data' => []
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile()], 500);
        }
    }

    public function getCocInsuranceEmail($id)
    {
        try{
            // dd("123");
             // Check COC
             $coc = Coc::where('id', $id)->first();
             if (!$coc) {
                 return response()->json([
                     'status' => 422,
                     'message' => 'COC Not Found'
                 ], 422);
             }
             return response()->json([
                'status' => 200,
                'message' => 'Coc Insurance Email Found successfully',
                'data' => $coc
            ], 200);

        }catch(Exception $e){
            return response()->json(['error' => $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile()], 500);
        }
    }

    public function CocInsuranceEmailStatus(Request $request, $id)
    {
        $this->validate($request, [
            'status' => 'nullable',
        ]);
    
        try {
            // Check COC
            $coc = Coc::where('id', $id)->first();
            if (!$coc) {
                return response()->json([
                    'status' => 422,
                    'message' => 'COC Not Found'
                ], 422);
            }
    
            // Update COC
            $coc->coc_insurance_email_sent = $request->status;
            $coc->save();
    
            return response()->json([
                'status' => 200,
                'message' => 'Status Updated successfully',
                'data' => $coc
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile()], 500);
        }
    }

    public function getCocInsuranceEmailStatus(Request $request, $id)
    {
    
        try {
            // Check COC
            $coc = Coc::where('id', $id)->first();
            if (!$coc) {
                return response()->json([
                    'status' => 422,
                    'message' => 'COC Not Found'
                ], 422);
            }
    
            return response()->json([
                'status' => 200,
                'message' => 'Status Found successfully',
                'data' => $coc
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile()], 500);
        }
    }

}
