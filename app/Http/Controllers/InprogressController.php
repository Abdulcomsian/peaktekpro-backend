<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanyJob;
use App\Models\Inprogress;
use Carbon\Carbon;

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
                $job->status_id = 11;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();
            }elseif(isset($request->status) && $request->status == false) {
                $job->status_id = 10;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();
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

}
