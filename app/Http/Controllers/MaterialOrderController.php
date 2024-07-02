<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\CompanyJob;
use App\Models\MaterialOrder;

class MaterialOrderController extends Controller
{
    public function materialOrder(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'street' => 'required',
                'city' => 'required',
                'state' => 'required',
                'zip_code' => 'required',
                'insurance' => 'required',
                'claim_number' => 'required',
                'policy_number' => 'required',
            ]);

            $job = CompanyJob::find($id);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job not found'
                ], 422);
            }

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

            return response()->json([
                'status' => 200,
                'message' => 'Material Order Created Successfully',
                'material_order' => $material_order
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getMaterialOrder($id)
    {
        try {
            $material_order = MaterialOrder::find($id);
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
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateMaterialOrder(Request $request, $id)
    {
        $this->validate($request, [
            'sign_image' => 'required',
        ]);

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
        Storage::disk('public')->put('material_order_signature/' . $filename, $decodedImage);
        $imageUrl = '/storage/material_order_signature/' . $filename;

        $material_order->sign_image_url = $imageUrl;
        $material_order->save();

        //Update Job Status
        $job = CompanyJob::find($material_order->company_job_id);
        $job->status_id = 2;
        $job->save();

        return response()->json([
            'status' => 200,
            'message' => 'Signature Image Added Successfully',
            'material_order' => $material_order
        ], 200);

    }
}
