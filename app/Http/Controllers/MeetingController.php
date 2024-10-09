<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Models\CompanyJob;
use Illuminate\Http\Request;
use App\Models\AdjustorMeeting;
use App\Models\OverturnMeeting;
use Illuminate\Support\Facades\DB;
use App\Events\JobStatusUpdateEvent;
use App\Models\OverturnMeetingMedia;
use App\Models\AdjustorMeetingMedia;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MeetingController extends Controller
{
    public function createAdjustorMeeting(Request $request, $jobId)
    {
        //Validate Rules
        $rules = [
            'email' => 'nullable|email',
            // 'time' => 'nullable|date_format:h:i A', // 12-hour format
            'date' => 'nullable|date_format:m/d/Y',
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable',
            'sent' => 'nullable',
            'status' => 'nullable|in:approved,overturn,appraisal',
            'attachments.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,txt',
            'image .*' => 'nullable|image|max:10240|mimes:png,jpg,jpeg,gif',
            'notes' => 'nullable'
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
                'email' => $request->email,
                'date' => $request->date,
                // 'time' => $request->time,
                'name' => $request->name,
                'phone' => $request->phone,
                'notes' => $request->notes,
                'status' => isset($request->status) ? $request->status : 'pending',
                'sent' => $request->sent
            ]);
            
            //Store Meeting Attachments
            if(isset($request->attachments) && count($request->attachments) > 0) {
                // Remove old attachments
                // $oldAttachments = AdjustorMeetingMedia::where('adjustor_id', $adjustor_meeting->id)->where('media_type', 'Document')->get();
                // foreach ($oldAttachments as $oldAttachment) {
                //     $oldFilePath = str_replace('/storage', 'public', $oldAttachment->url);
                //     Storage::delete($oldFilePath);
                //     $oldAttachment->delete();
                // }

                //Store New Attachments
                foreach($request->attachments as $documents) {
                    $fileName = time() . '_' . $documents->getClientOriginalName();
                    $filePath = $documents->storeAs('public/adjustor_meeting_attachments', $fileName);

                    // Store Path
                    $media = new AdjustorMeetingMedia();
                    $media->adjustor_id = $adjustor_meeting->id;
                    $media->media_type = 'Document';
                    $media->media_url = Storage::url($filePath);
                    $media->save();
                }
            } 

            //Store Meeting Images
            if(isset($request->image) && count($request->image) > 0) {
                // Remove old attachments
                // $oldImages = AdjustorMeetingMedia::where('adjustor_id', $adjustor_meeting->id)->where('media_type', 'image')->get();
                // foreach ($oldImages as $oldImage) {
                //     $oldImagePath = str_replace('/storage', 'public', $oldImage->url);
                //     Storage::delete($oldImagePath);
                //     $oldImage->delete();
                // }

                //Store New Images
                foreach($request->image as $image) {
                    $image_fileName = time() . '_' . $image->getClientOriginalName();
                    $image_filePath = $image->storeAs('public/adjustor_meeting_images', $image_fileName);

                    // Store Path
                    $media = new AdjustorMeetingMedia();
                    $media->adjustor_id = $adjustor_meeting->id;
                    $media->media_type = 'image';
                    $media->media_url = Storage::url($image_filePath);
                    $media->save();
                }
            } 
            
            //Update Status when select approved and nothing happen upon apprisal and overturn
            // if(isset($request->completed) && $request->completed == 1 && isset($request->status) && $request->status == 'Approved') {
            //     $job->status_id = 8;
            //     $job->date = Carbon::now()->format('Y-m-d');
            //     $job->save();   
            // }

            if(isset($request->status) && $request->status == 'approved') {
                $job->status_id = 8;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();   
            }

            return response()->json([
                'status' => 200,
                'message' => 'Adjustor Meeting Created Successfully',
                'data' => $adjustor_meeting
            ], 200); 

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    
    public function updateAdjustorMeetingMedia(Request $request, $jobId)
    {
        //Validate Rules
        $this->validate($request, [
            'attachments .*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,txt',
            'image.*' => 'nullable|image|max:10240|mimes:png,jpg,jpeg,gif',
            'manufacturer_attachments.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,txt',
            'notes' => 'nullable'
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

            //Update Overturn Meeting
            $adjustor_meeting = AdjustorMeeting::updateOrCreate([
                'company_job_id' => $jobId,
            ],[
                'company_job_id' => $jobId,
                'notes' => $request->notes
            ]);

            //Store Meeting Attachments
            if(isset($request->attachments) && count($request->attachments) > 0) {
                // Remove old attachments
                $oldAttachments = AdjustorMeetingMedia::where('adjustor_id', $adjustor_meeting->id)->where('media_type', 'Document')->get();
                foreach ($oldAttachments as $oldAttachment) {
                    $oldFilePath = str_replace('/storage', 'public', $oldAttachment->url);
                    Storage::delete($oldFilePath);
                    $oldAttachment->delete();
                }

                //Store New Attachments
                foreach($request->attachments as $documents) {
                    $fileName = time() . '_' . $documents->getClientOriginalName();
                    $filePath = $documents->storeAs('public/adjustor_meeting_attachments', $fileName);

                    // Store Path
                    $media = new AdjustorMeetingMedia();
                    $media->adjustor_id = $adjustor_meeting->id;
                    $media->media_type = 'Document';
                    $media->media_url = Storage::url($filePath);
                    $media->save();
                }
            } 

            //Store Meeting Images
            if(isset($request->image) && count($request->image) > 0) {
                // Remove old attachments
                $oldImages = AdjustorMeetingMedia::where('adjustor_id', $adjustor_meeting->id)->where('media_type', 'image')->get();
                foreach ($oldImages as $oldImage) {
                    $oldImagePath = str_replace('/storage', 'public', $oldImage->url);
                    Storage::delete($oldImagePath);
                    $oldImage->delete();
                }

                //Store New Images
                foreach($request->image as $image) {
                    $image_fileName = time() . '_' . $image->getClientOriginalName();
                    $image_filePath = $image->storeAs('public/adjustor_meeting_images', $image_fileName);

                    // Store Path
                    $media = new AdjustorMeetingMedia();
                    $media->adjustor_id = $adjustor_meeting->id;
                    $media->media_type = 'image';
                    $media->media_url = Storage::url($image_filePath);
                    $media->save();
                }
            } 

            //Store Manufacturer Attachments
            if(isset($request->manufacturer_attachments) && count($request->manufacturer_attachments) > 0) {
                // Remove old attachments
                $oldManufacturerAttachments = AdjustorMeetingMedia::where('adjustor_id', $adjustor_meeting->id)->where('media_type', 'Manufacturer Document')->get();
                foreach ($oldManufacturerAttachments as $oldManufacturerAttachment) {
                    $oldManufacturerFilePath = str_replace('/storage', 'public', $oldManufacturerAttachment->url);
                    Storage::delete($oldManufacturerFilePath);
                    $oldManufacturerAttachment->delete();
                }

                //Store New Attachments
                foreach($request->manufacturer_attachments as $manufacturer_attachment) {
                    $manufacturerFileName = time() . '_' . $manufacturer_attachment->getClientOriginalName();
                    $manufacturerFilePath = $manufacturer_attachment->storeAs('public/adjustor_meeting_manufacturer_attachments', $manufacturerFileName);

                    // Store Path
                    $media = new AdjustorMeetingMedia();
                    $media->adjustor_id = $adjustor_meeting->id;
                    $media->media_type = 'Manufacturer Document';
                    $media->media_url = Storage::url($manufacturerFilePath);
                    $media->save();
                }
            }

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => 'Adjustor Meeting Updated Successfully',
                'data' => $adjustor_meeting
            ], 200); 

        } catch (\Exception $e) {
            DB::rollback();
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

            if($request->status != 'Denial') {
                if($request->status == 'Full Approval') {
                    //Update Job Status
                    $job->status_id = 4;
                    $job->date = Carbon::now()->format('Y-m-d');
                    $job->save();

                    //Update Meeting Status
                    $adjustor_meeting->status = 'completed';
                    $adjustor_meeting->save();
                } else {
                    $job->status_id = 5;
                    $job->date = Carbon::now()->format('Y-m-d');
                    $job->save();
                }
            }

            //Fire an Event
            event(new JobStatusUpdateEvent('Refresh Pgae'));

            return response()->json([
                'status' => 200,
                'message' => 'Job Status Updated Successfully',
                'data' => $job
            ], 200);            

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getAdjustorMeeting($jobId)
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

            $adjustor_meeting = AdjustorMeeting::where('company_job_id', $jobId)->with('images','attachments')->first();

            // Transform the response
            if ($adjustor_meeting) {
                $data = [
                    'id' => $adjustor_meeting->id,
                    'company_job_id' => $adjustor_meeting->company_job_id,
                    'name' => $adjustor_meeting->name,
                    'phone' => $adjustor_meeting->phone,
                    'email' => $adjustor_meeting->email,
                    'time' => $adjustor_meeting->time,
                    'date' => $adjustor_meeting->date,
                    'notes' => $adjustor_meeting->notes,
                    'status' => $adjustor_meeting->status,
                    'sent' => $adjustor_meeting->sent,
                    'created_at' => $adjustor_meeting->created_at,
                    'updated_at' => $adjustor_meeting->updated_at,
                    'image_url' => $adjustor_meeting->images->pluck('media_url'),
                    'documents' => $adjustor_meeting->attachments->pluck('media_url'),
                ];

                return response()->json([
                    'status' => 200,
                    'message' => 'Adjustor Meeting Found Successfully',
                    'data' => $data,
                ]);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'Adjustor Meeting Not Found',
                ]);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    
    public function changeAdjustorMeetingFileName(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'file_name' => 'required|string'
        ]);
        
        try {
            
            //Check Adjustor Meeting Media
            $check_adjustor_media = AdjustorMeetingMedia::find($id);
            if(!$check_adjustor_media) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Adjustor Meeting Media Not Found'
                ], 422);
            }

            //Update File Name
            $check_adjustor_media->file_name = $request->file_name;
            $check_adjustor_media->save();

            return response()->json([
                'status' => 200,
                'message' => 'File Name Updated Successfully',
                'data' => []
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    
    public function deleteAdjustorMeetingMedia(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'image_url' => 'required|string'
        ]);
        
        try {
            
            //Check Adjustor Meeting Media
            $check_adjustor_media = AdjustorMeetingMedia::find($id);
            if(!$check_adjustor_media) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Adjustor Meeting Media Not Found'
                ], 422);
            }

            //Delete Media
            $oldImagePath = str_replace('/storage', 'public', $check_adjustor_media->media_url);
            Storage::delete($oldImagePath);
            $check_adjustor_media->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Media Deleted Successfully',
                'data' => $check_adjustor_media
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function createOverturnMeeting(Request $request, $jobId)
    {
        //Validate Rules
        $rules = [
            'email' => 'nullable|email',
            'time' => 'nullable|date_format:h:i A', // 12-hour format
            'date' => 'nullable|date_format:m/d/Y',
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable',
        ];

        // If updating an existing record, ignore the current record's email for uniqueness check
        $overturnMeeting = OverturnMeeting::where('company_job_id', $jobId)->first();
        if($overturnMeeting) {
            $rules['email'] .= '|unique:overturn_meetings,email,' . $overturnMeeting->id;
        } else {
            $rules['email'] .= '|unique:overturn_meetings,email';
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

            //Create Overturn Meeting
            $overturn_meeting = OverturnMeeting::updateOrCreate([
                'company_job_id' => $jobId,
            ],[
                'company_job_id' => $jobId,
                'email' => $request->email,
                'date' => $request->date,
                'time' => $request->time,
                'name' => $request->name,
                'phone' => $request->phone,
            ]); 

            return response()->json([
                'status' => 200,
                'message' => 'Overturn Meeting Created Successfully',
                'data' => $overturn_meeting
            ], 200); 

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function updateOverturnMeetingMedia(Request $request, $jobId)
    {
        //Validate Rules
        $this->validate($request, [
            'attachments.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,txt',
            'images.*' => 'nullable|image|max:10240|mimes:png,jpg,jpeg,gif',
            'manufacturer_attachments.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,txt',
            'notes' => 'nullable'
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

            //Update Overturn Meeting
            $overturn_meeting = OverturnMeeting::updateOrCreate([
                'company_job_id' => $jobId,
            ],[
                'company_job_id' => $jobId,
                'notes' => $request->notes
            ]);

            //Store Meeting Attachments
            if(isset($request->attachments) && count($request->attachments) > 0) {
                //Store New Attachments
                foreach($request->attachments as $attachment) {
                    $fileName = time() . '_' . $attachment->getClientOriginalName();
                    $filePath = $attachment->storeAs('public/overturn_meeting_attachments', $fileName);

                    // Store Path
                    $media = new OverturnMeetingMedia();
                    $media->overturn_id = $overturn_meeting->id;
                    $media->media_type = 'Document';
                    $media->media_url = Storage::url($filePath);
                    $media->save();
                }
            } 

            //Store Meeting Images
            if(isset($request->images) && count($request->images) > 0) {
                //Store New Images
                foreach($request->images as $image) {
                    $image_fileName = time() . '_' . $image->getClientOriginalName();
                    $image_filePath = $image->storeAs('public/overturn_meeting_images', $image_fileName);

                    // Store Path
                    $media = new OverturnMeetingMedia();
                    $media->overturn_id = $overturn_meeting->id;
                    $media->media_type = 'image';
                    $media->media_url = Storage::url($image_filePath);
                    $media->save();
                }
            } 

            //Store Manufacturer Attachments
            if(isset($request->manufacturer_attachments) && count($request->manufacturer_attachments) > 0) {
                //Store New Attachments
                foreach($request->manufacturer_attachments as $manufacturer_attachment) {
                    $manufacturerFileName = time() . '_' . $manufacturer_attachment->getClientOriginalName();
                    $manufacturerFilePath = $manufacturer_attachment->storeAs('public/overturn_meeting_attachments', $manufacturerFileName);

                    // Store Path
                    $media = new OverturnMeetingMedia();
                    $media->overturn_id = $overturn_meeting->id;
                    $media->media_type = 'Manufacturer Document';
                    $media->media_url = Storage::url($manufacturerFilePath);
                    $media->save();
                }
            }

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => 'Overturn Meeting Updated Successfully',
                'data' => $overturn_meeting
            ], 200); 

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getOverturnMeeting($jobId)
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

            $overturn_meeting = OverturnMeeting::where('company_job_id', $jobId)->with('attachments','images','manufacturerAttachments')->first();

            return response()->json([
                'status' => 200,
                'message' => 'Overturn Meeting Found Successfully',
                'data' => $overturn_meeting
            ], 200);


        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function updateOverturnMeetingStatus(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'status' => 'required|in:Full Approval,Archive'
        ]);

        try {

            //Check Adjustor Meeting
            $overturn_meeting = OverturnMeeting::find($id);
            if(!$overturn_meeting) {
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

            if($request->status == 'Full Approval') {
                //Update Job Status
                $job->status_id = 4;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();

                //Update Meeting Status
                $overturn_meeting->status = 'completed';
                $overturn_meeting->save();
            } else {
                $job->status_id = 5;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();
            }

            //Fire an Event
            event(new JobStatusUpdateEvent('Refresh Pgae'));

            return response()->json([
                'status' => 200,
                'message' => 'Job Status Updated Successfully',
                'data' => $job
            ], 200);            

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function updateOverturnMeetingFileName(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'file_name' => 'required|string'
        ]);

        try {

            //Check Overturn Meeting
            $check_overturn_meeting_media = OverturnMeetingMedia::find($id);
            if(!$check_overturn_meeting_media) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Overturn Meeting Media Not Found'
                ], 422);
            }

            //Update File Name
            $check_overturn_meeting_media->file_name = $request->file_name;
            $check_overturn_meeting_media->save();

            return response()->json([
                'status' => 200,
                'message' => 'File Name Updated Successfully',
                'data' => []
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function deleteOverturnMeetingMedia(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'image_url' => 'required|string'
        ]);

        try {

            //Check Media
            $check_media = OverturnMeetingMedia::find($id);
            if(!$check_media) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Overturn Meeting Media Not Found'
                ], 422);
            }

            // Remove Media
            $oldPath = str_replace('/storage', 'public', $check_media->media_url);
            Storage::delete($oldPath);
            $check_media->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Media Deleted Successfully',
                'data' => []
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
