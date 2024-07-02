<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CompanyJob;

class CompanyJobController extends Controller
{
    public function createJob(Request $request)
    {
        try {
            $this->validate($request, [
                'address' => 'required',
                'latitude' => 'required',
                'longitude' => 'required',
                'name' => 'required',
                'email' => 'required|unique:company_jobs,email',
                'phone' => 'required'
            ]);

            //Create Job
            $job = new CompanyJob;
            $job->status_id = 1;
            $job->user_id = Auth::user()->created_by;
            $job->name = $request->name;
            $job->address = $request->address;
            $job->latitude = $request->latitude;
            $job->longitude = $request->longitude;
            $job->email = $request->email;
            $job->phone = $request->phone;
            $job->save();

            return response()->json(['job' => $job], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getAllJobs()
    {
        try {
            $user = Auth::user();
            $jobs = CompanyJob::where('user_id', $user->created_by)
            ->with('status')
            ->orderBy('status_id', 'asc')
            ->get();

            // Group jobs by status name
            $groupedJobs = $jobs->groupBy(function ($job) {
                return $job->status->name;
            });
            return response()->json(['jobs' => $groupedJobs], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
