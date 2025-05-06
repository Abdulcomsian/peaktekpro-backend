<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecentNoteController extends Controller
{
    public function getAllRecentNotes($jobId)
    {
        try{
            // dd($jobId);
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

            return response()->json([
                'status_code' => 200,
                'message' => 'Recent Notes Fetched Successfully',
                'data' => $latestNotes
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
