<?php

namespace App\Http\Controllers;

use App\Models\CompanyJob;
use Illuminate\Http\Request;
use App\Models\InsuranceUnderReview;
use Illuminate\Support\Facades\Storage;
use App\Models\InsuranceUnderReviewPhotos;
use App\Http\Requests\InsuranceUnderReview\StoreRequest;
class InsuranceUnderReviewController extends Controller
{
    public function addInsuranceUnderReview($id, Request $request)
    {
        // dd($request->all());
        $request->validate([
            'notes'=>'nullable|string',
            'photo' => 'nullable|array', 
            'photo.*' => 'nullable|image|mimes:png,jpg,jpeg,svg,gif|max:2048', 
            'label' => 'nullable|array',         
            'label.*' => 'nullable|string',      
        ]);

        try {
            $company = CompanyJob::find($id);
            if (!$company) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Company Not Found',
                ]);
            }

            $insurance = InsuranceUnderReview::updateOrCreate(
                ['company_job_id' => $id],
                [
                    'notes' => $request->notes,
                ]
            );

            // return response()->json($insurance);

            // Step 1: Delete existing images
            $existingPhotos = InsuranceUnderReviewPhotos::where('insurance_under_reviews_id', $insurance->id)->get();
            foreach ($existingPhotos as $photo) {
                // Delete file from storage
                $filePath = str_replace('/storage/', 'public/', $photo->photo); // Convert storage path to public disk path
                Storage::delete($filePath);
                $photo->delete(); // Delete the record from the database
            }

            // Step 2: Upload new images
            $savedPhotos = []; // To store successfully saved photos
            $squarePhotos = $request->photo ?? [];
            foreach ($squarePhotos as $index => $image) {
                $image_fileName = time() . '_' . $image->getClientOriginalName();
                $image_filePath = $image->storeAs('InsuranceUnderReviewPhotos', $image_fileName, 'public');

                // Save new photo in database
                $media = new InsuranceUnderReviewPhotos();
                $media->insurance_under_reviews_id = $insurance->id;
                $media->label = $request->label[$index] ?? null;
                $media->photo = Storage::url($image_filePath);
                $media->save();

                // Collect saved photo details
                $savedPhotos[] = [
                    'id' => $media->id,
                    'notes' =>  $insurance->notes,                    
                    'insurance_under_reviews_id' => $media->insurance_under_reviews_id,
                    'label' => $media->label,
                    'photo' => $media->photo,
                    'created_at' => $media->created_at,
                    'updated_at' => $media->updated_at,
                ];
            }

            return response()->json([
                'status' => 200,
                'message' => 'Insurance Added Successfully',
                'id' => $insurance->id,
                'company_job_id'=> $insurance->company_job_id,
                'notes' => $insurance->notes,
                'photo' => $savedPhotos,
            ]);
        } catch (\Exception $e) {
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

            $insurance = InsuranceUnderReview::with('getPhotos')->where('company_job_id',$id)->first();
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

    public function statusInsuranceUnderReview(StoreRequest $request,$id)
    {
        // dd($id);
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
}
