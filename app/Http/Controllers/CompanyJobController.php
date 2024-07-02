<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CompanyJob;
use App\Models\CustomerAgreement;

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

            return response()->json(['status' => 201, 'job' => $job], 201);
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
            return response()->json(['status' => 200, 'jobs' => $groupedJobs], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getSingleJob($id)
    {
        try {
            $job = CompanyJob::find($id);
            if(!$job) {
                return response()->json(['status' => 422, 'message' => 'Job not found']);
            }

            return response()->json(['status' => 200, 'job' => $job]); 
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function customerAgreement(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'street' => 'required',
                'city' => 'required',
                'state' => 'required',
                'zip_code' => 'required',
                'insurance' => 'required',
                'claim_number' => 'required',
                'policy_number' => 'required',
                'company_signature' => 'required',
                'company_printed_name' => 'required',
                'company_date' => 'required|date',
                'customer_signature' => 'required',
                'customer_printed_name' => 'required',
                'customer_date' => 'required|date',
            ]);

            $job = CompanyJob::find($id);
            if(!$job) {
                return response()->json(['status' => 422, 'message' => 'Job not found']);
            }

            $agreement = new CustomerAgreement;
            $agreement->company_job_id = $id;
            $agreement->street = $request->street;
            $agreement->city = $request->city;
            $agreement->state = $request->state;
            $agreement->zip_code = $request->zip_code;
            $agreement->insurance = $request->insurance;
            $agreement->claim_number = $request->claim_number;
            $agreement->policy_number = $request->policy_number;
            $agreement->company_signature = $request->company_signature;
            $agreement->company_printed_name = $request->company_printed_name;
            $agreement->company_date = $request->company_date;
            $agreement->customer_signature = $request->customer_signature;
            $agreement->customer_printed_name = $request->customer_printed_name;
            $agreement->customer_date = $request->customer_date;
            $agreement->save();

            return response()->json(['status' => 200, 'message' => 'Agreement created successfully', 'agreement' => $agreement]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
