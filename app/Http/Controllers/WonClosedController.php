<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\WonClosed;
use App\Models\CompanyJob;
use Illuminate\Http\Request;

class WonClosedController extends Controller
{
    public function updateWonClosed(Request $request, $jobId)
    {
        //Validate Request
        $request->validate([
            'closed_date' => 'nullable|date_format:m/d/Y',
            'notes' => 'nullable',    
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
            
            $won_closed = WonClosed::updateOrCreate([
                'company_job_id' => $jobId
            ],[
                'company_job_id' => $jobId,
                'closed_date' => $request->closed_date,
                'notes' => $request->notes
            ]);
            
            return response()->json([
                'status' => 200,
                'message' => 'Won Closed Updated Successfully',
                'data' => $won_closed
            ], 200); 
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function updateWonClosedStatus(Request $request,$jobId)
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

        $inspection = WonClosed::updateOrCreate([
            'company_job_id' => $jobId,
        ],[
            'status' => $request->status
        ]);

        if(isset($request->status) && $request->status == "true"){
            $job->status_id = 3;
            $job->date = Carbon::now()->format('Y-m-d');
            $job->save();
        }elseif(isset($request->status) && $request->status == "false"){
            $job->status_id = 2;
            $job->date = Carbon::now()->format('Y-m-d');
            $job->save();
        }

        return response()->json([
            'status' => 200,
            'message' => 'Inspection Status Updated Successfully',
        ]);

    }
    
    public function getWonClosed($jobId)
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
            
            $won_closed = WonClosed::where('company_job_id', $jobId)->first();
            if(is_null($won_closed)) {
                return response()->json([
                'status' => 422,
                'message' => 'Won Closed Not Yet Created',
            ], 422);
            }
            
            return response()->json([
                'status' => 200,
                'message' => 'Won Closed Found Successfully',
                'data' => $won_closed
            ], 200); 
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
