<?php

namespace App\Http\Controllers;

use App\Models\CompanyJob;
use Illuminate\Http\Request;
use App\Models\ProjectDesignTitle;
use Illuminate\Support\Facades\DB;
use App\Models\ProjectDesignInspection;
use App\Models\ProjectDesignPageStatus;
use Illuminate\Support\Facades\Storage;
use App\Models\ProjectDesignIntroduction;
use App\Models\ProjectDesignInspectionMedia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProjectDesignController extends Controller
{
    public function updateProjectDesignPageStatus(Request $request, $jobId)
    {
        //Validate Request
        $this->validate($request, [
            'project_design_page_id' => 'required|integer',
            'status' => 'required|in:0,1'
        ]);

        try {
            //Check Job
            $job = CompanyJob::find($jobId);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            //Update Status
            ProjectDesignPageStatus::updateOrCreate([
                'project_design_page_id' => $request->project_design_page_id,
                'company_job_id' => $jobId,
            ],[
                'project_design_page_id' => $request->project_design_page_id,
                'company_job_id' => $jobId,
                'status' => $request->status
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Status Updated Successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function storeProjectDesignTitle(Request $request ,$jobId)
    {
        //Validate Request
        $this->validate($request, [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'company_name' => 'required|string',
            'address' => 'required',
            'city' => 'required|string',
            'state' => 'required|string',
            'zip' => 'required',
            'report_type' => 'required',
            'date' => 'required|date_format:d/m/Y',
            'primary_image' => 'nullable',
            'secondary_image' => 'nullable',
        ]);

        try {
            //Check Job
            $job = CompanyJob::find($jobId);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            //Store Project Design Title
            $title = ProjectDesignTitle::updateOrCreate([
                'company_job_id' => $jobId
            ],[
                'company_job_id' => $jobId,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'company_name' => $request->company_name,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'zip' => $request->zip,
                'report_type' => $request->report_type,
                'date' => $request->date,
            ]);

            // Store Primary Image if provided
            if (isset($request->primary_image) && $request->hasFile('primary_image') && $request->primary_image != "null") {
                // Delete the old primary image if it exists
                if ($title->primary_image) {
                    $oldPrimaryImagePath = str_replace('/storage', 'public', $title->primary_image);
                    Storage::delete($oldPrimaryImagePath);
                }

                $primary_image = $request->file('primary_image');
                $primary_image_filename = $primary_image->getClientOriginalName();
                $primary_fileName = time() . '_' . $primary_image_filename;
                $primary_image_path = $primary_image->storeAs('public/pd_title', $primary_fileName);

                // Save the image path in the database
                $title->primary_image = Storage::url($primary_image_path);
                // $title->primary_image_file_name = $primary_image_filename;
                $title->save();
            }

            // Store Secondary Image if provided
            if (isset($request->secondary_image) && $request->hasFile('secondary_image') && $request->secondary_image != "null") {
                // Delete the old secondary image if it exists
                if ($title->secondary_image) {
                    $oldSecondaryImagePath = str_replace('/storage', 'public', $title->secondary_image);
                    Storage::delete($oldSecondaryImagePath);
                }

                $secondary_image = $request->file('secondary_image');
                $secondary_image_filename = $secondary_image->getClientOriginalName();
                $secondary_fileName = time() . '_' . $secondary_image_filename;
                $secondary_image_path = $secondary_image->storeAs('public/pd_title', $secondary_fileName);

                // Save the image path in the database
                $title->secondary_image = Storage::url($secondary_image_path);
                // $title->secondary_image_file_name = $secondary_image_filename;
                $title->save();
            }

            $title = ProjectDesignTitle::find($title->id);

            return response()->json([
                'status' => 200,
                'message' => 'Title Added Successfully',
                'data' => $title
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getProjectDesignTitle($jobId)
    {
        try {
            $primary_image = [];
            $secondary_image = [];
            //Check Job
            $job = CompanyJob::find($jobId);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            $get_title = ProjectDesignTitle::where('company_job_id', $jobId)->first();
            if(!$get_title) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Project Design Title Not Yet Created',
                ], 404);
            }

            //Creating Array for Primary Image
            if(!is_null($get_title->primary_image)) {
                $primary_object = new \stdClass();
                $primary_object->id = $get_title->id;
                $primary_object->image_url = $get_title->primary_image;
                $primary_object->type = 'primary_image_file_name';
                $primary_object->file_name = $get_title->primary_image_file_name;

                $primary_image[] = $primary_object;

                $get_title->primary_images = $primary_image;
            }

            //Creating Array for Secondary Image
            if(!is_null($get_title->secondary_image)) {
                $secondary_object = new \stdClass();
                $secondary_object->id = $get_title->id;
                $secondary_object->image_url = $get_title->secondary_image;
                $secondary_object->type = 'secondary_image_file_name';
                $secondary_object->file_name = $get_title->secondary_image_file_name;

                $secondary_image[] = $secondary_object;

                $get_title->secondary_images = $secondary_image;
            }

            return response()->json([
                'status' => 200,
                'message' => 'Project Design Title Found Successfully',
                'data' => $get_title
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function changeProjectDesignTitleFileName(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'file_name' => 'required|string',
            'type' => 'required|string|in:primary_image_file_name,secondary_image_file_name'
        ]);

        try {

            //Check PD Title
            $check_pd_title = ProjectDesignTitle::find($id);
            if(!$check_pd_title) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Project Design Title Not Found'
                ], 422);
            }

            //Update File Name
            $check_pd_title->{$request->type} = $request->file_name;
            $check_pd_title->save();

            return response()->json([
                'status' => 200,
                'message' => 'File Name Updated Successfully',
                'data' => []
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function deleteProjectDesignTitleMedia(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'image_url' => 'required|string',
            'type' => 'required|string|in:primary_image_file_name,secondary_image_file_name'
        ]);

        try {

            // Check PD Title
            $check_pd_title = ProjectDesignTitle::find($id);
            if (!$check_pd_title) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Project Design Title Not Found'
                ], 422);
            }
            
            // Format the type
            $formatted = str_replace('_file_name', '', $request->type);
            
            // Handle the primary_image and secondary_image cases
            if ($formatted === 'primary_image') {
                $formatted = 'primary_image';
            } elseif ($formatted === 'secondary_image') {
                $formatted = 'secondary_image';
            }
            
            // Delete Media
            $oldImagePath = str_replace('/storage', 'public', $check_pd_title->{$formatted});
            Storage::delete($oldImagePath);
            
            // Update File Name
            $check_pd_title->{$formatted} = null;
            $check_pd_title->{$request->type} = null;
            $check_pd_title->save();

            return response()->json([
                'status' => 200,
                'message' => 'Media Deleted Successfully',
                'data' => []
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function storeProjectDesignIntroduction(Request $request, $jobId)
    {
        //Validate Request
        $this->validate($request, [
            'introduction' => 'required'
        ]);

        try {
            //Check Job
            $job = CompanyJob::find($jobId);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            //Store Project Design Introduction
            $introduction = ProjectDesignIntroduction::updateOrCreate([
                'company_job_id' => $jobId,
            ],[
                'company_job_id' => $jobId,
                'introduction' => $request->introduction
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Introduction Added Successfully',
                'data' => $introduction
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getProjectDesignIntroduction($jobId)
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

            $get_introduction = ProjectDesignIntroduction::where('company_job_id', $jobId)->first();
            if(!$get_introduction) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Project Design Introduction Not Yet Created',
                ], 422);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Project Design Introduction Found Successfully',
                'data' => $get_introduction
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function storeProjectDesignInspection(Request $request, $jobId)
    {
        //Validate Request
        $this->validate($request, [
            'inspectionData' => 'required|array',
            'inspectionData.*.inspection' => 'required',
            'inspectionData.*.attachment' => 'nullable|array',
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

            //Store Project Design Inspection
            $inspections = $request->inspectionData;
            foreach($inspections as $key => $inspection) {
                if(isset($inspection['id'])) {
                    $get_inspection = ProjectDesignInspection::find($inspection['id']);
                    if($get_inspection) {
                        //Update Existing
                        $get_inspection->company_job_id = $jobId;
                        $get_inspection->inspection = $inspection['inspection'];
                        $get_inspection->save();

                        // Handle attachments
                        if (isset($inspection['attachment']) && !is_null($inspection['attachment']) && $inspection['attachment'] != 'null') {
                            // Remove old attachments
                            // $oldAttachments = ProjectDesignInspectionMedia::where('inspection_id', $get_inspection->id)->get();
                            // foreach($oldAttachments as $oldAttachment)
                            // if ($oldAttachment) {
                            //     $oldFilePath = str_replace('/storage', 'public', $oldAttachment->url);
                            //     Storage::delete($oldFilePath);
                            //     $oldAttachment->delete();
                            // }

                            // Store new attachments
                            foreach($inspection['attachment'] as $attachment)
                            {
                                $file = $attachment;
                                $fileName = time() . '_' . $file->getClientOriginalName();
                                $filePath = $file->storeAs('public/project_design_inspection', $fileName);

                                // Store Path
                                $media = new ProjectDesignInspectionMedia;
                                $media->inspection_id = $get_inspection->id;
                                $media->url = Storage::url($filePath);
                                $media->save();
                            }
                        }
                    }
                } else {
                    //Create New Inspection
                    $create_inspection = new ProjectDesignInspection;
                    $create_inspection->company_job_id = $jobId;
                    $create_inspection->inspection = $inspection['inspection'];
                    $create_inspection->save();

                    // Handle attachments
                    if (isset($inspection['attachment']) && count($inspection['attachment']) > 0) {
                        // Remove old attachments
                        // $oldAttachments = ProjectDesignInspectionMedia::where('inspection_id', $create_inspection->id)->get();
                        // foreach($oldAttachments as $oldAttachment)
                        // if ($oldAttachment) {
                        //     $oldFilePath = str_replace('/storage', 'public', $oldAttachment->url);
                        //     Storage::delete($oldFilePath);
                        //     $oldAttachment->delete();
                        // }

                        // Store new attachments
                        foreach($inspection['attachment'] as $attachment)
                        {
                            $file = $attachment;
                            $fileName = time() . '_' . $file->getClientOriginalName();
                            $filePath = $file->storeAs('public/project_design_inspection', $fileName);

                            // Store Path
                            $media = new ProjectDesignInspectionMedia;
                            $media->inspection_id = $create_inspection->id;
                            $media->url = Storage::url($filePath);
                            $media->save();
                        }
                    }
                }
            }

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => 'Inspection Added Successfully',
                'data' => []
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getProjectDesignInspection($jobId)
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

            $get_inspection = ProjectDesignInspection::where('company_job_id', $jobId)->with('attachment')->get();
            if(count($get_inspection) == 0) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Project Design Inspection Not Yet Created',
                ], 422);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Project Design Inspection Found Successfully',
                'data' => $get_inspection
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function deleteProjectDesignInspection($id)
    {
        try {

            //Check Inspection
            $get_inspection = ProjectDesignInspection::find($id);
            if(!$get_inspection) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Inspection Item Not Found',
                    'data' => []
                ], 200);
            }

            // Remove old attachments
            $oldAttachment = ProjectDesignInspectionMedia::where('inspection_id', $id)->first();
            if ($oldAttachment) {
                $oldFilePath = str_replace('/storage', 'public', $oldAttachment->url);
                Storage::delete($oldFilePath);
                $oldAttachment->delete();
            }

            //Remove Inspection
            $get_inspection->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Inspection Item Deleted Successfully',
                'data' => []
            ], 200);


        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
