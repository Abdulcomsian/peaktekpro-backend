<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use App\Models\User;
use App\Models\CompanyJob;
use Illuminate\Http\Request;
use App\Resources\ReportResource;

class ReportController extends Controller
{
    public function userReports(Request $request)
    {
        // Validate request parameters with specific date format (Y-m-d)
        $request->validate([
            'startDate' => 'nullable|date_format:Y-m-d',  // Must match Y-m-d format if provided
            'endDate' => 'nullable|date_format:Y-m-d|after_or_equal:startDate',  // Must match Y-m-d format and be after or equal to startDate
        ]);

        $user = Auth::user();
        $company_id = $user->company_id;

        // Set date range - default to the current month if no date range is provided
        $startDate = $request->startDate ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->endDate ?? now()->endOfMonth()->format('Y-m-d');

        // Retrieve users with the specified roles within the company
        $users = User::where('company_id', $company_id)
                    ->whereIn('role_id', [1,2, 8, 9])
                    ->get();

        // Count all jobs created within the date range for the company
        $totalCreatedJobs = DB::table('company_jobs')
            ->where('created_by', $company_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // For each user, calculate job totals, job count, average job value, conversion rate, and new_leads
        $reports = $users->map(function ($user) use ($company_id, $startDate, $endDate, $totalCreatedJobs) {
            // Main job data query for each user
            $jobs = DB::table('company_jobs')
                ->join('company_job_summaries', 'company_job_summaries.company_job_id', '=', 'company_jobs.id')
                ->where('company_jobs.created_by', $company_id)  // Filter by the logged-in user's company
                ->where('company_jobs.user_id', $user->id)  // Filter jobs specifically created by the user
                ->whereBetween('company_job_summaries.created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('count(company_jobs.id) as job_won'),  // Count jobs won by this user
                    DB::raw('sum(company_job_summaries.job_total) as value_jobs_won'),  // Sum of job totals for this user
                    'company_jobs.status_id'  // Get the job status
                )
                ->groupBy('company_jobs.status_id')  // Group by job status if needed
                ->get();

            // Count of jobs with status_id = 1 (representing new leads) for the current user
            $new_leads = DB::table('company_jobs')
                ->where('created_by', $company_id)
                ->where('user_id', $user->id)
                ->where('status_id', 1)  // New leads have status_id = 1
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            // Set default null values for the job data structure
            $defaultJobData = [
                'job_won' => null,
                'value_jobs_won' => null,
                'status_id' => null,
                'average_job_value' => null,
                'conversion_rate' => null,
                'new_leads' => null,
            ];

            // Calculate average job value, conversion rate, and add new_leads to each entry in job data
            $job_data = $jobs->map(function ($job) use ($totalCreatedJobs, $new_leads, $defaultJobData) {
                // If no jobs data, return default values with null
                if ($job->job_won === null) {
                    return $defaultJobData;
                }

                // Otherwise, calculate values
                $job->average_job_value = $job->job_won > 0 ? $job->value_jobs_won / $job->job_won : null;
                $job->conversion_rate = $totalCreatedJobs > 0 ? round(($job->job_won / $totalCreatedJobs) * 100, 2) : null;
                $job->new_leads = $new_leads ?? null;  // Set new_leads to null if no data exists

                return $job;
            });

            // If no job data, return default values (with nulls)
            if ($job_data->isEmpty()) {
                $job_data = [$defaultJobData];  // Ensure we return at least one entry with null values
            }

            return [
                'user' => $user,
                'job_total' => $job_data,  // Job total now returns default values with nulls
            ];
        });

        return response()->json($reports);
    }


    public function getPipelineDate(Request $request)
    {
        // Validate request parameters (optional)
        $request->validate([
            'startDate' => 'nullable|date_format:Y-m-d',  // Date format validation
            'endDate' => 'nullable|date_format:Y-m-d|after_or_equal:startDate',  // Ensure endDate is after or equal to startDate
        ]);

        $statusIds = [1, 2, 3, 4, 8, 9, 10, 11, 12, 13, 14, 15];

        $statusNames = [
            1 => 'New Leads',
            2 => 'Signed Deals',
            3 => 'Estimate Prepared',
            4 => 'Adjustor',
            8 => 'Ready To Build',
            9 => 'Build Scheduled',
            10 => 'In Progress',
            11 => 'Build Complete',
            12 => 'COC Required',
            13 => 'Final Payment Due',
            14 => 'Ready to Close',
            15 => 'Won and Closed'
        ];

        // Get the company_id from the authenticated user
        $user = auth()->user();
        $company_id = $user->company_id;

        // $startDate = $request->startDate ?? now()->startOfMonth()->format('Y-m-d');
        // $endDate = $request->endDate ?? now()->endOfMonth()->format('Y-m-d');

        // Get jobs matching the status_id values in the specified date range
        $jobSummary = CompanyJob::with('companyJobSummaries')
            ->where('created_by', $company_id)
            ->whereIn('status_id', $statusIds) 
            // ->whereBetween('created_at', [$startDate, $endDate])  // Date range filter
            ->get();
        // Prepare response data
        $jobCountsAndTotal = $jobSummary->map(function ($job) use ($statusNames) {
            // Get the count of job summaries (company_job_summaries)
            $jobCount = $job->companyJobSummaries->count();
            // return response($jobCount->toArray());

            // Get the sum of the job totals (amount)
            $jobTotalAmount = $job->companyJobSummaries->sum('job_total');

            // Get the status name using the status_id
            $statusName = $statusNames[$job->status_id] ?? 'Unknown Status';  // Default to 'Unknown Status' if not found

            return [
                'status_id' => $job->status_id,
                'status_name' => $statusName,
                'job_count' => $jobCount,
                'total_amount' => $jobTotalAmount,
            ];
        });

        // Return the response as JSON
        return response()->json([
            'data' => $jobCountsAndTotal,
        ]);
    }





    

}
