<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Models\CompanyJob;
use Illuminate\Http\Request;
use App\Models\AdjustorMeeting;
use App\Models\CompanyJobSummary;
use App\Models\CustomerAgreement;
use App\Models\ProjectDesignPage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use App\Models\ProjectDesignPageStatus;

class CompanyJobController extends Controller
{
    public function createJob(Request $request)
    {
        //Validate Request
        $this->validate($request, [
            'address' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'name' => 'required',
            'email' => 'required|unique:company_jobs,email',
            'phone' => 'required'
        ]);

        try {

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

            //Update Project Design Status Table
            $pages = ProjectDesignPage::all();
            foreach($pages as $page)
            {
                $status = ProjectDesignPageStatus::updateOrCreate([
                    'project_design_page_id' => $page->id,
                    'company_job_id' => $job->id,
                ],[
                    'project_design_page_id' => $page->id,
                    'company_job_id' => $job->id,
                ]);
            }

            return response()->json([
                'status' => 201,
                'message' => 'Job Created Successfully',
                'job' => $job
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getAllJobs()
    {
        try {
            $user = Auth::user();
            $jobs = CompanyJob::select('id','name','address','status_id')->where('user_id', $user->created_by)
            ->orderBy('status_id', 'asc')
            ->orderBy('id', 'desc')
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
                'data' => $response
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getSingleJob($id)
    {
        try {
            //Check Job
            $job = CompanyJob::whereId($id)->with('summary')->first();
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
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function updateJobSummary(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'job_total' => 'required',
            'first_payment' => 'required',
            'first_payment_cheque_number' => 'required',
            'deductable' => 'required',
            'deductable_cheque_number' => 'required',
            'final_payment' => 'required',
            'final_payment_cheque_number' => 'required',
            'upgrades' => 'required',
            'upgrades_cheque_number' => 'required',
            'balance' => 'required'
        ]);

        try {

            //Check Job
            $job = CompanyJob::find($id);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            //Update Job Summary
            CompanyJobSummary::updateOrCreate([
                'company_job_id' => $id,
            ],[
                'company_job_id' => $id,
                'job_total' => $request->job_total,
                'first_payment' => $request->first_payment,
                'first_payment_cheque_number' => $request->first_payment_cheque_number,
                'deductable' => $request->deductable,
                'deductable_cheque_number' => $request->deductable_cheque_number,
                'upgrades' => $request->upgrades,
                'upgrades_cheque_number' => $request->upgrades_cheque_number,
                'final_payment' => $request->final_payment,
                'final_payment_cheque_number' => $request->final_payment_cheque_number,
                'balance' => $request->balance
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Job Summary Updated Successfully',
                'job' => $job
            ], 200); 

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function createAdjustorMeeting(Request $request, $jobId)
    {
        //Validate Rules
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'time' => 'required|date_format:h:i A', // 12-hour format
            'date' => 'required|date_format:d/m/Y',
        ];

        // If updating an existing record, ignore the current record's email for uniqueness check
        $adjustorMeeting = AdjustorMeeting::where('company_job_id', $jobId)->first();
        if($adjustorMeeting) {
            $rules['email'] .= '|unique:adjustor_meetings,email,' . $adjustorMeeting->id;
        } else {
            $rules['email'] .= '|unique:adjustor_meetings,email';
        }

        // Validate the request
        $validatedData = $request->validate($rules, []);

        try {

            //Check Job
            $job = CompanyJob::find($jobId);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            //Create Adjustor Meeting
            $adjustor_meeting = AdjustorMeeting::updateOrCreate([
                'company_job_id' => $jobId,
            ],[
                'company_job_id' => $jobId,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'date' => $request->date,
                'time' => $request->time,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Meeting Created Successfully',
                'data' => $adjustor_meeting
            ], 200); 

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function updateAdjustorMeetingStatus(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'status' => 'required|in:Full Approval,Partial Approval, Denial'
        ]);

        try {

            //Check Adjustor Meeting
            $adjustor_meeting = AdjustorMeeting::find($id);
            if(!$adjustor_meeting) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Meeting Not Found'
                ], 422);
            }

            //Check Job
            $job = CompanyJob::find($id);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            //Update Meeting Status
            $adjustor_meeting->status = $request->status;
            $adjustor_meeting->save();

            if($request->status != 'Denial') {
                if($request->status == 'Full Approval') {
                    //Get Status
                    $get_status = Status::where('name', $request->status)->first();
                    //Update Job Status
                    $job->status_id = $get_status->id;
                    $job->save();
                } else {
                    $job->status_id = 5;
                    $job->save();
                }
            }

            return response()->json([
                'status' => 200,
                'message' => 'Job Status Updated Successfully',
                'data' => $job
            ], 200);            

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
