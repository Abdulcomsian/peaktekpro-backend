<?php

namespace App\Http\Controllers;

use App\Models\CompanyJob;
use Illuminate\Http\Request;
use App\Models\TermAndCondition;
use Illuminate\Support\Facades\Storage;

class TermAndConditionController extends Controller
{
    public function storeTermAndConditions(Request $request, $jobId)
    {
        //Validate Request
        $this->validate($request, [
            'sign_image' => 'required',
        ]);

        try {

            //Check Job
            $job = CompanyJob::find($jobId);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            $old_image = TermAndCondition::where('company_job_id', $jobId)->first();

            // Get base64 image data
            $base64Image = $request->input('sign_image');
            $data = substr($base64Image, strpos($base64Image, ',') + 1);
            $decodedImage = base64_decode($data);

            // Generate a unique filename
            $filename = 'image_' . time() . '.png';
            // Check if the old image exists and delete it
            if (!is_null($old_image) && $old_image->sign_image) {
                $oldImagePath = public_path($old_image->sign_image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            // Save the new image
            Storage::disk('public')->put('terms_and_conditions/' . $filename, $decodedImage);
            $imageUrl = '/storage/terms_and_conditions/' . $filename;

            //Save Image Path
            $terms_conditions = TermAndCondition::updateOrCreate([
                'company_job_id' => $jobId
            ],[
                'company_job_id' => $jobId,
                'sign_image' => $imageUrl
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Signature Image Added Successfully',
                'data' => $terms_conditions
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getTermAndConditions($jobId)
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

            //Check Terms And Conditions
            $check_terms_conditions = TermAndCondition::where('company_job_id', $jobId)->first();
            if(!$check_terms_conditions) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Terms And Conditions Not Found'
                ], 422);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Terms And Conditions Found Successfully',
                'data' => $check_terms_conditions
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
