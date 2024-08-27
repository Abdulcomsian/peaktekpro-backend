<?php

namespace App\Http\Controllers;

use App\Models\CompanyJob;
use App\Models\CarrierScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CarrierScopeController extends Controller
{
    public function storeCarrierScope(Request $request, $jobId)
    {
        //Validate Request
        $validatedData = $request->validate([
            'images' => 'required|array',
            'images.*' => 'required|image|max:10240|mimes:png,jpg,jpeg,gif',
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

            //Store Carrier Scope
            if(isset($request->images) && count($request->images) > 0) {

                //Store New Images
                foreach($request->file('images') as $image) {
                    $image_fileName = time() . '_' . $image->getClientOriginalName();
                    $image_filePath = $image->storeAs('public/carrier_scope', $image_fileName);

                    // Store Path
                    $carrier_scope = new carrierScope();
                    $carrier_scope->company_job_id = $jobId;
                    $carrier_scope->image_url = Storage::url($image_filePath);
                    $carrier_scope->save();
                }
            }

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => 'Carrier Scope Created Successfully',
                'data' => []
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getCarrierScope($jobId)
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

            $get_carrier_scope = CarrierScope::where('company_job_id', $jobId)->get();
            if(count($get_carrier_scope) == 0) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Carrier Scope Not Found'
                ], 422);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Carrier Scope Found Successfully',
                'data' => $get_carrier_scope
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function changeCarrierScopeFileName(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'file_name' => 'required|string'
        ]);

        try {

            //Check Carrier Scope
            $check_carrier_scope = CarrierScope::find($id);
            if(!$check_carrier_scope) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Carrier Scope Not Found'
                ], 422);
            }

            //Update File Name
            $check_carrier_scope->file_name = $request->file_name;
            $check_carrier_scope->save();

            return response()->json([
                'status' => 200,
                'message' => 'File Name Updated Successfully',
                'data' => $check_carrier_scope
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function deleteCarrierScopeMedia(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'image_url' => 'required|string'
        ]);

        try {

            //Check Media
            $check_media = CarrierScope::find($id);
            if(!$check_media) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Carrier Scope Media Not Found'
                ], 422);
            }

            // Remove old images
            $oldImage = CarrierScope::find($id);
            $oldImagePath = str_replace('/storage', 'public', $oldImage->image_url);
            Storage::delete($oldImagePath);
            $oldImage->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Media Deleted Successfully',
                'data' => $check_media
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
