<?php

namespace App\Http\Controllers;

use App\Models\CompanyJob;
use Illuminate\Http\Request;
use App\Models\ProjectDesignTitle;
use App\Models\ProjectDesignInspection;
use App\Models\ProjectDesignPageStatus;
use Illuminate\Support\Facades\Storage;
use App\Models\ProjectDesignIntroduction;
use App\Models\ProjectDesignInspectionMedia;

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
            'date' => 'required|date',
            'primary_image' => 'nullable|image',
            'secondary_image' => 'nullable|image',
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
            if (isset($request->primary_image) && $request->hasFile('primary_image')) {
                // Delete the old primary image if it exists
                if ($title->primary_image) {
                    $oldPrimaryImagePath = str_replace('/storage', 'public', $title->primary_image);
                    Storage::delete($oldPrimaryImagePath);
                }

                $primary_image = $request->file('primary_image');
                $primary_image_filename = $primary_image->getClientOriginalName();
                $primary_fileName = time() . '_' . $primary_image_filename;
                $primary_image_path = $primary_image->storeAs('public/project_design_title', $primary_fileName);

                // Save the image path in the database
                $title->primary_image = Storage::url($primary_image_path);
                $title->save();
            }

            // Store Secondary Image if provided
            if (isset($request->secondary_image) && $request->hasFile('secondary_image')) {
                // Delete the old secondary image if it exists
                if ($title->secondary_image) {
                    $oldSecondaryImagePath = str_replace('/storage', 'public', $title->secondary_image);
                    Storage::delete($oldSecondaryImagePath);
                }

                $secondary_image = $request->file('secondary_image');
                $secondary_image_filename = $secondary_image->getClientOriginalName();
                $secondary_fileName = time() . '_' . $secondary_image_filename;
                $secondary_image_path = $secondary_image->storeAs('public/project_design_title', $secondary_fileName);

                // Save the image path in the database
                $title->secondary_image = Storage::url($secondary_image_path);
                $title->save();
            }

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
            //Check Job
            $job = CompanyJob::find($jobId);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            $get_title = ProjectDesignTitle::where('company_job_id', $jobId)->first();

            return response()->json([
                'status' => 200,
                'message' => 'Project Design Title Found Successfully',
                'data' => $get_title
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
            'inspection' => 'required',
            'attachments' => 'nullable|array'
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

            //Store Project Design Inspection
            $inspection = ProjectDesignInspection::updateOrCreate([
                'company_job_id' => $jobId,
            ],[
                'company_job_id' => $jobId,
                'inspection' => $request->inspection
            ]);

            // Handle attachments
            if (isset($request->attachments) && $request->hasFile('attachments')) {
                // Remove old attachments
                $oldAttachments = ProjectDesignInspectionMedia::where('inspection_id', $inspection->id)->get();
                foreach ($oldAttachments as $oldAttachment) {
                    $oldFilePath = str_replace('/storage', 'public', $oldAttachment->url);
                    Storage::delete($oldFilePath);
                    $oldAttachment->delete();
                }

                // Store new attachments
                foreach ($request->file('attachments') as $file) {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs('public/project_design_inspection', $fileName);

                    // Store Path
                    $media = new ProjectDesignInspectionMedia();
                    $media->inspection_id = $inspection->id;
                    $media->url = Storage::url($filePath);
                    $media->save();
                }
            }

            return response()->json([
                'status' => 200,
                'message' => 'Inspection Added Successfully',
                'data' => $inspection
            ], 200);
        } catch (\Exception $e) {
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

            $get_inspection = ProjectDesignInspection::where('company_job_id', $jobId)->with('attachments')->first();

            return response()->json([
                'status' => 200,
                'message' => 'Project Design Inspection Found Successfully',
                'data' => $get_inspection
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
