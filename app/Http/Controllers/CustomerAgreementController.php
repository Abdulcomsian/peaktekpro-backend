<?php

namespace App\Http\Controllers;

// use PDF;
use Exception;
use DOMDocument;
use Carbon\Carbon;
use App\Models\Company;
use App\Jobs\SignEmailJob;
use App\Models\CompanyJob;
use App\Mail\SignEmailMail; 
use App\Mail\SaveFilledMail;
use App\Mail\SaveFilledMail2;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AgreementContent;
use App\Models\CompanyJobSummary;
use App\Models\CustomerAgreement;
use App\Models\ProjectDesignTitle;
use Illuminate\Support\Facades\DB;
use App\Events\JobStatusUpdateEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\CustomerAgreementResource;
use App\Http\Resources\SignCustomerAgreementResource; 

class CustomerAgreementController extends Controller
{
    public function customerAgreement(Request $request, $id)
    {
        try {

            //Validate Request
            $this->validate($request, [
                // 'street' => 'nullable',
                // 'city' => 'nullable',
                // 'state' => 'nullable',
                // 'zip_code' => 'nullable',
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
                'status' => 'nullable',
            ]);

            //Check Job
            $job = CompanyJob::find($id);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            /////////////////
            $companyRepresentativeSignUrl = null;
            $customerSignatureUrl = null; 
            if($request->has('company_signature')) {
                $base64Image = $request->input('company_signature');
                $data = substr($base64Image, strpos($base64Image, ',') + 1);
                $decodedImage = base64_decode($data);

                // Generate a unique filename
                $filename = 'image_' . time() . '.png';

                // Check and delete old image
                $oldImage = CustomerAgreement::where('company_job_id', $id)->value('company_signature');
                if ($oldImage) {
                    $oldImagePath = public_path($oldImage);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                // Save the new image
                Storage::disk('public')->put('company_representative_signature/' . $filename, $decodedImage);
                $companyRepresentativeSignUrl = '/storage/company_representative_signature/' . $filename;
            }

            if($request->has('customer_signature')) {
                $base64Image = $request->input('customer_signature');
                $data = substr($base64Image, strpos($base64Image, ',') + 1);
                $decodedImage = base64_decode($data);

                // Generate a unique filename
                $filename = 'image_' . time() . '.png';

                // Check and delete old image
                $oldImage = CustomerAgreement::where('company_job_id', $id)->value('customer_signature');
                if ($oldImage) {
                    $oldImagePath = public_path($oldImage);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                // Save the new image
                Storage::disk('public')->put('customer_signature/' . $filename, $decodedImage);
                $customerSignatureUrl = '/storage/customer_signature/' . $filename;
            }


            //Update Agreement
            $agreement = CustomerAgreement::updateOrCreate([
                'company_job_id' => $id,
            ],[
                'company_job_id' => $id,
                // 'street' => $request->street,
                // 'city' => $request->city,
                // 'state' => $request->state,
                // 'zip_code' => $request->zip_code,
                'insurance' => $request->insurance,
                'claim_number' => $request->claim_number,
                'policy_number' => $request->policy_number,
                // 'company_signature' => $request->company_signature,
                'company_printed_name' => $request->company_printed_name,
                'company_date' => $request->company_date,
                // 'customer_signature' => $request->customer_signature,
                'customer_printed_name' => $request->customer_printed_name,
                'customer_date' => $request->customer_date,
                'agreement_date' => $request->agreement_date,
                'customer_name' => $request->customer_name,
                'status' => $request->status,
                'company_signature'=>$companyRepresentativeSignUrl,
                'customer_signature'=>$customerSignatureUrl

            ]);

            $job_summary = CompanyJobSummary::updateOrCreate([
                'company_job_id' => $id,
            ],[
                'company_job_id' => $id,
                'insurance' => $request->insurance,
                'policy_number' => $request->policy_number,
                'claim_number' => $request->claim_number
            ]);

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

            // Store values in project_design_titles because it will used in design meeting pdf making
            $projectDesignTitle = ProjectDesignTitle::updateOrCreate(
                ['company_job_id' => $id],
                [
            'company_job_id' => $id,
            'first_name' => explode(' ', $request->customer_name)[0],
            'last_name' => explode(' ', $request->customer_name)[1] ?? '',
            'company_name' => $request->company_printed_name,
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

            // return response()->json([
            //     'status' => 200,
            //     'message' => 'Agreement Created Successfully',
            //     'agreement' => $agreement
            // ], 200);
            return response()->json([
                'status' => 200,
                'message' => 'Agreement Created Successfully',
                'agreement' => $agreement
            ], 200, [], JSON_UNESCAPED_SLASHES);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function customerAgreementStatus11(Request $request, $id) //currently not used because of we have elimite the estimate prepared stage and also not checking the job_type
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

            $jobSummary = CompanyJobSummary::where('company_job_id',$id)->first();
            if (!$jobSummary) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Summary Not Found for the given Job ID.'
                ], 422);
            }
            $jobType = $jobSummary->job_type;

            if($jobType == "Insurance")
            {
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
                    
                    //current stage save
                    $agreement->current_stage="yes";
                    $agreement->save();
                } elseif(isset($request->status) && $request->status == false) {
                    $job->status_id = 2;
                    $job->date = Carbon::now()->format('Y-m-d');
                    $job->save(); 
                    
                    //current stage save
                    $agreement->current_stage="no";
                    $agreement->save();
                }
            }else{
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
                    
                    //current stage save
                    $agreement->current_stage="yes";
                    $agreement->save();
                } elseif(isset($request->status) && $request->status == false) {
                    $job->status_id = 2;
                    $job->date = Carbon::now()->format('Y-m-d');
                    $job->save(); 
                    
                    //current stage save
                    $agreement->current_stage="no";
                    $agreement->save();
                }

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

    public function customerAgreementStatus(Request $request, $id)
    {
        //Check Job
        $job = CompanyJob::find($id);
        // dd($job);
        if(!$job) {
            return response()->json([
                'status' => 404,
                'message' => 'Job Not Found'
            ], 404);
        }

        $this->validate($request, [
            'status' => 'nullable|boolean'
        ]);

        try {
            $agreement = CustomerAgreement::updateOrCreate(
                ['company_job_id'=> $id],

                [
                    'company_job_id'=> $id,
                    'status' => $request->status
                ]
            );

            //Update Status
            if(isset($request->status) && $request->status == true) {
                $job->status_id = 5;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();   
                
                //current stage save
                $agreement->current_stage="yes";
                $agreement->save();
            } elseif(isset($request->status) && $request->status == false) {
                $job->status_id = 3;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save(); 
                
                //current stage save
                $agreement->current_stage="no";
                $agreement->save();
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

    //upload filled pdf document
    public function saveFilledPdf($jobId, Request $request) //here send the email also
    {
        try {
            $request->validate([
                'file_path' => 'nullable|file',
                'pdf_status' => 'required' 
            ]);

            $job = CompanyJob::find($jobId);
            if (!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Company Job Not Found',
                ], 422);
            }


            if ($request->hasFile('file_path')) {
                $file = $request->file('file_path');
                $fileExtension = $file->getClientOriginalExtension();
                $fileFinalName = rand(1000, 9999) . '_' . time() . '.' . $fileExtension;

                $file->storeAs('CustomerAgreements', $fileFinalName, 'public'); // Store in 'storage/app/public/CustomerAgreements'

                $saveFilledDocument = CustomerAgreement::updateOrCreate(
                    ['company_job_id' => $jobId],
                    [
                        'sign_pdf_url' => 'CustomerAgreements/'. $fileFinalName,
                        'pdf_status' => $request->pdf_status

                    ]
                );

                $PDFpath =  asset('storage/' .$saveFilledDocument->sign_pdf_url );

                Mail::to($job->email)->send(new SaveFilledMail2($job,$PDFpath));

                return response()->json([
                    'status' => 200,
                    'message' => 'PDF saved successfully.',
                    'data' =>  new CustomerAgreementResource($saveFilledDocument)
                ]);
            } else {
                return response()->json([
                    'status' => 400,
                    'message' => 'No file uploaded.',
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }


    //here generate pdf without signing
   
    public function updateCustomerAgreement(Request $request, $id)
    {
        try {
            //Check Agreement
            $agreement = CustomerAgreement::where('company_job_id',$id)->first();
            if(!$agreement) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Agreement Not Found for this job'
                ], 422);
            }
            $user = Auth::user();
            $companyId = $user->company_id;
            $agreement_content = AgreementContent::where('company_id',$companyId)->first();
            // dd($agreement_content);

            // Parse the content using DOMDocument
            $pdf = Pdf::loadView('pdf.customer',['data' => $agreement,'content'=>$agreement_content]);
            $pdf->setPaper('A4', 'portrait');
        
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

            return response()->json([
                'status' => 200,
                'message' => 'Agreement pdf Generated Successfully',
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
            $companyjob = CompanyJob::find($id);
            if(!$companyjob) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Company Job Not Found'
                ], 422);
            }
            // dd("Sd");

            //Send Email
            $encrypted_url = Crypt::encryptString($request->url);
            // $customer = CompanyJob::find($agreement->company_job_id);
            // dd($customer->email);
            // dispatch(new SignEmailJob($customer,$encrypted_url));
            Mail::to($companyjob->email)->send(new SignEmailMail($companyjob,$encrypted_url));

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
            $job_summary = CompanyJobSummary::select('id','insurance','policy_number','insurance_representative','claim_number')
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
            $agreement = CustomerAgreement::where('company_job_id',$jobId)->first();
        //  dd(public_path($agreement->sign_pdf_url));   
        $file = file_get_contents('storage/' . $agreement->sign_pdf_url);
dd($file);
 $result = $this->pdfSignatureService->extractSignaturesFromUpload($file, [
                'include_base64' => $request->get('include_base64', true),
                'save_images' => $request->get('save_images', true),
            ]);
            dd($result);
            return response()->json([
                'status' => 200,
                'message' => 'Agreement Found Successfully',
                'agreement' => new SignCustomerAgreementResource($agreement)
                // 'agreement' => $agreement
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }


   public function signCustomerByEmail(Request $request, $id) //not used currently
    {
        try {
            $request->validate([
                'file_path' => 'nullable|file',
            ]);

            $agreement = CustomerAgreement::find($id);
            if (!$agreement) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Agreement Not Found'
                ], 422);
            }

            if ($request->hasFile('file_path')) {
                // Delete old file if it exists
                if ($agreement->sign_pdf_url && Storage::disk('public')->exists($agreement->sign_pdf_url)) {
                    Storage::disk('public')->delete($agreement->sign_pdf_url);
                }

                // Store new file
                $file = $request->file('file_path');
                $fileExtension = $file->getClientOriginalExtension();
                $fileFinalName = rand(1000, 9999) . '_' . time() . '.' . $fileExtension;

                $filePath = $file->storeAs('CustomerAgreements', $fileFinalName, 'public');

                // Update agreement
                $agreement->sign_pdf_url = $filePath;
                $agreement->save();

                return response()->json([
                    'status' => 200,
                    'message' => 'Signature Added by Client and PDF updated successfully.',
                    'data' => new CustomerAgreementResource($agreement)
                ]);
            } else {
                return response()->json([
                    'status' => 400,
                    'message' => 'No file uploaded.',
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }



     public function signCustomerByEmail11(Request $request, $id) //when clicnet submit  replace old and add new
    {
        try {
            $request->validate([
                'file_path' => 'nullable|file',
            ]);

                //Check Agreement
            $agreement = CustomerAgreement::find($id);
            if(!$agreement) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Agreement Not Found'
                ], 422);
            }

            if ($request->hasFile('file_path')) {
                $file = $request->file('file_path');
                $fileExtension = $file->getClientOriginalExtension();
                $fileFinalName = rand(1000, 9999) . '_' . time() . '.' . $fileExtension;

                $file->storeAs('CustomerAgreements', $fileFinalName, 'public'); // Store in 'storage/app/public/CustomerAgreements'

                $saveFilledDocument = CustomerAgreement::updateOrCreate(
                    ['company_job_id' => $agreement->company_job_id],
                    ['sign_pdf_url' => 'CustomerAgreements/'. $fileFinalName]
                );

                return response()->json([
                    'status' => 200,
                    'message' => 'PDF saved successfully.',
                    'data' =>  new CustomerAgreementResource($saveFilledDocument)
                ]);
            } else {
                return response()->json([
                    'status' => 400,
                    'message' => 'No file uploaded.',
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }
    
    public function signCustomerByEmailold(Request $request, $id)
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

    // Manage content of customer agreements //here upload pdf also
    public function storeCustomerAgreementContent($companyId, Request $request) //it is company id
    {
        $request->validate([
            'content'=> 'nullable|string',
            'file_path' => 'nullable|file'
        ]);

        try{
            $user = Auth()->user();
            // $companyId = $user->company_id;
            $company = Company::find($companyId); //check this company exist or not
            // dd($company);
            if(!$company)
            {
                return response()->json([
                    'status' => 404,
                    'message'=> 'Company Not Found'
                ]);
            }

            $fileFinalName = null;

            if ($request->hasFile('file_path')) {
                $file = $request->file('file_path');
                $fileExtension = $file->getClientOriginalExtension();
                $fileFinalName = rand(1000, 9999) . '_' . time() . '.' . $fileExtension;

                $file->storeAs('CompanyCustomerAgreements', $fileFinalName, 'public'); // Store in 'storage/app/public/CustomerAgreements'

        
            } 

            $agreement = AgreementContent::updateOrCreate([
                'company_id' => $companyId,
            ],[
                'company_id' => $companyId,
                'content' => $request->content,
                'file_path' => $request->hasFile('file_path') ?  'CompanyCustomerAgreements/'. $fileFinalName : null
            ]);

            return response()->json([
                'status' => 200,
                'message'=> 'Agreement Content Added Successfully',
                'data' => [
                    'id' => $agreement->id,
                    'company_id' => $agreement->company_id,
                    'file_path' =>  $request->hasFile('file_path') ? asset('storage/' .$agreement->file_path ) : null,
                    'created_at' => $agreement->created_at,
                    'updated_at' => $agreement->updated_at

                ]
            ]);

        }catch(\Exception $e){
            return response()->json([
                'status' => 200,
                'message'=> $e->getMessage(),
            ]);

        }
        
    }
   

    public function getCustomerAgreementContent($companyId)
    {
        $company = Company::find($companyId);
        if(!$company)
        {
            return response()->json([
                'status' => 404,
                'message'=> 'Company Not Found'
            ]);
        }

        $agreement= AgreementContent::where('company_id',$companyId)->first();
        if($agreement)
        {
            return response()->json([
                'status' => 200,
                'message'=> 'Agreement Content Found Successfully',
                    'data' => [
                    'id' => $agreement->id,
                    'company_id' => $agreement->company_id,
                    'file_path' => $agreement->file_path ? asset('storage/' .$agreement->file_path ) : null,
                  
                    'created_at' => $agreement->created_at,
                    'updated_at' => $agreement->updated_at

                ]
            ]);
        }

    }

    public function getCustomerAgreementContentold($jobId)
    {
        $job = CompanyJob::find($jobId);
        if(!$job)
        {
            return response()->json([
                'status' => 404,
                'message'=> 'Job Not Found'
            ]);
        }

        $agreement= CustomerAgreement::where('company_job_id',$jobId)->first();
        if($agreement)
        {
            return response()->json([
                'status' => 200,
                'message'=> 'Agreement Content Found Successfully',
                'data' => $agreement
            ]);
        }

    }

}
