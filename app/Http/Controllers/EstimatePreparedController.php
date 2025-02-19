<?php

namespace App\Http\Controllers;

use App\Models\CompanyJob;
use Illuminate\Http\Request;
use App\Models\EstimatePrepared;
use App\Models\EstimatePreparedMedia;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class EstimatePreparedController extends Controller
{
    public function storeEstimatePrepared(Request $request, $jobId)
    {
        $this->validate($request, [
            'prepared_by' => 'nullable|string',
            'complete_box' => 'nullable|in:true,false',
            'date' => 'nullable|date_format:m/d/Y',
            'images' => 'nullable|array'
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

            $estimate = EstimatePrepared::updateOrCreate([
                'company_job_id' => $jobId
            ],[
                'company_job_id' => $jobId,
                'prepared_by' => $request->prepared_by,
                'complete_box' => $request->complete_box == 'true' ? true : false,
                'date' => $request->date,
            ]);

            //Store QC Inspections Images
            if(isset($request->images) && count($request->images) > 0) {
                //Store New Images
                foreach($request->file('images') as $image) {
                    $image_fileName = time() . '_' . $image->getClientOriginalName();
                    $image_filePath = $image->storeAs('public/estimate_prepared_images', $image_fileName);

                    // Store Path
                    $estimate_media = new EstimatePreparedMedia();
                    $estimate_media->estimate_prepared_id = $estimate->id;
                    $estimate_media->media_url = Storage::url($image_filePath);
                    $estimate_media->save();
                }
            }

            return response()->json([
                'status' => 200,
                'message' => 'Estimate Prepared Added Successfully',
                'data' => $estimate
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function EstimatePreparedStatus(Request $request, $jobId)
    {
        // dd("123");
        $this->validate($request, [
            'status' => 'nullable|string|in:true,false',
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

            $estimate = EstimatePrepared::updateOrCreate([
                'company_job_id' => $jobId
            ],[
                'company_job_id' => $jobId,
                'status' => $request->status,
            ]);

            //Update Status
            if(isset($request->status) && $request->status == 'true') {
                $job->status_id = 4;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();    

                //current stage save
                $estimate->current_stage="yes";
                $estimate->save();

            }elseif(isset($request->status) && $request->status == 'false'){
                $job->status_id = 3;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();  

                //current stage save
                $estimate->current_stage="no";
                $estimate->save();
            }

            return response()->json([
                'status' => 200,
                'message' => 'Estimate Prepared Status Updated Successfully',
                'data' => $estimate
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getEstimatePrepared($jobId)
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

            $estimate = EstimatePrepared::where('company_job_id', $jobId)->with('images')->first();

            return response()->json([
                'status' => 200,
                'message' => 'Estimate Prepared Found Successfully',
                'data' => $estimate
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function changeEstimatePreparedFileName(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'file_name' => 'nullable|string'
        ]);

        try {

            //Check Estimate Prepared Media
            $check_media = EstimatePreparedMedia::find($id);
            if(!$check_media) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Estimate Prepared Media Not Found'
                ], 422);
            }

            //Update File Name
            $check_media->file_name = $request->file_name;
            $check_media->save();

            return response()->json([
                'status' => 200,
                'message' => 'File Name Updated Successfully',
                'data' => $check_media
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function deleteEstimatePreparedMedia(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'image_url' => 'nullable|string'
        ]);

        try {

            //Check Estimate Prepared Media
            $check_media = EstimatePreparedMedia::find($id);
            if(!$check_media) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Estimate Prepared Media Not Found'
                ], 422);
            }

            // Remove old images
            $oldImage = EstimatePreparedMedia::find($id);
            $oldImagePath = str_replace('/storage', 'public', $oldImage->media_url);
            Storage::delete($oldImagePath);
            $oldImage->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Media Deleted Successfully',
                'data' => $check_media
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
