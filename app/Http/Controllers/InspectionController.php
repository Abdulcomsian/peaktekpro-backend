<?php

namespace App\Http\Controllers;

use App\Models\CompanyJob;
use App\Models\Inspection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InspectionController extends Controller
{
    public function addInspection($jobId, Request $request)
    {
        $request->validate([
            'file_path' => 'nullable|array', 
            'file_path.*' => 'nullable|file', 
            'labels' => 'nullable|array',         
            'labels.*' => 'nullable|string',      
        ]);

        try {
            $adjustor = CompanyJob::find($jobId);
            if (!$adjustor) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Company Job Not Found',
                ]);
            }

            // Step 1: Delete existing images
            $existingPhotos = Inspection::where('company_job_id', $jobId)->get();
            // dd($existingPhotos);
            foreach ($existingPhotos as $photo) {
                // Delete file from storage
                $filePath = str_replace('/storage/', 'public/', $photo->file_path); // Convert storage path to public disk path
                Storage::delete($filePath);
                $photo->delete(); // Delete the record from the database
            }

            // Step 2: Upload new images
            $savedPhotos = []; // To store successfully saved photos
            $squarePhotos = $request->file_path ?? [];
            foreach ($squarePhotos as $index => $image) {
                $image_fileName = time() . '_' . $image->getClientOriginalName();
                $image_filePath = $image->storeAs('InspectionPhotos', $image_fileName, 'public');

                // Save new photo in database
                $media = new Inspection();
                $media->company_job_id = $jobId;
                $media->labels = $request->labels[$index] ?? null;
                $media->file_path = Storage::url($image_filePath);
                $media->save();

                // Collect saved photo details
                $savedPhotos[] = [
                    'id' => $media->id,
                    'adjustor_meeting_id' => $media->company_job_id,
                    'label' => $media->labels,
                    'square_photos' => $media->file_path,
                    'created_at' => $media->created_at,
                    'updated_at' => $media->updated_at,
                ];
            }

            return response()->json([
                'status' => 200,
                'message' => 'Inspection Photos Updated Successfully',
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

    public function getInspection($jobId)
    {
        $job = CompanyJob::find($jobId);
        if (!$job) {
            return response()->json([
                'status' => 404,
                'message' => 'Company Job Not Found',
            ]);
        }

        $data = Inspection::where('company_job_id', $jobId)->get();
        return response()->json([
            'status' => 200,
            'message' => 'Inspection Photos Fetched Successfully',
            'data' => $data,
        ]);
    }

    public function deleteInspection($id)
    {
        $media = Inspection::find($id);
        if (!$media) {
            return response()->json([
                'status' => 404,
                'message' => 'Photo not found',
            ]);
        }
        $imagePath = $media->file_path; 
        $relativePath = str_replace('/storage/', '', $imagePath);

        // Delete the image from storage
        if (Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }

        $media->delete();
        return response()->json([
            'status' => 200,
            'message' => 'Media Deleted Successfully',
        ]);
    }
}
