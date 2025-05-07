<?php

namespace App\Http\Controllers;

use App\Models\CompanyJobSummary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecentNoteController extends Controller
{
    public function getAllRecentNotes($jobId)
    {
        try{
            $customer_name = CompanyJobSummary::select('customer_name')->where('company_job_id',$jobId)->first();
            // dd($customer_name);
            $latestNotes = 
                DB::table('adjustor_meetings')->select('notes', 'created_at', 'company_job_id')
                
            ->unionAll(
                DB::table('insurance_under_reviews')->select('notes', 'created_at', 'company_job_id')
            )
            ->unionAll(
                DB::table('ready_to_builds')->select('notes', 'created_at', 'company_job_id')
            )
            ->unionAll(
                DB::table('final_payment_dues')->select('notes', 'created_at', 'company_job_id')
            )
            ->unionAll(
                DB::table('claim_details')->select('notes', 'created_at', 'company_job_id')
            )
            ->unionAll(
                DB::table('won_closeds')->select('notes', 'created_at', 'company_job_id')
            )
            ->where('company_job_id', $jobId) 
            ->orderBy('created_at', 'desc') 
            ->limit(10) 
            ->get();
            // $latestNotes1=[
            //     'notes' =>$latestNotes->notes,
            //     'created_at' =>$latestNotes->created_at,
            //     'company_job_id' =>$latestNotes->company_job_id,
            // ];

            return response()->json([
                'status_code' => 200,
                'message' => 'Recent Notes Fetched Successfully',
                'customer_name' => $customer_name->customer_name,

                'data' => $latestNotes,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 500,
                'message' => 'Error occurred while adding Payment History',
                'error' => $e->getMessage()
            ]);
        }
    }
}
