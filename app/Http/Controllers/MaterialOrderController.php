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
            'date_needed' => 'required|date',
            'square_count' => 'required',
            'total_perimeter' => 'required',
            'ridge_lf' => 'required',
            'build_date' => 'required|date',
            'valley_sf' => 'required',
            'hip_and_ridge_lf' => 'required',
            'drip_edge_lf' => 'required',
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
            $material_order = new MaterialOrder;
            $material_order->company_job_id = $id;
            $material_order->street = $request->street;
            $material_order->city = $request->city;
            $material_order->state = $request->state;
            $material_order->zip_code = $request->zip_code;
            $material_order->insurance = $request->insurance;
            $material_order->claim_number = $request->claim_number;
            $material_order->policy_number = $request->policy_number;
            $material_order->save();

            //Store Delivery Information
            $delivery_information = new MaterialOrderDeliveryInformation;
            $delivery_information->material_order_id = $material_order->id;
            $delivery_information->date_needed = $request->date_needed;
            $delivery_information->square_count = $request->square_count;
            $delivery_information->total_perimeter = $request->total_perimeter;
            $delivery_information->ridge_lf = $request->ridge_lf;
            $delivery_information->build_date = $request->date_build_dateneeded;
            $delivery_information->valley_sf = $request->valley_sf;
            $delivery_information->hip_and_ridge_lf = $request->hip_and_ridge_lf;
            $delivery_information->drip_edge_lf = $request->drip_edge_lf;
            $delivery_information->save();

            //Store Materials
            if(isset($request->materials) && count($request->materials) > 0) {
                foreach($request->materials as $material) {
                    $add_material = new MaterialOrderMaterial;
                    $add_material->material_order_id = $material_order->id;
                    $add_material->material = $material['material'];
                    $add_material->quantity = $material['quantity'];
                    $add_material->color = $material['color'];
                    $add_material->order_key = $material['order_key'];
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
}
