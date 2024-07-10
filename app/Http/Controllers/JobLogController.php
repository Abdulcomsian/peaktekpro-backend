<?php

namespace App\Http\Controllers;

use App\Models\JobLog;
use App\Models\CompanyJob;
use App\Models\JobLogItem;
use Illuminate\Http\Request;

class JobLogController extends Controller
{
    public function storeJobLog(Request $request, $jobId)
    {
        //Validate Request
        $this->validate($request, [
            'rep1_name' => 'required|string|max:255',
            'rep1_email' => 'required|email|max:255|unique:job_logs,rep1_email|unique:job_logs,rep2_email',
            'rep1_phone' => 'required|string|max:20',
            'rep2_name' => 'nullable|string|max:255',
            'rep2_email' => 'nullable|email|max:255|unique:job_logs,rep1_email|unique:job_logs,rep2_email',
            'rep2_phone' => 'nullable|string|max:20',
            'customer_name' => 'required|string|max:255',
            'job_total' => 'required',
            'overhead_total' => 'required',
            'purchase_order_number' => 'required',
            'total_profit' => 'required',
            'commission_rep1' => 'required',
            'commission_rep2' => 'required',
            'team_lead' => 'required',
            'net_to_company' => 'required',
            'items' => 'required|array',
            'items.*.item' => 'required|string',
            'items.*.description' => 'required',
            'items.*.cost' => 'required',
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

            //Store Job Log
            $job_log = JobLog::updateOrCreate([
                'company_job_id' => $jobId,
            ],[
                'company_job_id' => $jobId,
                'rep1_name' => $request->rep1_name,
                'rep1_email' => $request->rep1_email,
                'rep1_phone' => $request->rep1_phone,
                'rep2_name' => $request->rep2_name,
                'rep2_email' => $request->rep2_email,
                'rep2_phone' => $request->rep2_phone,
                'customer_name' => $request->customer_name,
                'job_total' => $request->job_total,
                'overhead_total' => $request->overhead_total,
                'purchase_order_number' => $request->purchase_order_number,
                'total_profit' => $request->total_profit,
                'commission_rep1' => $request->commission_rep1,
                'commission_rep2' => $request->commission_rep2,
                'team_lead' => $request->team_lead,
                'net_to_company' => $request->net_to_company,
            ]);

            //Store Items
            foreach($request->items as $item)
            {
                $job_log_item = new JobLogItem;
                $job_log_item->job_log_id = $job_log->id;
                $job_log_item->item = $item['item'];
                $job_log_item->description = $item['description'];
                $job_log_item->cost = $item['cost'];
                $job_log_item->save();
            }

            return response()->json([
                'status' => 200,
                'message' => 'Job Log Added Successfully',
                'data' => []
            ], 200);


        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getJobLog($jobId)
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

            $get_job_log = JobLog::where('company_job_id', $jobId)->with('items')->first();

            return response()->json([
                'status' => 200,
                'message' => 'Job Log Found Successfully',
                'data' => $get_job_log
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
