<?php

namespace App\Http\Controllers;

use App\Models\CompanyJob;
use App\Models\QcInspection;
use Illuminate\Http\Request;
use App\Models\QcInspectionMedia;
use App\Models\QcInspectionMaterials;
use Illuminate\Support\Facades\Storage;

class QcInspectionController extends Controller
{
    public function storeQcInspection(Request $request, $jobId)
    {

        //Validate Rules
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|max:25',
            'street' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip_code' => 'required',
            'insurance' => 'required',
            'claim_number' => 'required',
            'policy_number' => 'required',
            'company_signature' => 'required',
            'company_printed_name' => 'required',
            'company_date' => 'required|date_format:d/m/Y',
            'customer_signature' => 'required',
            'customer_printed_name' => 'required',
            'customer_date' => 'required|date_format:d/m/Y',
            'materials' => 'required|array',
            'materials.*.material' => 'required|string',
            'materials.*.damaged' => 'nullable|boolean',
            'materials.*.notes' => 'nullable|string',
        ];

        // If updating an existing record, ignore the current record's email for uniqueness check
        $check_qc_inspection = QcInspection::where('company_job_id', $jobId)->first();
        if($check_qc_inspection) {
            $rules['email'] .= '|unique:qc_inspections,email,' . $check_qc_inspection->id;
        } else {
            $rules['email'] .= '|unique:qc_inspections,email';
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

            //Update QC Inspection
            $qc_inspection = QcInspection::updateOrCreate([
                'company_job_id' => $jobId,
            ],[
                'company_job_id' => $jobId,
                'email' => $request->email,
                'name' => $request->name,
                'phone' => $request->phone,
                'street' => $request->street,
                'city' => $request->city,
                'state' => $request->state,
                'zip_code' => $request->zip_code,
                'insurance' => $request->insurance,
                'claim_number' => $request->claim_number,
                'policy_number' => $request->policy_number,
                'company_representative' => $request->company_signature,
                'company_printed_name' => $request->company_printed_name,
                'company_signed_date' => $request->company_date,
                'customer_signature' => $request->customer_signature,
                'customer_printed_name' => $request->customer_printed_name,
                'customer_signed_date' => $request->customer_date,
            ]);

            // Delete materials that are not in the incoming data
            $existingMaterials = QcInspectionMaterials::where('qc_inspection_id', $qc_inspection->id)->get();
            $incomingMaterials = $request->materials;
            $incomingMaterialIds = array_column($incomingMaterials, 'id');

            foreach ($existingMaterials as $existingMaterial) {
                if (!in_array($existingMaterial->id, $incomingMaterialIds)) {
                    $existingMaterial->delete();
                }
            }

            //Store Materials
            foreach($request->materials as $material)
            {
                if(isset($material['id'])) {
                    // Update existing material
                    $update_material = QcInspectionMaterials::find($material['id']);
                    if($update_material) {
                        $update_material->qc_inspection_id = $qc_inspection->id;
                        $update_material->material = $material['material'];
                        $update_material->damaged = isset($material['damaged']) ? $material['damaged'] : false;
                        $update_material->notes = (isset($material['notes'])) ? $material['notes'] : null;
                        $update_material->save();
                    }
                } else {
                    //Create New Material
                    $add_material = new QcInspectionMaterials;
                    $add_material->qc_inspection_id = $qc_inspection->id;
                    $add_material->material = $material['material'];
                    $add_material->damaged = isset($material['damaged']) ? $material['damaged'] : false;
                    $add_material->notes = isset($material['notes']) ? $material['notes'] : null;
                    $add_material->save();
                }
            }


            return response()->json([
                'status' => 200,
                'message' => 'QC Inspection Added Successfully',
                'data' => $qc_inspection
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function storeQcInspectionMedia(Request $request, $jobId)
    {
        $this->validate($request, [
            'notes' => 'required|string',
            'images' => 'nullable|array',
            'images.*' => 'image|max:10240|mimes:png,jpg,jpeg,gif',
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

            //Check QC Inspection
            $qc_inspection = QcInspection::where('company_job_id', $jobId)->first();
            if(!$qc_inspection) {
                return response()->json([
                    'status' => 422,
                    'message' => 'QC Inspection Not Found'
                ], 422);
            }

            //Update QC Inspection
            $qc_inspection = QcInspection::updateOrCreate([
                'company_job_id' => $jobId,
            ],[
                'company_job_id' => $jobId,
                'notes' => $request->notes,
            ]);

            //Store QC Inspections Images
            if(isset($request->images) && count($request->images) > 0) {
                // Remove old images
                $oldImages = QcInspectionMedia::where('qc_inspection_id', $qc_inspection->id)->get();
                foreach ($oldImages as $oldImage) {
                    if(!is_null($oldImage)) {
                        $oldImagePath = str_replace('/storage', 'public', $oldImage->image_url);
                        Storage::delete($oldImagePath);
                        $oldImage->delete();
                    }
                }

                //Store New Images
                foreach($request->file('images') as $image) {
                    $image_fileName = time() . '_' . $image->getClientOriginalName();
                    $image_filePath = $image->storeAs('public/qc_inspection_images', $image_fileName);

                    // Store Path
                    $qc_inspection_media = new QcInspectionMedia();
                    $qc_inspection_media->qc_inspection_id = $qc_inspection->id;
                    $qc_inspection_media->image_url = Storage::url($image_filePath);
                    $qc_inspection_media->save();
                }
            }

            return response()->json([
                'status' => 200,
                'message' => 'QC Inspection Media Added Successfully',
                'data' => $qc_inspection
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getQcInspection($jobId)
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

            $qc_inspection = QcInspection::where('company_job_id', $jobId)->with('materials','images')->first();

            return response()->json([
                'status' => 200,
                'message' => 'QC Inspection Found Successfully',
                'data' => $qc_inspection
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
