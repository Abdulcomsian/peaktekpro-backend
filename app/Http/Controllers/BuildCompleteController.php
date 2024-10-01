<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\CompanyJob;
use App\Models\JobLog;
use App\Models\SubPaySheet;
use App\Models\BuildComplete;
use App\Models\BuildCompleteMedia;
use App\Models\JobLogMedia;
use App\Models\SubPaySheetMedia;
use Carbon\Carbon;

class BuildCompleteController extends Controller
{
    public function updateBuildComplete(Request $request, $jobId)
    {
        //Validate Request
        $this->validate($request, [
            'build_end_date' => 'required|date_format:m/d/Y',   
            'status' => 'nullable',
            'attachments' => 'nullable|array'
        ]);
        
        DB::beginTransaction();
        try {
            
            //Check Job
            $job = CompanyJob::find($jobId);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }
            
            $build_complete = BuildComplete::updateOrCreate([
               'company_job_id' => $jobId 
            ],[
               'company_job_id' => $jobId,
               'build_end_date' => $request->build_end_date,
               'status' => $request->status
            ]);
            
            //Store Attachments
            if(isset($request->attachments) && count($request->attachments) > 0) {
                
                //Remove Old Attachments
                $oldAttachments = BuildCompleteMedia::where('build_complete_id', $build_complete->id)->get();
                if(count($oldAttachments) > 0) {
                    foreach($oldAttachments  as $oldAttachment) {
                        //Delete
                        $oldAttachmentPath = str_replace('/storage', 'public', $oldAttachment->media_url);
                        Storage::delete($oldAttachmentPath);
                        $oldAttachment->delete();
                    }
                }
                
                //Store New Images
                foreach($request->file('attachments') as $attachment) {
                    $attachment_fileName = time() . '_' . $attachment->getClientOriginalName();
                    $attachment_filePath = $attachment->storeAs('public/build_complete_attachments', $attachment_fileName);

                    // Store Path
                    $build_complete_media = new BuildCompleteMedia();
                    $build_complete_media->build_complete_id = $build_complete->id;
                    $build_complete_media->media_url = Storage::url($attachment_filePath);
                    $build_complete_media->save();
                }
            }
            
            //Update Status
            if(isset($request->status) && $request->status == true) {
                $job->status_id = 12;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();
            }
            
            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => 'Build Complete Updated Successfully',
                'data' => $build_complete
            ], 200);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    
    public function getBuildComplete($jobId)
    {
        try {
            
            //Check Job
            $job = CompanyJob::find($jobId);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }
            
            $build_complete = BuildComplete::where('company_job_id', $jobId)->with('attachments')->first();
            if(!$build_complete) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Build Complete Not Yet Created'
                ], 422);
            }
            
            return response()->json([
                'status' => 200,
                'message' => 'Build Complete Found Successfully',
                'data' => $build_complete
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    
    public function updateSubpaySheet(Request $request, $buildCompleteId)
    {
        //Validate Request
        $this->validate($request, [
            'contractor' => 'required|string|max:255',
            'contractor_email' => 'required|email',   
            'notes' => 'nullable',
            'attachments' => 'nullable|array'
        ]);
        
        DB::beginTransaction();
        try {
            
            //Check Build Complete
            $build_complete = BuildComplete::find($buildCompleteId);
            if(!$build_complete) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Build Complete Not Found'
                ], 422);
            }
            
            //Update Subpay Sheet
            $subpay_sheet = SubPaySheet::updateOrCreate([
               'build_complete_id' => $buildCompleteId 
            ],[
               'build_complete_id' => $buildCompleteId,
               'contractor' => $request->contractor,
               'contractor_email' => $request->contractor_email,
               'notes' => $request->notes,
            ]);
            
            //Store Attachments
            if(isset($request->attachments) && count($request->attachments) > 0) {
                
                //Remove Old Attachments
                $oldAttachments = SubPaySheetMedia::where('sub_pay_sheet_id', $subpay_sheet->id)->get();
                if(count($oldAttachments) > 0) {
                    foreach($oldAttachments  as $oldAttachment) {
                        //Delete
                        $oldAttachmentPath = str_replace('/storage', 'public', $oldAttachment->media_url);
                        Storage::delete($oldAttachmentPath);
                        $oldAttachment->delete();
                    }
                }
                
                //Store New Images
                foreach($request->file('attachments') as $attachment) {
                    $attachment_fileName = time() . '_' . $attachment->getClientOriginalName();
                    $attachment_filePath = $attachment->storeAs('public/subpay_sheet_attachments', $attachment_fileName);

                    // Store Path
                    $subpay_sheet_media = new SubPaySheetMedia();
                    $subpay_sheet_media->sub_pay_sheet_id = $subpay_sheet->id;
                    $subpay_sheet_media->media_url = Storage::url($attachment_filePath);
                    $subpay_sheet_media->save();
                }
            }
            
            
            
            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => 'Sub Pay Sheet Updated Successfully',
                'data' => $subpay_sheet
            ], 200);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    
    public function getSubpaySheet($buildCompleteId)
    {
        try {
            
            //Check Build Complete
            $build_complete = BuildComplete::find($buildCompleteId);
            if(!$build_complete) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Build Complete Not Found'
                ], 422);
            }
            
            //Check Subpay Sheet
            $sub_pay_sheet = SubPaySheet::where('build_complete_id', $buildCompleteId)->with('attachments')->first();
            if(!$sub_pay_sheet) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Sub Pay Sheet Not Yet Created'
                ], 422);
            }
            
            return response()->json([
                'status' => 200,
                'message' => 'Sub Pay Sheet Found Successfully',
                'data' => $sub_pay_sheet
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    
    public function updateJobLog(Request $request, $buildCompleteId)
    {
        //Validate Request
        $this->validate($request, [
            'contractor' => 'required|string|max:255',
            'notes' => 'nullable',
            'attachments' => 'nullable|array'
        ]);
        
        DB::beginTransaction();
        try {
            
            //Check Build Complete
            $build_complete = BuildComplete::find($buildCompleteId);
            if(!$build_complete) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Build Complete Not Found'
                ], 422);
            }
            
            //Update Job Log
            $job_log = JobLog::updateOrCreate([
               'build_complete_id' => $buildCompleteId 
            ],[
               'build_complete_id' => $buildCompleteId,
               'contractor' => $request->contractor,
               'notes' => $request->notes,
            ]);
            
            //Store Attachments
            if(isset($request->attachments) && count($request->attachments) > 0) {
                
                //Remove Old Attachments
                $oldAttachments = JobLogMedia::where('job_log_id', $job_log->id)->get();
                if(count($oldAttachments) > 0) {
                    foreach($oldAttachments  as $oldAttachment) {
                        //Delete
                        $oldAttachmentPath = str_replace('/storage', 'public', $oldAttachment->media_url);
                        Storage::delete($oldAttachmentPath);
                        $oldAttachment->delete();
                    }
                }
                
                //Store New Images
                foreach($request->file('attachments') as $attachment) {
                    $attachment_fileName = time() . '_' . $attachment->getClientOriginalName();
                    $attachment_filePath = $attachment->storeAs('public/job_log_attachments', $attachment_fileName);

                    // Store Path
                    $job_log_media = new JobLogMedia();
                    $job_log_media->job_log_id = $job_log->id;
                    $job_log_media->media_url = Storage::url($attachment_filePath);
                    $job_log_media->save();
                }
            }
            
            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => 'Job Log Updated Successfully',
                'data' => $job_log
            ], 200);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    
    public function getJobLog($buildCompleteId)
    {
        try {
            
            //Check Build Complete
            $build_complete = BuildComplete::find($buildCompleteId);
            if(!$build_complete) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Build Complete Not Found'
                ], 422);
            }
            
            //Check Subpay Sheet
            $job_log = JobLog::where('build_complete_id', $buildCompleteId)->with('attachments')->first();
            if(!$job_log) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Log Not Yet Created'
                ], 422);
            }
            
            return response()->json([
                'status' => 200,
                'message' => 'Job Log Found Successfully',
                'data' => $job_log
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
