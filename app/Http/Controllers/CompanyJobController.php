<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CompanyJob;
use App\Models\CustomerAgreement;
use App\Models\Status;
use Illuminate\Support\Collection;

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

            return response()->json([
                'status' => 201,
                'message' => 'Job Created Successfully',
                'job' => $job
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getAllJobs()
    {
        try {
            $user = Auth::user();
            $jobs = CompanyJob::select('id','name','address','status_id')->where('user_id', $user->created_by)
            ->orderBy('status_id', 'asc')
            ->get();

            // Group jobs by status name
            $groupedJobs = $jobs->groupBy(function ($job) {
                return $job->status->name;
            });

            $statuses = Status::all();
            // Structure the response
            $response = $statuses->map(function ($status) use ($groupedJobs) {
                return [
                    'id' => $status->id,
                    'name' => $status->name,
                    'tasks' => $groupedJobs->get($status->name, new Collection()),
                ];
            });

            return response()->json([
                'status' => 200,
                'message' => 'Jobs Found Successfully',
                'response' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getSingleJob($id)
    {
        try {
            $job = CompanyJob::find($id);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Job Found Successfully',
                'job' => $job
            ], 200); 
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
