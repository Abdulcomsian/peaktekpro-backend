<?php

namespace App\Http\Controllers;

use App\Models\CompanyJob;
use App\Models\ReadyToBuild;
use Illuminate\Http\Request;

class ReadyToBuildController extends Controller
{
    public function storeReadyToBuild(Request $request, $jobId)
    {
        //Validation Request
        $this->validate($request, [
            'recipient' => 'required|string|max:255',
            'time' => 'required|date_format:h:i A', // 12-hour format
            'date' => 'required|date_format:d/m/Y',
            'text' => 'required',
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

            //Update Ready To Build
            $ready_to_build = ReadyToBuild::updateOrCreate([
                'company_job_id' => $jobId,
            ],[
                'company_job_id' => $jobId,
                'recipient' => $request->recipient,
                'date' => $request->date,
                'time' => $request->time,
                'text' => $request->text,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Ready To Build Added Successfully',
                'data' => $ready_to_build
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getReadyToBuild($jobId)
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

            $get_ready_to_build = ReadyToBuild::where('company_job_id', $jobId)->first();
            if(!$get_ready_to_build) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Ready To Build Not Yet Created',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Ready To Build Found Successfully',
                'data' => $get_ready_to_build
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
