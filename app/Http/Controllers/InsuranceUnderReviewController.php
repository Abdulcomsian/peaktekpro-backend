<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\CompanyJob;
use Illuminate\Http\Request;
use App\Models\InsuranceUnderReview;
use Illuminate\Support\Facades\Storage;
use App\Models\InsuranceUnderReviewImages;
use App\Models\InsuranceUnderReviewPhotos;
use App\Http\Requests\InsuranceUnderReview\StoreRequest;

class InsuranceUnderReviewController extends Controller
{
    public function addInsuranceUnderReview200($id, Request $request)
    {
        // dd($request->all());
        $request->validate([
            // 'notes'=>'nullable|string',
            // 'photo' => 'nullable|array', 
            // 'photo.*' => 'sometimes|image|mimes:png,jpg,jpeg,svg,gif|max:2048', 
            // 'label' => 'nullable|array',         
            // 'label.*' => 'nullable|string', 
            'pdf_path'=>  'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,txt', 
            'status' => 'nullable|in:approved,overturn'   
        ]);
        $filePath = null;


        try {
            $company = CompanyJob::find($id);
            if (!$company) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Company Not Found',
                ]);
            }

            // $filePath = null;

            $insurance = InsuranceUnderReview::where('company_job_id', $id)->first();
            $existingFilePath = $insurance ? $insurance->pdf_path : null;
            //store attachements here
            if ($request->hasFile('pdf_path')) {
                 // Delete the old document if it exists
                if ($existingFilePath) {
                    $oldFilePath = str_replace('/storage/', 'public/', $existingFilePath);
                    Storage::delete($oldFilePath);
                }
                $document = $request->file('pdf_path');
                $fileName = uniqid() . '_' . $document->getClientOriginalName();
                $filePath = $document->storeAs('public/insurance_under_review', $fileName);
            }else {
                $newFilePath = $existingFilePath ? str_replace('/storage/', 'public/', $existingFilePath) : null;
            }

            //job status update
            if($request->status == "approved"){
                $company->status_id = 8;
                $company->save();
            }elseif($request->status == "overturn"){
                $company->status_id = 6;
                $company->save();
            }


            //save the insurance data
            $insurance = InsuranceUnderReview::updateOrCreate(
                ['company_job_id' => $id],
                [
                    // 'notes' => $request->notes,
                    'pdf_path' => $filePath ? Storage::url($filePath) : null,
                    'file_name' => $request->file_name,
                    'status' => $request->status,

                ]
            );


            // Step 1: Delete existing images
            // $existingPhotos = InsuranceUnderReviewPhotos::where('insurance_under_reviews_id', $insurance->id)->get();
            // foreach ($existingPhotos as $photo) {
            //     // Delete file from storage
            //     $filePath = str_replace('/storage/', 'public/', $photo->photo); // Convert storage path to public disk path
            //     Storage::delete($filePath);
            //     $photo->delete(); // Delete the record from the database
            // }

            // Step 2: Upload new images
            // $savedPhotos = []; // To store successfully saved photos
            // $squarePhotos = $request->photo ?? [];
            // foreach ($squarePhotos as $index => $image) {
            //     $image_fileName = time() . '_' . $image->getClientOriginalName();
            //     $image_filePath = $image->storeAs('InsuranceUnderReviewPhotos', $image_fileName, 'public');

            //     // Save new photo in database
            //     $media = new InsuranceUnderReviewPhotos();
            //     $media->insurance_under_reviews_id = $insurance->id;
            //     $media->label = $request->label[$index] ?? null;
            //     $media->photo = Storage::url($image_filePath);
            //     $media->save();

                // Collect saved photo details
            //     $savedPhotos[] = [
            //         'id' => $media->id,                  
            //         'insurance_under_reviews_id' => $media->insurance_under_reviews_id,
            //         'label' => $media->label,
            //         'photo' => $media->photo,
            //         'created_at' => $media->created_at,
            //         'updated_at' => $media->updated_at,
            //     ];
            // }

            return response()->json([
                'status' => 200,
                'message' => 'Insurance Added Successfully',
                'id' => $insurance->id,
                'company_job_id'=> $insurance->company_job_id,
                // 'notes' => $insurance->notes,
                'pdf_path' =>  $insurance->pdf_path, 
                'job_status' => $insurance->status,                   
                // 'file_name' =>  $insurance->file_name, 
                // 'photo' => $savedPhotos,
                // 'data'=>$insurance
            ]);
        } 
        catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'An issue occurred: ' . $e->getMessage(),
                'data' => [],
            ]);
        }
    }

    public function addInsuranceUnderReview($id, Request $request)
    {
        $request->validate([
            'adjustor_name' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|string',
            'notes' => 'nullable|string',
            'date' => 'nullable|date',
            'pdf_path'=>  'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,txt', 
            'file_name'=> 'nullable|string',
            'status' => 'nullable|in:approved,overturn,denied'   
        ]);


        try {
            $filePath = null;

            $company = CompanyJob::find($id);
            if (!$company) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Company Not Found',
                ]);
            }

            // $filePath = null;

            $date = $request->input('date'); // or $request->date
            $formattedDate = Carbon::createFromFormat('m/d/Y', $date)->format('Y-m-d');

            // dd($formattedDate);
            $insurance = InsuranceUnderReview::where('company_job_id', $id)->first();
            $existingFilePath = $insurance ? $insurance->pdf_path : null;
            //store attachements here
            if ($request->hasFile('pdf_path')) {
                 // Delete the old document if it exists
                if ($existingFilePath) { 
                    $oldFilePath = str_replace('/storage/', 'public/', $existingFilePath);
                    Storage::delete($oldFilePath);
                }
                $document = $request->file('pdf_path');
                $fileName = uniqid() . '_' . $document->getClientOriginalName();
                $filePath = $document->storeAs('public/insurance_under_review', $fileName);
            }else {
                $newFilePath = $existingFilePath ? str_replace('/storage/', 'public/', $existingFilePath) : null;
            }

            //job status update
            if($request->status == "approved"){
                $company->status_id = 8;
                $company->save();
            }elseif($request->status == "overturn"){
                $company->status_id = 6;
                $company->save();
            }elseif($request->status == "denied"){
                $company->status_id = 5;
                $company->save();
            }


            //save the insurance data
            $insurance = InsuranceUnderReview::updateOrCreate(
                ['company_job_id' => $id],
                [
                    'adjustor_name' => $request->adjustor_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'date' => $formattedDate,

                    'notes' => $request->notes,
                    'pdf_path' => $filePath ? Storage::url($filePath) : null,
                    'file_name' => $request->file_name,
                    'status' => $request->status,

                ]
            );

            return response()->json([
                'status' => 200,
                'message' => 'Insurance Added Successfully',
                'data' =>   $insurance               
           
            ]);
        } 
        catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'An issue occurred: ' . $e->getMessage(),
                'data' => [],
            ]);
        }
    }

    public function getInsuranceUnderReview($id, Request $request)
    {
        try {
            // dd($id);
            $company = CompanyJob::find($id);
            if (!$company) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Company Not Found',
                ]);
            }

            $insurance = InsuranceUnderReview::where('company_job_id',$id)->first();
            // dd($insurance);
            if(!$insurance){
                return response()->json([
                    'status' => 404,
                    'message' => 'Insurance Under review not found for this job',
                ]);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Insurance Added Successfully',
                'data' => $insurance,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'An issue occurred: ' . $e->getMessage(),
                'data' => [],
            ]);
        }
    }

    public function addInsuranceUnderReviewDocument($jobId,Request $request)
    {
        $companyJob = CompanyJob::find($jobId);
        if(!$companyJob)
        {
            return response()->json([
                'status_code' =>404,
                'message' => 'Company Job Not Found',
                'data' => []
            ]);
        }

        $request->validate([
            'document' => 'nullable|array', 
            'document.*' => 'nullable|image', 
            'file_name' => 'nullable|array',         
            'file_name.*' => 'nullable|string',      
        ]);

        // $existingPhotos = InsuranceUnderReviewImages::where('company_job_id', $jobId)->get();
        // // dd($existingPhotos);
        // foreach ($existingPhotos as $photo) {
        //     // Delete file from storage
        //     $filePath = str_replace('/storage/', 'public/', $photo->document); // Convert storage path to public disk path
        //     Storage::delete($filePath);
        //     $photo->delete(); // Delete the record from the database
        // }

        $savedPhotos = []; // To store successfully saved photos
        $squarePhotos = $request->document ?? [];
        foreach ($squarePhotos as $index => $document) {
            $document_fileName = time() . '_' . $document->getClientOriginalName();
            $document_filePath = $document->storeAs('InsuranceDetailImages', $document_fileName, 'public');

            // Save new photo in database
            $media = new InsuranceUnderReviewImages();
            $media->company_job_id = $jobId;
            $media->file_name = $request->file_name[$index] ?? null;
            $media->image_path = Storage::url($document_filePath);
            $media->save();

               // Collect saved photo details
               $savedPhotos[] = [
                'id' => $media->id,
                'company_job_id' => $media->company_job_id,
                'file_name' => $media->file_name,
                'pdf_path' => $media->image_path,
                'created_at' => $media->created_at,
                'updated_at' => $media->updated_at,
            ];

        }

        return response()->json([
            'status' => 200,
            'message' => 'Images Updated Successfully',
            'data' => $savedPhotos,
        ]);


    }

    public function getInsuranceUnderReviewDocument($jobId)
    {
        $companyjob = CompanyJob::find($jobId);
        if(!$companyjob)
        {
            return response()->json([
                'status_code' =>404,
                'message' => 'Company Job Not Found',
                'data' => []
            ]);
        }

        $document = InsuranceUnderReviewImages::where('company_job_id',$jobId)->get();
        return response()->json([
            'status' => 200,
            'message' => 'Document Fetched Successfully',
            'data' => $document,
        ]);
    }

    public function statusInsuranceUnderReview(StoreRequest $request,$id) //not used currently if used use some other field for status because it is alreay in use of other fun
    {
        dd($id);
        try{
            $company = CompanyJob::find($id);
            if (!$company) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Company Not Found',
                ]);
            }

            if($request->status == "approved"){
                $company->status_id = 8;
                $company->save();
            }elseif($request->status == "partial-approved"){
                $company->status_id = 4;
                $company->save();
            }elseif($request->status == "denied"){
                $company->status_id = 18;
                $company->save();
            }

            $insurance = InsuranceUnderReview::updateOrCreate(
                ['company_job_id' => $id],
                [
                    'status' => $request->status,
                ]
            );

            return response()->json([
                'status' => 200,
                'message' => 'Insurance Added Successfully',
                'data' => $insurance,
            ]);

        }catch(\Exception $e){
            return response()->json([
                'status' => 500,
                'message' => 'An issue occurred: ' . $e->getMessage(),
                'data' => [],
            ]);
        }
    }


    public function updateInsuranceUnderReview(Request $request,$id)
    {
        $request->validate([
            'status'=>'nullable|in:approved,overturn'
        ]);
        try{
            $company = CompanyJob::find($id);
            if (!$company) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Company Not Found',
                ]);
            }

            if($request->status == "approved"){
                $company->status_id = 8;
                $company->save();
            }elseif($request->status == "overturn"){
                $company->status_id = 6;
                $company->save();
            }

            $insurance = InsuranceUnderReview::updateOrCreate(
                ['company_job_id' => $id],
                [
                    'status' => $request->status,
                ]
            );

            return response()->json([
                'status' => 200,
                'message' => 'Insurance Added Successfully',
                'data' => $insurance,
            ]);

        }catch(\Exception $e){
            return response()->json([
                'status' => 500,
                'message' => 'An issue occurred: ' . $e->getMessage(),
                'data' => [],
            ]);
        }
    }

    public function updateAdjustorResent($id,Request $request)
    {
        try{
            $request->validate([
                'retry_sent'=>'nullable'
            ]);

            $insurance = InsuranceUnderReview::updateOrCreate(
                ['company_job_id' => $id],
                [
                    'retry_sent' => $request->retry_sent,
                ]
            );

            return response()->json([
                'status' => 200,
                'message' => 'Retry sent status Updated Successfully',
                'data' => $insurance,
            ]);

        }catch(\Exception $e){
            return response()->json([
                'status' => 500,
                'message' => 'An issue occurred: ' . $e->getMessage(),
                'data' => [],
            ]);
        }
    }
  
}
