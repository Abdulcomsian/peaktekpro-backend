<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\CompanyJob;
use App\Models\Inprogress;
use Illuminate\Http\Request;
use App\Models\BuildPacketChecklist;
use Illuminate\Support\Facades\Storage;

class InprogressController extends Controller
{
    public function updateInprogress(Request $request, $jobId)
    {
        //Validate Request
        $this->validate($request, [
            'build_start_date' => 'nullable|date_format:m/d/Y',
            'build_end_date' => 'nullable|date_format:m/d/Y',
            'notes' => 'nullable',
            'status' => 'nullable'
        ]);
        
        try {
            
            //Check Job
            $job = CompanyJob::find($jobId);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job not found'
                ], 422);
            }
            
            //Update Inprogress
            $in_progress = Inprogress::updateOrCreate([
                'company_job_id' => $jobId,
            ],[
                'company_job_id' => $jobId,
                'build_start_date' => $request->build_start_date,
                'build_end_date' => $request->build_end_date,
                'notes' => $request->notes,
                'status' => $request->status,
            ]);
            
            if(isset($request->status) && $request->status == true) {
                $job->status_id = 11;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();
            }
            
            return response()->json([
                'status' => 200,
                'message' => 'Inprogress Build Updated Successfully',
                'data' => $in_progress
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    
    public function updateInprogressStatus(Request $request, $jobId)
    {
        //Validate Request
        $this->validate($request, [
            'status' => 'nullable'
        ]);
        
        try {
            //Check Job
            $job = CompanyJob::find($jobId);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job not found'
                ], 422);
            }
            
            //Update Inprogress
            $in_progress = Inprogress::updateOrCreate([
                'company_job_id' => $jobId,
            ],[
                'status' => $request->status,
            ]);
            
            if(isset($request->status) && $request->status == true) {
                $job->status_id = 12;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();

                   //current stage save
                $in_progress->current_stage="yes";
                $in_progress->save();
            }elseif(isset($request->status) && $request->status == false) {
                $job->status_id = 11;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();

                 //current stage save
                 $in_progress->current_stage="no";
                 $in_progress->save();
            }
            
            return response()->json([
                'status' => 200,
                'message' => 'Inprogress Build status Updated Successfully',
                'data' => $in_progress
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }


    public function getInprogress($jobId)
    {
        try {
            
            //Check Job
            $job = CompanyJob::find($jobId);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job not found'
                ], 422);
            }
            
            $in_progress = Inprogress::where('company_job_id', $jobId)->first();
            if(!$in_progress) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Inprogress Build Not Yet Created'
                ], 422);
            }
            
            return response()->json([
                'status' => 200,
                'message' => 'Inprogress Build Found Successfully',
                'data' => $in_progress
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function buildPacketSidebar($jobId, Request $request)
    {
        $company = CompanyJob::find($jobId);
        if(!$company)
        {
            return response()->json([
                'status' => 404,
                'message' => 'company not found',
                'data' => []
            ]);
        }

        $request->validate([
            'project_overview'=>'nullable|string|in:true,false',
            'scope_of_work'=>'nullable|string|in:true,false',
            'customer_preparation'=>'nullable|string|in:true,false',
            'photo_documentation'=>'nullable|string|in:true,false',
            'product_selection'=>'nullable|string|in:true,false',
            'authorization'=>'nullable|string|in:true,false',
            'terms_condition'=>'nullable|string|in:true,false',
        ]);

        // if($validate->fails()){

        // }

        try{
                $company_job_checklist = BuildPacketChecklist::updateOrCreate([
                    'company_job_id' => $jobId
                ],
                [
                    'company_job_id' => $jobId,
                    'project_overview' => $request->project_overview,
                    'scope_of_work' => $request->scope_of_work,
                    'customer_preparation' => $request->customer_preparation,
                    'photo_documentation' => $request->photo_documentation,
                    'product_selection' => $request->product_selection,
                    'authorization' => $request->authorization,
                    'terms_condition' => $request->terms_condition,
                ]
            );

                return response()->json([
                    'status' => 200,
                    'message' => 'Build packet sidebar updated successfully',
                    'data' => $company_job_checklist
                ]);
            }
        
        catch(\Exception $e){
             return response()->json([
                    'status' => 500,
                    'message' => 'Internal Server Error',
                    'error' => $e->getMessage()
                ]);
        }
        

    }

    public function getBuildPacketSidebar($jobId)
    {
        $company = CompanyJob::find($jobId);
        if(!$company)
        {
            return response()->json([
                'status' => 404,
                'message' => 'Company not Found',
                'data' => []
            ]);
        }

        //now check in buildpacket table
        $build_packet = BuildPacketChecklist::where('company_job_id',$jobId)->first();
        if($build_packet)
        {
            return response()->json([
                'status' => 404,
                'message' => 'Data Fetched Successfully',
                'data' => $build_packet
            ]);
        }
        return response()->json([
            'status' => 404,
            'message' => 'Build Packet Not Found',
            'data' => []
        ]);

    }

    public function markBuildPacketComplete($jobId, Request $request)
    {
        $request->validate([
            'status' => 'nullable|string|in:true,false'
        ]);

        $company = CompanyJob::find($jobId);
        if(!$company)
        {
            return response()->json([
                'status' => 404,
                'message' => 'Company not Found',
                'data' => []
            ]);
        }

        //now check in buildpacket table
        $build_packet = BuildPacketChecklist::where('company_job_id',$jobId)->first();
        if($build_packet && $request->status == "true")
        {
            $build_packet->is_complete = "true";
            $build_packet->status = "Build Complete";
            $build_packet->save();

            return response()->json([
                'status' => 404,
                'message' => 'Build Packet Updated',
                'data' => $build_packet
            ]);

        } else if($build_packet && $request->status == "false")
        {
            $build_packet->is_complete = "false";
            $build_packet->status = "Approved";
            $build_packet->save();

            return response()->json([
                'status' => 404,
                'message' => 'Build Packet Updated',
                'data' => $build_packet
            ]);

        }
        return response()->json([
            'status' => 404,
            'message' => 'Build Packet Not Found',
            'data' => []
        ]);
    }

    public function getProjectStatus($jobId)
    {
        $company = CompanyJob::find($jobId);
        if(!$company)
        {
            return response()->json([
                'status' => 404,
                'message' => 'Company not Found',
                'data' => []
            ]);
        }

        $build_packet = BuildPacketChecklist::select('id','company_job_id','is_complete','status','created_at','updated_at')->where('company_job_id',$jobId)->first();
        if($build_packet)
        {
            return response()->json([
                'status' => 404,
                'message' => 'Build Packet Fetched',
                'data' => $build_packet
            ]);
        }
        return response()->json([
            'status' => 404,
            'message' => 'Build Packet Not Found',
            'data' => $build_packet
        ]);

    }

    public function signBuildPacket($jobId, Request $request)
    {
        $this->validate($request, [
            'sign_image' => 'nullable',
        ]);

        try{
            $build_packet = BuildPacketChecklist::where('company_job_id',$jobId)->first();
            if(!$build_packet) {
                return response()->json([
                    'status' => 422,
                    'message' => 'build_packet Not Found',
                    'data' => []
                ], 422);
            }
             // Get base64 image data
             $base64Image = $request->input('sign_image');
             $data = substr($base64Image, strpos($base64Image, ',') + 1);
             $decodedImage = base64_decode($data);

             // Generate a unique filename
            $filename = 'image_' . time() . '.png';
            // Check if the old image exists and delete it
            if ($build_packet->sign_image_url) {
                $oldImagePath = public_path($build_packet->sign_image_url);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
             
            //save new image
            Storage::disk('public')->put('build_packet_sign/' . $filename, $decodedImage);
            $imageUrl = '/storage/build_packet_sign/' . $filename;

            //Save Image Path and Update the Status also
            $build_packet->sign_image_url = $imageUrl;
            $build_packet->status = "Ready to Build";
            $build_packet->save();

            // //Generate PDF
            // $pdf = PDF::loadView('pdf.build_packet', ['data' => $build_packet]);
            // $pdf_fileName = time() . '.pdf';
            // $pdf_filePath = 'build_packet_pdf/' . $pdf_fileName;
            // // Check if the old PDF exists and delete it
            // if ($build_packet->pdf_url) {
            //     $oldPdfPath = public_path($build_packet->sign_pdf_url);
            //     if (file_exists($oldPdfPath)) {
            //         unlink($oldPdfPath);
            //     }
            // }
            //  // Save the new PDF
            //  Storage::put('public/' . $pdf_filePath, $pdf->output());
            //  //Save PDF Path
            //  $build_packet->sign_pdf_url = '/storage/' . $pdf_filePath;
            //  $build_packet->save();

            //Update Job Status
            $job = CompanyJob::find($build_packet->company_job_id);
            $job->status_id = 9;
            $job->date = Carbon::now()->format('Y-m-d');
            $job->save();

            return response()->json([
                'status' => 200,
                'message' => 'Signature Added Successfully',
                'data' => $build_packet
            ], 200);


        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);

        }
    }

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

}
