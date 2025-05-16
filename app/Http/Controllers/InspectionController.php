<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Coc;
use App\Models\WonClosed;
use App\Models\CompanyJob;
use App\Models\Inprogress;
use App\Models\Inspection;
use App\Models\BuildDetail;
use App\Models\ReadyToBuild;
use Illuminate\Http\Request;
use App\Models\BuildComplete;
use App\Models\AdjustorMeeting;
use App\Models\FinalPaymentDue;
use App\Models\CustomerAgreement;
use App\Models\InsuranceUnderReview;
use Illuminate\Support\Facades\Storage;

class InspectionController extends Controller
{
    public function addInspection($jobId, Request $request)
    {
        $request->validate([
            'file_path' => 'nullable|array', 
            'file_path.*' => 'nullable|file', 
            'labels' => 'nullable|array',         
            'labels.*' => 'nullable|string',      
        ]);

        try {
            $adjustor = CompanyJob::find($jobId);
            if (!$adjustor) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Company Job Not Found',
                ]);
            }


            $savedPhotos = [];
            $squarePhotos = $request->file_path ?? [];
    
            foreach ($squarePhotos as $index => $document) {
                $document_fileName = time() . '_' . $document->getClientOriginalName();
                $document_filePath = $document->storeAs('InspectionPhotos', $document_fileName, 'public');
    
                $media = new Inspection();
                $media->company_job_id = $jobId;
                $media->labels =  $request->labels[$index] ?? null;
                $media->file_path = Storage::url($document_filePath);

                $media->save();
    
                $savedPhotos[] = [
                    'id' => $media->id,
                    'company_job_id' => $media->company_job_id,
                    'labels' => $media->labels,
                    'file_path' => $media->file_path,
                    'created_at' => $media->created_at,
                    'updated_at' => $media->updated_at,
                ];
            }

            return response()->json([
                'status' => 200,
                'message' => 'Inspection Photos Added Successfully',
                'data' => $savedPhotos,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'An issue occurred: ' . $e->getMessage(),
                'data' => [],
            ]);
        }
    }

    public function getInspection($jobId)
    {
        $job = CompanyJob::find($jobId);
        if (!$job) {
            return response()->json([
                'status' => 404,
                'message' => 'Company Job Not Found',
            ]);
        }

        $data = Inspection::where('company_job_id', $jobId)->get();
        return response()->json([
            'status' => 200,
            'message' => 'Inspection Photos Fetched Successfully',
            'data' => $data,
        ]);
    }

    public function deleteInspection($id)
    {
        $media = Inspection::find($id);
        if (!$media) {
            return response()->json([
                'status' => 404,
                'message' => 'Photo not found',
            ]);
        }
        $imagePath = $media->file_path; 
        $relativePath = str_replace('/storage/', '', $imagePath);

        // Delete the image from storage
        if (Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }

        $media->delete();
        return response()->json([
            'status' => 200,
            'message' => 'Inspection Media Deleted Successfully',
        ]);
    }

    public function updateInspectionStatus(Request $request,$jobId)
    {
        $this->validate($request, [
            'status' => 'nullable|string'
        ]);

        $job = CompanyJob::find($jobId);
        if (!$job) {
            return response()->json([
                'status' => 404,
                'message' => 'Company Job Not Found',
            ]);
        }

        $inspection = Inspection::updateOrCreate([
            'company_job_id' => $jobId,
        ],[
            'status' => $request->status
        ]);

        if(isset($request->status) && $request->status == "true"){
            $job->status_id = 3;
            $job->date = Carbon::now()->format('Y-m-d');
            $job->save();
        }elseif(isset($request->status) && $request->status == "false"){
            $job->status_id = 1;
            $job->date = Carbon::now()->format('Y-m-d');
            $job->save();
        }

        return response()->json([
            'status' => 200,
            'message' => 'Inspection Status Updated Successfully',
        ]);

    }

    public function getAllStatus($jobId)
    {
        //NewLead
        $NewLead = "true";

        //inspection
        // $inspection = Inspection::where('company_job_id', $jobId)
        //     ->whereNotNull('file_path')
        //     ->where('file_path', '!=', '')
        //     ->exists();
        //     // dd($inspection);
        // if ($inspection) {
        //     $inspection = "true";
        // } else{
        //     $inspection = "false";
        // }
        $Inspection = Inspection::select('status')->where('company_job_id',$jobId)->first();
        if ($Inspection && $Inspection->status == "true") {
            $inspection = "true";

        }else{
            $inspection = "false";

        }

            //Signed Deal
        $SignedDeal = CustomerAgreement::select('status')->where('company_job_id',$jobId)->first();
        if ($SignedDeal && $SignedDeal->status == 1) {
            $SignedDeal = "true";

        }else{
            $SignedDeal = "false";

        }

        //Adjustor
        $adjustor = AdjustorMeeting::select('sent')->where('company_job_id',$jobId)->first();
        if ($adjustor && $adjustor->sent == "true") {
            $adjustor = "true";

        }else{
            $adjustor = "false";

        }

        //Verdict
        $insurance = InsuranceUnderReview::select('status')->where('company_job_id',$jobId)->first();
        if (!$insurance || $insurance->status !== "approved") {
            $insurance = "false";
        }else{
            $insurance = "true";

        }

        //Ready To Build
        $readyBuild = ReadyToBuild::select('status')->where('company_job_id',$jobId)->first();
        if ($readyBuild && $readyBuild->status == "true") {
            $readyBuild = "true";

        }else{
            $readyBuild = "false";

        }

        //Build Schedlue
        $buildSchedule = BuildDetail::select('confirmed')->where('company_job_id',$jobId)->first();
        if ($buildSchedule && $buildSchedule->confirmed == true) {
            $buildSchedule = "true";

        }else{
            $buildSchedule = "false";


        }

        //inprogress
        $Inprogress = Inprogress::select('status')->where('company_job_id',$jobId)->first();
        if ($Inprogress && $Inprogress->status == true) {
            $Inprogress = "true";
        }else{
            $Inprogress = "false";
        }

        //build Complete
        $coc = Coc::select('status')->where('company_job_id',$jobId)->first();
        if ($coc && $coc->status == "true") {
            $coc = "true";

        }else{
            $coc = "false";
        }

        //Final Payment Due
        $finalPayment = FinalPaymentDue::where('company_job_id',$jobId)->first();
        if ($finalPayment && $finalPayment->status == "true") {
            $finalPayment = "true";

        }else{
            $finalPayment = "false";
        }

        //Won and Closed
        $wonClosed = WonClosed::where('company_job_id',$jobId)->first();
        if (!$wonClosed || $wonClosed->closed_date == NULL) {
            $wonClosed = "false";
        }else{
            $wonClosed = "true";

        }

        return response()->json([
            'status_code'=> 200,
            'msg' => 'Status of tabs',
            'data'=> [
                'NewLead' =>$NewLead,
                'inspection'=> $inspection,
                'SignedDeal' => $SignedDeal,
                'Adjustor' => $adjustor,
                'InsuranceUnderReview' => $insurance,
                'readyBuild' => $readyBuild,
                'buildSchedule' => $buildSchedule,
                'Inprogress' => $Inprogress,
                'Coc' => $coc,
                'finalPayment' => $finalPayment,
                'WonClosed' => $wonClosed

            ],
        ]);



    }

    

}
