<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Models\CompanyJob;
use Illuminate\Http\Request;
use App\Models\JobContentMedia;
use App\Models\CompanyJobSummary;
use App\Models\CustomerAgreement;
use App\Models\ProjectDesignPage;
use Illuminate\Support\Collection;
use App\Events\JobStatusUpdateEvent;
use Illuminate\Support\Facades\Auth;
use App\Models\ProjectDesignPageStatus;
use Illuminate\Support\Facades\Storage;

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

            //Fire an Event
            event(new JobStatusUpdateEvent('Refresh Pgae'));

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

    public function getJobSummary($id)
    {
        try {

            //Check Job
            $job = CompanyJob::find($id);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            $job_summary = CompanyJobSummary::where('company_job_id', $job->id)->with('images')->first();
            if(!$job_summary) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Summary Not Found'
                ], 422);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Job Summary Found Successfully',
                'job' => $job_summary
            ], 200); 

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function updateJobContent(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'notes' => 'required',
            'images' => 'required|array',
            'images.*' => 'mimes:png,jpg,jpeg,gif'
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

            $job_summary = CompanyJobSummary::where('company_job_id', $job->id)->first();
            if(!$job_summary) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Summary Not Found'
                ], 422);
            }

            //Update Job Summary
            $job_summary->notes = $request->notes;
            $job_summary->save();

            if(isset($request->images)) {
                //Remove Old Images
                $oldImages = JobContentMedia::where('summary_id', $job_summary->id)->get();
                foreach($oldImages as $oldImage) {
                    $oldFilePath = str_replace('/storage', 'public', $oldImage->media_url);
                    Storage::delete($oldFilePath);
                    $oldImage->delete();
                }

                //Store New Images
                foreach($request->file('images') as $file)
                {
                    $fileName = $file->getClientOriginalName();
                    $fileName = time() . '_' . $fileName;
                    $path = $file->storeAs('public/job_content_media', $fileName);

                    //Update Job Content
                    $media = new JobContentMedia;
                    $media->summary_id = $job_summary->id;
                    $media->media_url = Storage::url($path);
                    $media->save();
                }
            }

            return response()->json([
                'status' => 200,
                'message' => 'Job Content Updated Successfully',
                'job' => $job
            ], 200); 

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

}
