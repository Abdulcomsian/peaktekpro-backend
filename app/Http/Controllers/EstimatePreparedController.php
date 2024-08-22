<?php

namespace App\Http\Controllers;

use App\Models\CompanyJob;
use Illuminate\Http\Request;
use App\Models\EstimatePrepared;
use App\Models\EstimatePreparedMedia;
use Illuminate\Support\Facades\Storage;

class EstimatePreparedController extends Controller
{
    public function storeEstimatePrepared(Request $request, $jobId)
    {
        $this->validate($request, [
            'prepared_by' => 'required|string',
            'complete_box' => 'required|in:0,1',
            'date' => 'nullable|date_format:d/m/Y',
            'images' => 'required|array'
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
                'complete_box' => $request->complete_box,
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
}
