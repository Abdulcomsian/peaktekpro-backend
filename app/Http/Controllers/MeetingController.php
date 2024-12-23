<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Status;
use App\Models\CompanyJob;
use Illuminate\Http\Request;
use App\Models\AdjustorMeeting;
use App\Models\OverturnMeeting;
use Illuminate\Support\Facades\DB;
use App\Events\JobStatusUpdateEvent;
use App\Models\AdjustorMeetingMedia;
use App\Models\AdjustorSquarePhotos;
use App\Models\OverturnMeetingMedia;
use Illuminate\Support\Facades\Storage;
use App\Models\AdjustorMeetingPhotoSection;

class MeetingController extends Controller
{
    public function createAdjustorMeeting(Request $request, $jobId)
    {
        //Validate Rules
        $rules = [
            'email' => 'nullable|email',
            'time' => 'required|date_format:h:i A',
            'date' => 'required|date_format:m/d/Y',
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable',
            'sent' => 'nullable',
            'status' => 'nullable|in:approved,overturn,appraisal',
            'attachments.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,txt',
            'images .*' => 'nullable|image|max:10240|mimes:png,jpg,jpeg,gif',
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
                'time' => $request->time,
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
            if(isset($request->images) && count($request->images) > 0) {
                // Remove old attachments
                // $oldImages = AdjustorMeetingMedia::where('adjustor_id', $adjustor_meeting->id)->where('media_type', 'image')->get();
                // foreach ($oldImages as $oldImage) {
                //     $oldImagePath = str_replace('/storage', 'public', $oldImage->url);
                //     Storage::delete($oldImagePath);
                //     $oldImage->delete();
                // }

                //Store New Images
                foreach($request->images as $image) {
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


    public function AddExteriorPhotoSection(Request $request, $Id)
    {
        // Validate request
        $request->validate([
            // 'front' => 'nullable|string',
            'exterior_front' => 'nullable|image',
            // 'front_left' => 'nullable|string',
            'exterior_front_left' => 'nullable|image',
            // 'left' => 'nullable|string',
            'exterior_left' => 'nullable|image',
            // 'back_left' => 'nullable|string',
            'exterior_back_left' => 'nullable|image',
            // 'back' => 'nullable|string',
            'exterior_back' => 'nullable|image',
            // 'back_right' => 'nullable|string',
            'exterior_back_right' => 'nullable|image',
            // 'right' => 'nullable|string',
            'exterior_right' => 'nullable|image',
            // 'front_right' => 'nullable|string',
            'exterior_front_right' => 'nullable|image',
        ]);

        $adjustor_meeting = AdjustorMeeting::where('id', $Id)->first();
        if(!$adjustor_meeting)
        {
            return response()->json([
                'message' => 'Adjustor Meeting Does not Exist',
                'status' => 404, 
                'data' => [],
            ]);
        }
        // Handle file uploads (if needed) and store file paths
        $data = [
            'adjustor_meeting_id' => $Id,
            'front' => "front",
            'front_left' => "frontleft",
            'left' => "left",
            'back_left' => "backleft",
            'back' => "back",
            'back_right' => "backright",
            'right' => "right",
            'front_right' => "frontright",
        ];

        // Process image files
        foreach (['exterior_front', 'exterior_front_left', 'exterior_left', 'exterior_back_left', 'exterior_back', 'exterior_back_right', 'exterior_right', 'exterior_front_right'] as $imageField) {
            if ($request->hasFile($imageField)) {
                // $data[$imageField] = $request->file($imageField)->store('AdjustorMeetinPhotosSections', 'public'); // Store  in the public disk
                  // Generate a unique file name
                $image_fileName = time() . '_' . $request->file($imageField)->getClientOriginalName();
                
                // Store the file in the desired directory
                $image_filePath = $request->file($imageField)->storeAs('AdjustorMeetinPhotosSections', $image_fileName, 'public');
                
                // Store the public URL of the file in the $data array
                $data[$imageField] = Storage::url($image_filePath); 

            }
        }

        // Update or create the record
        $adjustor_meeting_photos = AdjustorMeetingPhotoSection::updateOrCreate(
            ['adjustor_meeting_id' => $Id], 
            $data                        
        );

        // Return response
        return response()->json([
            'message' => 'Added successfully',
            'status' => 200,
            'data' => [
                'id'=> $adjustor_meeting_photos->id,
                    'adjustor_meeting_id' => $adjustor_meeting_photos->adjustor_meeting_id,
                    'exterior_front' => $adjustor_meeting_photos->exteriorPhotos_front,
                    'exterior_front_left' =>$adjustor_meeting_photos->exteriorPhotos_front_left,
                    'exterior_left'=>$adjustor_meeting_photos->exteriorPhotos_left,
                    'exterior_back_left'=>$adjustor_meeting_photos->exteriorPhotos_back_left,
                    'exterior_back'=>$adjustor_meeting_photos->exteriorPhotos_back,
                    'exterior_back_right'=>$adjustor_meeting_photos->exteriorPhotos_back_right,
                    'exterior_right'=>$adjustor_meeting_photos->exteriorPhotos_right,
                    'exterior_front_right' =>$adjustor_meeting_photos->exteriorPhotos_front_right,
                    'created_at'=> $adjustor_meeting_photos->created_at,
                    'updated_at' => $adjustor_meeting_photos->updated_at,
            ]
        ]);
    }


    public function getExteriorPhotoSection($Id)
    {
        // dd($Id);
        $adjustor_meeting_photos = AdjustorMeetingPhotoSection::where('adjustor_meeting_id', $Id)->first();
        if($adjustor_meeting_photos)
        {
            return response()->json([
                'message' => 'Fetched successfully',
                'status' => 200,
                'data' =>[
                    'id'=> $adjustor_meeting_photos->id,
                    'adjustor_meeting_id' => $adjustor_meeting_photos->adjustor_meeting_id,
                    'exterior_front' => $adjustor_meeting_photos->exteriorPhotos_front,
                    'exterior_front_left' =>$adjustor_meeting_photos->exteriorPhotos_front_left,
                    'exterior_left'=>$adjustor_meeting_photos->exteriorPhotos_left,
                    'exterior_back_left'=>$adjustor_meeting_photos->exteriorPhotos_back_left,
                    'exterior_back'=>$adjustor_meeting_photos->exteriorPhotos_back,
                    'exterior_back_right'=>$adjustor_meeting_photos->exteriorPhotos_back_right,
                    'exterior_right'=>$adjustor_meeting_photos->exteriorPhotos_right,
                    'exterior_front_right' =>$adjustor_meeting_photos->exteriorPhotos_front_right,
                    'created_at'=> $adjustor_meeting_photos->created_at,
                    'updated_at' => $adjustor_meeting_photos->updated_at,

                ],
            ]);
        }
        return response()->json([
            'message' => 'Not Found',
            'status' => 200,
            'data' => [],
        ]);
    }
    
    public function AdjustorMeetingStatus(Request $request, $jobId)
    {
        $rules = [
            'sent' => 'nullable',
        ];
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
                'sent' => $request->sent
            ]);

            if(isset($request->sent) && $request->sent== 'true' && $adjustor_meeting->status === 'approved') {
                $job->status_id = 8;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();  
                
                   //current stage save
                $adjustor_meeting->current_stage="yes";
                $adjustor_meeting->save();
            }
            elseif(isset($request->sent) && $request->sent== 'true' && $adjustor_meeting->status === 'overturn') {

                $job->status_id = 6;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();  
                
                    //current stage save
                $adjustor_meeting->current_stage="yes";
                $adjustor_meeting->save();

            }
            elseif(isset($request->sent) && $request->sent== 'true' && $adjustor_meeting->status === 'apprisal') {

                $job->status_id = 7;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();  
                
                    //current stage save
                $adjustor_meeting->current_stage="yes";
                $adjustor_meeting->save();

            }
            elseif(isset($request->sent) && $request->sent== 'true' && $adjustor_meeting->status === 'denied') {

                $job->status_id = 18;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();  
                
                    //current stage save
                $adjustor_meeting->current_stage="yes";
                $adjustor_meeting->save();

            }
            elseif(isset($request->sent) && $request->sent== 'true' && $adjustor_meeting->status === 'pending_insurance') {

                $job->status_id = 5;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();  
                
                    //current stage save
                $adjustor_meeting->current_stage="yes";
                $adjustor_meeting->save();

            }
            elseif(isset($request->sent) && $request->sent == "false"){
                $job->status_id = 4;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save(); 

                   //current stage save
                $adjustor_meeting->current_stage="no";
                $adjustor_meeting->save();
            }

            return response()->json([
                'status' => 200,
                'message' => 'Adjustor Meeting Status Updated Successfully',
                'data' => $adjustor_meeting,
            ], 200); 

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }


    public function AdjustorMeetingSquarePhotos($id, Request $request)
    {
        $request->validate([
            'square_photos' => 'nullable|array', 
            'square_photos.*' => 'nullable|image', 
            'labels' => 'nullable|array',         
            'labels.*' => 'nullable|string',      
        ]);
        try {
            $savedPhotos = []; 
            $squarePhotos = $request->square_photos ?? [];

            foreach($squarePhotos as $index => $image) {
                $image_fileName = time() . '_' . $image->getClientOriginalName();
                // $image_filePath = $image->storeAs('public/AdjustorSquarePhotos', $image_fileName);
                $image_filePath = $image->storeAs('AdjustorSquarePhotos', $image_fileName, 'public');
                // Store Path   
                $media = new AdjustorSquarePhotos();
                $media->adjustor_meeting_id = $id;
                $media->label = $request->labels[$index] ?? null;
                // $media->square_photos = Storage::url($image_filePath);
                $media->square_photos = Storage::url($image_filePath);
                $media->save();
                 // Collect saved photo details
                $savedPhotos[] = [
                    'id' => $media->id,
                    'adjustor_meeting_id' => $media->adjustor_meeting_id,
                    'label' => $media->label,
                    'square_photos' => $media->square_photos,
                    'created_at' => $media->created_at,
                    'updated_at' => $media->updated_at,
                ];
            }
            // $uploadedPhotos = [];

            // if ($request->hasFile('square_photos')) {
            //     foreach ($request->file('square_photos') as $key => $image) {
            //         $imageName = time() . '_' . $key . '.' . $image->getClientOriginalExtension();
            //         $imagePath = $image->storeAs('public/AdjustorSquarePhotos', $imageName);

            //         // Create a new record for each image
            //         $photos = new AdjustorSquarePhotos();
            //         $photos->adjustor_meeting_id = $id;
            //         $photos->square_photos = $imagePath;
            //         $photos->label = $request->labels[$key] ?? null; // Assign corresponding label or null
            //         $photos->save();

            //         $uploadedPhotos[] = $photos; // Collect each saved photo record
            //     }
            // }

            return response()->json([
                'status' => 200,
                'message' => 'Adjustor Square Photos Added Successfully',
                'data' => $savedPhotos,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'An issue occurred: ' . $e->getMessage(),
                'data' => [],
            ]);
        }
    }


    public function getAdjustorMeetingSquarePhotos($Id,Request $request)
    { 
        // adjustor_meeting_id
        $photos = AdjustorSquarePhotos::where('adjustor_meeting_id',$Id)->get();

        if($photos){
            return response()->json([
                'status' => 200,
                'message' => 'Adjustor Sqaure Photos Fetched Successfully',
                'date' => $photos
            ]);
        }

        return response()->json([
            'status' => 500,
            'message' => 'Not Found',
            'date' => []
        ]);

    }

    public function CompleteAdjustorMeetingSquarePhotos($Id, Request $request)
    {
        // dd($Id);
        $validator = $request->validate([
            'status'=> 'nullable|in:yes,no'
        ]);

        $job = CompanyJob::find($Id);
        if(!$job)
        {
            return response()->json([
                        'status' =>404,
                        'message' =>'Job Not Found',
                        'data'=>[]
                    ]);
        }

        $adjustor = AdjustorMeeting::where('company_job_id',$Id)->first();
        // dd($job);complete/adjustor-meeting-photos
        if($adjustor)
        {
            if($request->input('status') == "yes"){
                $job->status_id = 5;
                $job->save();

                // if ($adjustor->isComplete()) {
                //     $adjustor->is_complete = "yes";
                // } else {
                //     $adjustor->is_complete = "no";
                // }
                // $adjustor->save();
                // $adjustor->is_completed = $adjustor->isComplete() ? "yes" : "no";
                $adjustor->is_complete = "yes";
                $adjustor->save();

                return response()->json([
                    'status' =>200,
                    'message' =>'Adjustor Meeting Marked as Completed',
                    'data' => $adjustor
                ]);

            } elseif($request->input('status') == "no"){
            
                $job->status_id = 4;
                $job->save();

                $adjustor->is_complete = "no";
                $adjustor->save();

                return response()->json([
                    'status' =>200,
                    'message' =>'Adjustor Meeting Status Updated SuccessFully',
                    'data' => $adjustor
                ]);
            }
        }
        return response()->json([
            'status' =>200,
            'message' =>'Job Not Found',
            'data' => []
        ]);
           
    }

    public function getCompleteAdjustorMeetingSquarePhotos($Id)
    {
        $job = CompanyJob::find($Id);
        if(!$job)
        {
            return response()->json([
                        'status' => 404,
                        'message' => 'Job Not Found',
                        'data'=> []
            ]);
        }

        $adjustor = AdjustorMeeting::where('company_job_id',$Id)->first();
        // dd($job);complete/adjustor-meeting-photos
        if($adjustor)
        {
            if ($adjustor->isComplete()) {
                $adjustor->is_complete = "yes";
            } else {
                $adjustor->is_complete = "no";
            }
            $adjustor->save();
            $adjustor->is_completed = $adjustor->isComplete() ? "yes" : "no";
               
            return response()->json([
                'status' =>200,
                'message' =>'Adjustor Meeting Marked as Completed',
                'data' => $adjustor
            ]);

        }
        return response()->json([
            'status' =>200,
            'message' =>'Adjustor Not Found',
            'data' => []
        ]);
           
    }

    public function updateAdjustorMeetingStatus(Request $request, $jobId)
    {
        //Validate Rules
        $rules = [
            'status' => 'nullable|in:approved,overturn,appraisal,denied,pending_insurance',
        ];
        try {
            //Check Job
            $job = CompanyJob::find($jobId);
            // dd($job);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            $status = $request->input('status');

            //Create Adjustor Meeting
            $adjustor_meeting = AdjustorMeeting::updateOrCreate([
                'company_job_id' => $jobId,
            ],[
                'company_job_id' => $jobId,
                'status' => $status
            ]);

            if($request->status=== 'approved' && $adjustor_meeting->sent === 'true') {
                $job->status_id = 6;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();   
            }elseif($request->status=== 'overturn' && $adjustor_meeting->sent === 'true') {
                $job->status_id = 5;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();   
            }elseif($request->status=== 'appraisal' || $adjustor_meeting->sent === 'true') {
                $job->status_id = 18;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();   
            }elseif($request->status=== 'denied' || $adjustor_meeting->sent === 'true') {
                $job->status_id = 16;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();   
            }elseif($request->status=== 'pending_insurance' || $adjustor_meeting->sent === 'true') {
                $job->status_id = 17;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();   
            }

            return response()->json([
                'status' => 200,
                'message' => 'Adjustor Meeting Status Updated Successfully',
                'data' => $adjustor_meeting,
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
            if(isset($request->images) && count($request->images) > 0) {
                // Remove old attachments
                $oldImages = AdjustorMeetingMedia::where('adjustor_id', $adjustor_meeting->id)->where('media_type', 'image')->get();
                foreach ($oldImages as $oldImage) {
                    $oldImagePath = str_replace('/storage', 'public', $oldImage->url);
                    Storage::delete($oldImagePath);
                    $oldImage->delete();
                }

                //Store New Images
                foreach($request->images as $image) {
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

    public function updateAdjustorMeetingStatus11(Request $request, $id)
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
                $adjustor_meeting->is_completed = $adjustor_meeting->isComplete();

                $data = $adjustor_meeting->toArray(); // Convert the model to an array
                // Rename the keys
                $data['image_url'] = $data['images'];
                unset($data['images']); // Remove the old key
            
                $data['documents'] = $data['attachments'];
                unset($data['attachments']); // Remove the old key
            
                //return completed if the required fields are fill

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
