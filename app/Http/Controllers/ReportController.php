<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use App\Models\User;
use App\Models\Report;
use App\Models\CompanyJob;
use Illuminate\Http\Request;
use App\Resources\ReportResource;
use App\Http\Resources\ReportPdfResource;

class ReportController extends Controller
{
    public function userReports(Request $request)
    {
        $request->validate([
            'startDate' => 'nullable|date_format:Y-m-d',
            'endDate' => 'nullable|date_format:Y-m-d|after_or_equal:startDate',
        ]);

        $user = Auth::user();
        $company_id = $user->company_id;

        $startDate = $request->startDate ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->endDate ?? now()->endOfMonth()->format('Y-m-d');

        $users = User::query()
            ->when($user->role_id !== 7, function ($query) use ($company_id) {
                return $query->where('company_id', $company_id);
            })
            ->whereIn('role_id', [1, 2, 8, 9])
            ->get();

        $reports = $users->map(function ($user) use ($startDate, $endDate) {
            $jobWonCount = DB::table('company_jobs')
                ->where('user_id', $user->id)
                ->where('status_id', 15) // Job won status
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $jobWonTotal = DB::table('company_jobs')
                ->join('company_job_summaries', 'company_job_summaries.company_job_id', '=', 'company_jobs.id')
                ->where('company_jobs.user_id', $user->id)
                ->where('company_jobs.status_id', 15) // Job won status
                ->whereBetween('company_job_summaries.created_at', [$startDate, $endDate])
                ->sum('company_job_summaries.job_total');

            // Count of new leads (status_id = 1) for this user
            $newLeadsCount = DB::table('company_jobs')
                ->where('user_id', $user->id)
                ->where('status_id', 1) // New leads status
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $conversionRate = $newLeadsCount > 0 ? round(($jobWonCount / $newLeadsCount) * 100, 2) : 0;

            $averageJobWonValue = $jobWonCount > 0 ? round($jobWonTotal / $jobWonCount, 2) : 0;

            return [
                'user' => $user,
                'new_leads' => $newLeadsCount,
                'job_won' => $jobWonCount,
                'value_jobs_won' => $jobWonTotal,
                'average_job_value' => $averageJobWonValue,
                'conversion_rate' => $conversionRate
            ];
        });

        return response()->json($reports);
    }



    public function getPipelineData(Request $request)
    {
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

        $user = auth()->user();
        $company_id = $user->company_id;
        $role_id = $user->role_id;

        $query = CompanyJob::with('companyJobSummaries')
            ->whereIn('status_id', $statusIds);

        if ($role_id != 7) {
            $query->where('created_by', $company_id);
        }

        $jobSummary = $query->get();

        // Prepare response data with counts and sums for each status
        $jobCountsAndTotal = collect($statusIds)->map(function ($statusId) use ($jobSummary, $statusNames) {
            $jobsForStatus = $jobSummary->where('status_id', $statusId);
            $jobCount = $jobsForStatus->count();
            $jobTotalAmount = $jobsForStatus->sum(function ($job) {
                return $job->companyJobSummaries->sum('job_total');
            });

            return [
                'status_id' => $statusId,
                'status_name' => $statusNames[$statusId] ?? 'Unknown Status',
                'job_count' => $jobCount > 0 ? $jobCount : null,
                'total_amount' => $jobTotalAmount > 0 ? $jobTotalAmount : null,
            ];
        });

        return response()->json([
            'status_code' => 200,
            'status' => true,
            'message' => 'Pipeline Data fetched Successfully',
            'data' => $jobCountsAndTotal,
        ]);
    }

    public function getOwnPipelineData(Request $request)
    {
        $statusIds = [1, 2, 3, 4, 8, 9, 10, 11, 12, 13, 14, 15];
        $statusNames = [
            1 => 'New Leads',
            2 => 'Signed Deals',
            3 => 'Estimate Prepared',
            4 => 'Adjustor',
            8 => 'Ready To Build',
            9 => 'Build Scheduled',
            10 => 'In Progress',
            1,
            11 => 'Build Complete',
            12 => 'COC Required',
            13 => 'Final Payment Due',
            14 => 'Ready to Close',
            15 => 'Won and Closed'
        ];

        $user = auth()->user();
        $user_id = $user->id;
        $company_id = $user->company_id;
        $role_id = $user->role_id;

        $jobSummary = CompanyJob::with('companyJobSummaries')
            ->whereIn('status_id', $statusIds)
            ->where('user_id', $user_id)
            ->get();

        // Prepare response data with counts and sums for each status
        $jobCountsAndTotal = collect($statusIds)->map(function ($statusId) use ($jobSummary, $statusNames) {
            $jobsForStatus = $jobSummary->where('status_id', $statusId);
            $jobCount = $jobsForStatus->count();
            $jobTotalAmount = $jobsForStatus->sum(function ($job) {
                return $job->companyJobSummaries->sum('job_total');
            });

            return [
                'status_id' => $statusId,
                'status_name' => $statusNames[$statusId] ?? 'Unknown Status',
                'job_count' => $jobCount > 0 ? $jobCount : null,
                'total_amount' => $jobTotalAmount > 0 ? $jobTotalAmount : null,
            ];
        });

        return response()->json([
            'status_code' => 200,
            'status' => true,
            'message' => 'Data fetched Successfully',
            'data' => $jobCountsAndTotal,
        ]);
    }

    public function getReportPdf($jobId)
    {
        try {
            // Attempt to fetch reports based on the jobId
            $reports = Report::where('job_id', $jobId)->get();

            // Return a success response with the transformed reports using the ReportResource
            return ReportPdfResource::collection($reports)->additional([
                'status_code' => 200,
                'status' => true,
                'message' => 'Data fetched successfully',
            ]);
        } catch (\Exception $e) {
            // If an error occurs, catch the exception and return an error response
            return response()->json([
                'status_code' => 500,
                'status' => false,
                'message' => 'An error occurred while fetching the data: ' . $e->getMessage(),
            ], 500);
        }
    }

    ///////////////////job details section of reports ////////////////////////////
    public function getJobReports($jobId)
    {
        try {
            $reports = Report::with('reportPages.pageData')->where('job_id', $jobId)->paginate(5);
            $company = CompanyJob::find($jobId);
            $companyAddress = json_decode($company->address);
            $address = $companyAddress->formatedAddress;
            
            return view('reports_layout.index', compact('reports','company','address'));
        } catch (\Exception $e) {
            abort(500, 'An error occurred while fetching reports.');
        }
    }
}
