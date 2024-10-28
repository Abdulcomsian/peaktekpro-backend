<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanyJob;
use App\Models\FinalPaymentDue;
use Carbon\Carbon;

class FinalPaymentController extends Controller
{
    public function updateFinalPaymentDue(Request $request, $jobId)
    {
        //Validate Request
        $this->validate($request, [
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
            
            //Update Final Payment Due
            $final_payment = FinalPaymentDue::updateOrCreate([
                'company_job_id' => $jobId
            ],[
                'company_job_id' => $jobId,
                'status' => isset($request->status) ? $request->status : false,
                'notes' => $request->notes
            ]);
            
            if(isset($request->status) && $request->status == true) {
                $job->status_id = 14;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();
            }elseif(isset($request->status) && $request->status == false) {
                $job->status_id = 13;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();
            }
            
            return response()->json([
                'status' => 200,
                'message' => 'Final Payment Due Updated Successfully',
                'data' => $final_payment
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function updateFinalPaymentDueStatus(Request $request, $jobId)
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
            
            //Update Final Payment Due
            $final_payment = FinalPaymentDue::updateOrCreate([
                'company_job_id' => $jobId
            ],[
                'company_job_id' => $jobId,
                'status' => isset($request->status) ? $request->status : false,
            ]);
            
            if(isset($request->status) && $request->status == true) {
                $job->status_id = 14;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();
            }
            
            return response()->json([
                'status' => 200,
                'message' => 'Final Payment Due Status Updated Successfully',
                'data' => $final_payment
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    
    public function getFinalPaymentDue($jobId)
    {
        try {
            
            //Check Job
            $job = CompanyJob::whereId($jobId)->with('summary')->first();
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job not found'
                ], 422);
            }
            
            $final_payment = FinalPaymentDue::where('company_job_id', $jobId)->first();
            if(!$final_payment) {
                
                $object = new \StdClass();
                $object->summary = $job->summary;
                
                return response()->json([
                    'status' => 200,
                    'message' => 'Final Payment Due Not Yet Created',
                    'data' => $object
                ], 200);
            }
            
            $final_payment->summary = $job->summary;
            
            return response()->json([
                'status' => 200,
                'message' => 'Final Payment Due Found Successfully',
                'data' => $final_payment
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
