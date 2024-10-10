<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\CompanyJob;
use App\Models\ReadyToBuild;
use Illuminate\Http\Request;
use App\Models\ReadyToBuildMedia;
use Illuminate\Support\Facades\Storage;

class ReadyToBuildController extends Controller
{
    public function storeReadyToBuild(Request $request, $jobId)
    {
        // Validation Request
        $this->validate($request, [
            'home_owner' => 'nullable|string|max:255',
            'home_owner_email' => 'nullable|email',
            'date' => 'nullable|date_format:m/d/Y',
            'notes' => 'nullable|string',
            'attachements.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,txt',
            'status' => 'nullable|in:true,false',
        ]);
        
        try {
            // Check Job
            $job = CompanyJob::find($jobId);
            if (!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job not found',
                ], 422);
            }

            // Update Ready To Build
            $ready_to_build = ReadyToBuild::updateOrCreate([
                'company_job_id' => $jobId,
            ], [
                'company_job_id' => $jobId,
                'home_owner' => $request->home_owner,
                'home_owner_email' => $request->home_owner_email,
                'date' => $request->date,
                'notes' => $request->notes,
                'status' => $request->status,
            ]);

            //store attachements here
            if(isset($request->attachements) && count($request->attachements) > 0) {
                foreach($request->attachements as $documents)
                {
                    $fileName = time() . '_' . $documents->getClientOriginalName();
                    $filePath = $documents->storeAs('public/ready_to_build', $fileName);
                    // Store Path
                    $media = new ReadyToBuildMedia();
                    $media->ready_build_id = $ready_to_build->id;
                    $media->media_url = Storage::url($filePath);
                    $media->file_name = $request->filename;
                    $media->save();
                }
            }
            
            // Update Status
            if (isset($request->status) && $request->status == 'true') {
                $job->status_id = 8;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();
            }

            return response()->json([
                'status' => 200,
                'message' => 'Ready To Build Added Successfully',
                'data' => $ready_to_build,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile()], 500);
        }
    }

    public function storeReadyToBuildStatus(Request $request, $jobId)
    {
        // Validation Request
        $this->validate($request, [
            'status' => 'nullable|in:true,false',
        ]);
        
        try {
            // Check Job
            $job = CompanyJob::find($jobId);
            if (!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job not found',
                ], 422);
            }

            // Update Ready To Build
            $ready_to_build = ReadyToBuild::updateOrCreate([
                'company_job_id' => $jobId,
            ], [
                'status' => $request->status,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Ready To Build Status Updated Successfully',
                'data' => [],
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile()], 500);
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
            $readyToBuild = ReadyToBuild::with('documents')->where('company_job_id', $jobId)->first();
            
            if (!$readyToBuild) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Ready To Build Not Yet Created',
                    'data' => (object)[] 
                    // 'data' => []
                ], 200);
            }

            // Return response with Ready To Build details
            return response()->json([
                'status' => 200,
                'message' => 'Ready To Build Found Successfully',
                'data' => $readyToBuild,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    //file name saved
    public function changeReadyToBuildFileName(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'file_name' => 'required|string'
        ]);
        
        try {
            
            //Check Adjustor Meeting Media
            $ready_to_build = ReadyToBuildMedia::find($id);
            if(!$ready_to_build) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Ready To Build Media Not Found'
                ], 422);
            }

            //Update File Name
            $ready_to_build->file_name = $request->file_name;
            $ready_to_build->save();

            return response()->json([
                'status' => 200,
                'message' => 'File Name Updated Successfully',
                'data' => []
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    //delete file name
    public function deleteReadyToBuildMedia(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'image_url' => 'required|string'
        ]);
        
        try {
            
            //Check Ready to build Media
            $check_ready_to_build = ReadyToBuildMedia::find($id);
            if(!$check_ready_to_build) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Ready To Build Media Not Found'
                ], 422);
            }

            //Delete Media
            $oldImagePath = str_replace('/storage', 'public', $check_ready_to_build->media_url);
            Storage::delete($oldImagePath);
            $check_ready_to_build->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Media Deleted Successfully',
                'data' => $check_ready_to_build
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
