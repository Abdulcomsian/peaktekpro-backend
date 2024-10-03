<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CompanyJob;
use App\Models\ReadyToBuild;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReadyToBuildController extends Controller
{
    public function storeReadyToBuild(Request $request, $jobId)
    {
        // dd($request->all());
        //Validation Request
        $this->validate($request, [
            'home_owner' => 'nullable|string|max:255',
            'home_owner_email' => 'nullable|email',
            'date' => 'nullable|date_format:m/d/Y',
            'notes' => 'nullable|string',
            'attachements.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,txt',	
            'status' => 'nullable|in:true,false',
             'completed' => 'nullable|in:true,false'

        ]);
        try {

            //Check Job
            $job = CompanyJob::find($jobId);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job not found',
                    'data' => (object)[] 

                ], 422);
            }

             // Handle attachments
            $attachmentPaths = [];
            if ($request->hasFile('attachements')) {
                foreach ($request->file('attachements') as $attachment) {
                    \Log::info($request->file('attachements'));
                    \Log::info('Processing attachment: ' . $attachment->getClientOriginalName());

                    $attachmentPaths[] = $attachment->store('ready_to_build', 'public');
                }
            }
            \Log::info('Attachment paths: ', $attachmentPaths);

            //Update Ready To Build
            $ready_to_build = ReadyToBuild::updateOrCreate([
                'company_job_id' => $jobId,
            ],[
                'company_job_id' => $jobId,
                'home_owner' => $request->home_owner,
                'home_owner_email' => $request->home_owner_email,
                'date' => $request->date,
                'notes' => $request->notes,
                'attachements' => json_encode($attachmentPaths),
                'status' => $request->status,
            ]);
            
            //Update Status
            if(isset($request->completed) && $request->completed == true) {
                $job->status_id = 8;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();
            }

            return response()->json([
                'status' => 200,
                'message' => 'Ready To Build Added Successfully',
                'data' => $ready_to_build
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getReadyToBuild($jobId)
    {
        try {
            // Check Job
            $job = CompanyJob::find($jobId);
            if (!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job not found'
                ], 422);
            }

            // Retrieve Ready To Build
            $readyToBuild = ReadyToBuild::with('companyJob.status')->where('company_job_id', $jobId)->first();
            
            if (!$readyToBuild) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Ready To Build Not Yet Created',
                    'data' => []
                ], 200);
            }

            // Return response with Ready To Build details
            return response()->json([
                'status' => 200,
                'message' => 'Ready To Build Found Successfully',
                'data' => [
                    'id' => $readyToBuild->id,
                    'company_job_id' => $readyToBuild->company_job_id,
                    'home_owner' => $readyToBuild->home_owner,
                    'home_owner_email' => $readyToBuild->home_owner_email,
                    'date' => $readyToBuild->date,
                    'notes' => $readyToBuild->notes,
                    'attachements' => json_decode($readyToBuild->attachements),
                    'status' => $readyToBuild->status,
                    'created_at' => $readyToBuild->created_at,
                    'updated_at' => $readyToBuild->updated_at,
                    'completed' => $readyToBuild->companyJob->status->name, 
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }


    // public function getReadyToBuild($jobId)
    // {
    //     try {

    //         //Check Job
    //         $job = CompanyJob::find($jobId);
    //         if(!$job) {
    //             return response()->json([
    //                 'status' => 422,
    //                 'message' => 'Job not found'
    //             ], 422);
    //         }

    //         $get_ready_to_build = ReadyToBuild::where('company_job_id', $jobId)->with('subContractor')->first();
    //         if(!$get_ready_to_build) {
    //             return response()->json([
    //                 'status' => 200,
    //                 'message' => 'Ready To Build Not Yet Created',
    //                 'data' => []
    //             ], 200);
    //         }

    //         return response()->json([
    //             'status' => 200,
    //             'message' => 'Ready To Build Found Successfully',
    //             'data' => $get_ready_to_build
    //         ], 200);

    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
    //     }
    // }

    // public function storeReadyToBuild(Request $request, $jobId)
    // {
    //     //Validation Request
    //     $this->validate($request, [
    //         'recipient' => 'required|string|max:255',
    //         'time' => 'required|date_format:h:i A', // 12-hour format
    //         'date' => 'required|date_format:m/d/Y',
    //         'text' => 'required',
    //         'sub_contractor_id' => 'required|integer',
    //         'completed' => 'nullable|in:1,0'
    //     ]);

    //     try {

    //         //Check Job
    //         $job = CompanyJob::find($jobId);
    //         if(!$job) {
    //             return response()->json([
    //                 'status' => 422,
    //                 'message' => 'Job not found'
    //             ], 422);
    //         }

    //         //Check Sub Contractor
    //         $sub_contractor = User::whereId($request->sub_contractor_id)->where('role_id', 3)->first();
    //         if(!$sub_contractor) {
    //             return response()->json([
    //                 'status' => 422,
    //                 'message' => 'Sub Contractor not found'
    //             ], 422);
    //         }

    //         //Update Ready To Build
    //         $ready_to_build = ReadyToBuild::updateOrCreate([
    //             'company_job_id' => $jobId,
    //         ],[
    //             'company_job_id' => $jobId,
    //             'sub_contractor_id' => $request->sub_contractor_id,
    //             'recipient' => $request->recipient,
    //             'date' => $request->date,
    //             'time' => $request->time,
    //             'text' => $request->text,
    //         ]);
            
    //         //Update Status
    //         if(isset($request->completed) && $request->completed == true) {
    //             $job->status_id = 9;
    //             $job->date = Carbon::now()->format('Y-m-d');
    //             $job->save();
    //         }

    //         return response()->json([
    //             'status' => 200,
    //             'message' => 'Ready To Build Added Successfully',
    //             'data' => $ready_to_build
    //         ], 200);

    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
    //     }
    // }
}
