<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanyJob;
use App\Models\ReadyToClose;
use Carbon\Carbon;

class ReadyToCloseController extends Controller
{
    public function updateReadyToClose(Request $request, $jobId)
    {
        //Validate Request
        $request->validate([
            'deal_value' => 'nullable', 
            'labor_costs' => 'nullable', 
            'material_costs' => 'nullable', 
            'costs_of_goods' => 'nullable', 
            'market' => 'nullable', 
            'sales_rep1_commission_percentage' => 'nullable', 
            'sales_rep2_commission_percentage' => 'nullable',
            'status' => 'nullable',
            'sales_rep1' => 'nullable|integer|exists:users,id',
            'sales_rep2' => 'nullable|integer|exists:users,id',
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
            
            $ready_to_close = ReadyToClose::updateOrCreate([
                'company_job_id' => $jobId
            ],[
                'company_job_id' => $jobId,
                'sales_rep1_commission_percentage' => $request->sales_rep1_commission_percentage,
                'sales_rep2_commission_percentage' => $request->sales_rep2_commission_percentage,
                'deal_value' => $request->deal_value,
                'labor_costs' => $request->labor_costs,
                'material_costs' => $request->material_costs,
                'costs_of_goods' => $request->costs_of_goods,
                'market' => $request->market,
                'status' => (isset($request->status)) ? $request->status : false,
            ]);
            
            $user_ids = [];

            if (isset($request->sales_rep1)) {
                $user_ids[] = $request->sales_rep1;
            }
            
            if (isset($request->sales_rep2)) {
                $user_ids[] = $request->sales_rep2;
            }
            
            //Assign Job
            $job->users()->sync($user_ids);
            
            //Update Job Status
            if(isset($request->status) && $request->status == true)
            {
                $job->status_id = 15;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();
            }
            
            return response()->json([
                'status' => 200,
                'message' => 'Ready To Close Updated Successfully',
                'data' => $ready_to_close
            ], 200); 
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function updateReadyToCloseStatus(Request $request, $jobId)
    {
        //Validate Request
        $request->validate([
            'status' => 'nullable',
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
            
            $ready_to_close = ReadyToClose::updateOrCreate([
                'company_job_id' => $jobId
            ],[
                'status' => (isset($request->status)) ? $request->status : false,
            ]);
            
            //Update Job Status
            if(isset($request->status) && $request->status == true)
            {
                $job->status_id = 15;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();
            }
            
            return response()->json([
                'status' => 200,
                'message' => 'Ready To Close Status Updated Successfully',
                'data' => $ready_to_close
            ], 200); 
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    
    public function getReadyToClose($jobId)
    {
        try {
            
            //Check Job
            $job = CompanyJob::whereId($jobId)->with('materialOrder')->first();
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }
            
            $userIds = $job->users()->pluck('user_id')->toArray();
            
            $ready_to_close = ReadyToClose::where('company_job_id', $jobId)->first();
            if(is_null($ready_to_close)) {
                
                $object = new \StdClass();
                $object->sales_rep1 = isset($userIds[0]) ? $userIds[0] : null;
                $object->sales_rep2 = isset($userIds[1]) ? $userIds[1] : null;
                $object->square_count = !is_null($job->materialOrder) ? $job->materialOrder->square_count : null;
                
                return response()->json([
                    'status' => 200,
                    'message' => 'Ready To Close Not Yet Created',
                    'data' => $object
                ], 200);
            }
            
            $ready_to_close->sales_rep1 = isset($userIds[0]) ? $userIds[0] : null;
            $ready_to_close->sales_rep2 = isset($userIds[1]) ? $userIds[1] : null;
            $ready_to_close->square_count = !is_null($job->materialOrder) ? $job->materialOrder->square_count : null;
            
            return response()->json([
                'status' => 200,
                'message' => 'Ready To Close Found Successfully',
                'data' => $ready_to_close
            ], 200); 
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
