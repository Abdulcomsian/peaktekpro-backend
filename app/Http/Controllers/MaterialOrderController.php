<?php

namespace App\Http\Controllers;

use App\Models\CompanyJob;
use Illuminate\Http\Request;
use App\Models\MaterialOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\MaterialOrderMaterial;
use Illuminate\Support\Facades\Storage;
use App\Models\MaterialOrderDeliveryInformation;

class MaterialOrderController extends Controller
{
    public function materialOrder(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'street' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip_code' => 'required',
            'insurance' => 'required',
            'claim_number' => 'required',
            'policy_number' => 'required',
            'date_needed' => 'required|date_format:d/m/Y',
            'square_count' => 'required',
            'total_perimeter' => 'required',
            'ridge_lf' => 'required',
            'build_date' => 'required|date_format:d/m/Y',
            'valley_sf' => 'required',
            'hip_and_ridge_lf' => 'required',
            'drip_edge_lf' => 'required',
            'materials' => 'required|array',
            'materials.*.material' => 'required|string',
            'materials.*.quantity' => 'nullable',
            'materials.*.color' => 'nullable',
            'materials.*.order_key' => 'nullable',
        ]);

        DB::beginTransaction();
        try {

            //Check Job
            $job = CompanyJob::find($id);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job not found'
                ], 422);
            }

            //Store Material Order
            $material_order = MaterialOrder::updateOrCreate([
                'company_job_id' => $id,
            ],[
                'company_job_id' => $id,
                'street' => $request->street,
                'city' => $request->city,
                'state' => $request->state,
                'zip_code' => $request->zip_code,
                'insurance' => $request->insurance,
                'claim_number' => $request->claim_number,
                'policy_number' => $request->policy_number,
            ]);

            //Store Delivery Information
            $delivery_information = MaterialOrderDeliveryInformation::updateOrCreate([
                'material_order_id' => $material_order->id,
            ],[
                'material_order_id' => $material_order->id,
                'date_needed' => $request->date_needed,
                'square_count' => $request->square_count,
                'total_perimeter' => $request->total_perimeter,
                'ridge_lf' => $request->ridge_lf,
                'build_date' => $request->build_date,
                'valley_sf' => $request->valley_sf,
                'hip_and_ridge_lf' => $request->hip_and_ridge_lf,
                'drip_edge_lf' => $request->drip_edge_lf,
            ]);

            // Delete materials that are not in the incoming data
            $existingMaterials = MaterialOrderMaterial::where('material_order_id', $material_order->id)->get();
            $incomingMaterials = $request->materials;
            $incomingMaterialIds = array_column($incomingMaterials, 'id');

            foreach ($existingMaterials as $existingMaterial) {
                if (!in_array($existingMaterial->id, $incomingMaterialIds)) {
                    $existingMaterial->delete();
                }
            }

            foreach($request->materials as $material) {
                if(isset($material['id'])) {
                    // Update existing material
                    $get_material = MaterialOrderMaterial::find($material['id']);
                    if ($get_material) {
                        $get_material->material = $material['material'];
                        $get_material->quantity = isset($material['quantity']) ? $material['quantity'] : null;
                        $get_material->color = isset($material['color']) ? $material['color'] : null;
                        $get_material->order_key = isset($material['order_key']) ? $material['order_key'] : null;
                        $get_material->save();
                    }
                } else {
                    //Store New Material
                    $add_material = new MaterialOrderMaterial;
                    $add_material->material_order_id = $material_order->id;
                    $add_material->material = $material['material'];
                    $add_material->quantity = isset($material['quantity']) ? $material['quantity'] : null;
                    $add_material->color = isset($material['color']) ? $material['color'] : null;
                    $add_material->order_key = isset($material['order_key']) ? $material['order_key'] : null;
                    $add_material->save();
                }
            }

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => 'Material Order Created Successfully',
                'material_order' => $material_order
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getMaterialOrder($id)
    {
        try {

            //Check Material Order
            $material_order = MaterialOrder::where('id', $id)->with('deliveryInformation','materials')->first();
            if(!$material_order) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Material Order Not Found'
                ], 422);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Material Order Found successfully',
                'material_order' => $material_order
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function updateMaterialOrder(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'sign_image' => 'required',
        ]);

        try {

            //Check Material Order
            $material_order = MaterialOrder::find($id);
            if(!$material_order) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Material Order Not Found'
                ], 422);
            }

            // Get base64 image data
            $base64Image = $request->input('sign_image');
            $data = substr($base64Image, strpos($base64Image, ',') + 1);
            $decodedImage = base64_decode($data);

            // Generate a unique filename
            $filename = 'image_' . time() . '.png';
            // Check if the old image exists and delete it
            if ($material_order->sign_image_url) {
                $oldImagePath = public_path($material_order->sign_image_url);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            //Save the new image
            Storage::disk('public')->put('material_order_signature/' . $filename, $decodedImage);
            $imageUrl = '/storage/material_order_signature/' . $filename;

            $material_order->sign_image_url = $imageUrl;
            $material_order->save();

            //Update Job Status
            // $job = CompanyJob::find($material_order->company_job_id);
            // $job->status_id = 2;
            // $job->save();

            return response()->json([
                'status' => 200,
                'message' => 'Signature Image Added Successfully',
                'material_order' => $material_order
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }

    }

    public function checkMaterialOrder($jobId)
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

            //Check Agreement
            $material_order = MaterialOrder::where('company_job_id', $jobId)->with('deliveryInformation','materials')->first();
            if(!$material_order) {

                //Job Information
                $job_info = new \stdClass();
                $job_info->name = $job->name;
                $job_info->email = $job->email;
                $job_info->phone = $job->phone;
                
                return response()->json([
                    'status' => 200,
                    'message' => 'Material Order Not Found',
                    'agreement' => $job_info
                ], 200);
            }

            //Get Job
            $material_order->name = $job->name;
            $material_order->email = $job->email;
            $material_order->phone = $job->phone;

            return response()->json([
                'status' => 200,
                'message' => 'Material Order Found Successfully',
                'data' => $material_order
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
