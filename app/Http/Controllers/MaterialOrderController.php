<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CompanyJob;
use Illuminate\Http\Request;
use App\Models\MaterialOrder;
use App\Models\ReadyToBuild;
use App\Jobs\MaterialOrderJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\MaterialOrderMaterial;
use Illuminate\Support\Facades\Storage;
use App\Models\MaterialOrderDeliveryInformation;
use PDF;
use Carbon\Carbon;
use App\Models\BuildDetail;
use App\Models\MaterialOrderConfirmation;
use App\Jobs\ConfirmationJob;
use App\Jobs\MaterialOrderConfirmationJob;

class MaterialOrderController extends Controller
{
    public function materialOrder(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'supplier_id' => 'required|integer',
            'street' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip_code' => 'required',
            'insurance' => 'required',
            'claim_number' => 'required',
            'policy_number' => 'required',
            'date_needed' => 'required|date_format:m/d/Y',
            'square_count' => 'required',
            'total_perimeter' => 'required',
            'ridge_lf' => 'required',
            'build_date' => 'required|date_format:m/d/Y',
            'valley_sf' => 'required',
            'hip_and_ridge_lf' => 'required',
            'drip_edge_lf' => 'required',
            'status' => 'nullable|in:0,1',
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

            //Check Supplier
            $supplier = User::where('id', $request->supplier_id)->where('role_id', 4)->first();
            if(!$supplier) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Supplier Not Found'
                ], 422);
            }

            //Store Material Order
            $material_order = MaterialOrder::updateOrCreate([
                'company_job_id' => $id,
            ],[
                'company_job_id' => $id,
                'supplier_id' => $request->supplier_id,
                'street' => $request->street,
                'city' => $request->city,
                'state' => $request->state,
                'zip_code' => $request->zip_code,
                'insurance' => $request->insurance,
                'claim_number' => $request->claim_number,
                'policy_number' => $request->policy_number,
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
            
            //Update Status
            if(isset($request->status)) {
                $job->status_id = 10;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();
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
            $material_order = MaterialOrder::where('id', $id)->with('materials','supplier')->first();
            if(!$material_order) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Material Order Not Found'
                ], 422);
            }
            
            $build_detail = BuildDetail::where('company_job_id', $material_order->company_job_id)->first();
            if($build_detail) {
                $material_order->build_detail = $build_detail;
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
            $material_order = MaterialOrder::where('company_job_id', $jobId)->with('materials','supplier')->first();
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

    public function MaterialOrderEmail(Request $request, $jobId)
    {
        //Validate Request
        $this->validate($request, [
            'supplier_id' => 'required|integer',
            'sub_contractor_id' => 'required|integer',
            'material_order_id' => 'required|integer',
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

            //Check Material Order
            $material_order = MaterialOrder::where('id', $request->material_order_id)->first();
            if(!$material_order) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Material Order Not Found'
                ], 422);
            }

            //Check Supplier
            $supplier = User::where('id', $request->supplier_id)->where('role_id', 3)->first();
            if(!$supplier) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Supplier Not Found'
                ], 422);
            }

            //Check Sub Contractor
            $sub_contractor = User::where('id', $request->sub_contractor_id)->where('role_id', 2)->first();
            if(!$sub_contractor) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Sub Contractor Not Found'
                ], 422);
            }

            //Dispatch Email Through Queue
            dispatch(new MaterialOrderJob($supplier,$material_order));
            dispatch(new MaterialOrderJob($sub_contractor,$material_order));

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => 'Email Sent successfully',
                'data' => []
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function EmailToSupplier($jobId)
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

            //Check Material Order
            $material_order = MaterialOrder::where('company_job_id', $jobId)->with('job','materials')->first();
            if(!$material_order) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Material Order Not Found'
                ], 422);
            }

            //Check if Supplier is assigned
            $assigned_supplier = MaterialOrder::whereNotNull('supplier_id')->first();
            if(!$assigned_supplier) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Supplier Not Yet Assigned'
                ], 422);
            }

            //Check Supplier
            $supplier = User::where('id', $material_order->supplier_id)->where('role_id', 4)->first();
            if(!$supplier) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Supplier Not Found'
                ], 422);
            }

            //Generate PDF
            $pdf = PDF::loadView('pdf.material-order', ['data' => $material_order]);
            $pdf_fileName = time() . '.pdf';
            $pdf_filePath = 'material_order_pdf/' . $pdf_fileName;
            // Check if the old PDF exists and delete it
            if ($material_order->sign_pdf_url) {
                $oldPdfPath = public_path($material_order->sign_pdf_url);
                if (file_exists($oldPdfPath)) {
                    unlink($oldPdfPath);
                }
            }
            // Save the new PDF
            Storage::put('public/' . $pdf_filePath, $pdf->output());

            //Save PDF Path
            $material_order->sign_pdf_url = '/storage/' . $pdf_filePath;
            $material_order->save();

            //Dispatch Email Through Queue
            dispatch(new MaterialOrderJob($supplier,$material_order));

            return response()->json([
                'status' => 200,
                'message' => 'Email Sent successfully',
                'data' => []
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    
    public function updateBuildDetail(Request $request, $jobId)
    {
        //Validate Request
        $this->validate($request, [
            'homeowner' => 'nullable|string',
            'homeowner_email' => 'nullable|email',
            'contractor' => 'nullable|string',
            'contractor_email' => 'nullable|email',
            'supplier' => 'nullable|string',
            'supplier_email' => 'nullable|email',
            'build_time' => 'nullable|date_format:h:i A',
            'build_date' => 'nullable|date_format:m/d/Y',
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
            $readyBuild = ReadyToBuild::where('company_job_id', $jobId)->first();

            //Update Build Detail
            $build_detail = BuildDetail::updateOrCreate([
                'company_job_id' => $jobId
            ],[
                'company_job_id' => $jobId,
                'build_date' => $request->build_date,
                'build_time' => $request->build_time,
                'homeowner' => $readyBuild->home_owner ?? '',
                'homeowner_email' => $readyBuild->home_owner_email ?? '',
                'contractor' => $request->contractor,
                'contractor_email' => $request->contractor_email,
                'supplier' => $request->supplier,
                'supplier_email' => $request->supplier_email,
            ]);
            return response()->json([
                'status' => 200,
                'message' => 'Build Detail Updated Successfully',
                'data' => $build_detail
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getBuildDetail($jobId)
    {
        try{
            //Check Job
            $job = CompanyJob::find($jobId);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

             //get Build Detail
             $build_detail = BuildDetail::where('company_job_id',$jobId)->first();
             //get Ready to Build
             $readyBuild = ReadyToBuild::where('company_job_id', $jobId)->first();

             if (!$readyBuild) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Build Details Not Yet Created',
                    'data' => []
                ], 200);
            }
            // Return response with Ready To Build details
            return response()->json([
                'status' => 200,
                'message' => 'Build Details Found Successfully',
                'data' =>
                [
                        'homeowner' => $readyBuild->home_owner ?? '',
                        'homeowner_email' => $readyBuild->home_owner_email ?? '',
                        'id' => $build_detail->id ?? '',
                        'company_job_id' => $build_detail->company_job_id ?? '',
                        'build_date' => $build_detail->build_date ?? '',
                        'build_time' => $build_detail->build_time ?? '',
                        // 'home_owner' => $build_detail->homeowner,
                        // 'home_owner_email' => $build_detail->homeowner_email,
                        'contractor' => $build_detail->contractor ?? '',
                        'contractor_email' => $build_detail->contractor_email ?? '',
                        'supplier' => $build_detail->supplier ?? '',
                        'supplier_email' => $build_detail->supplier_email ?? '',
                        'created_at' => $build_detail->created_at ?? '',
                        'updated_at' => $build_detail->updated_at ?? '',                    
                ]
            ], 200);
            

        }catch(Exception $e){
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);

        }
    }
    
    public function confirmationEmail(Request $request, $jobId)
    {
        //Validate Request
        $this->validate($request, [
            'send_to' => 'required|string',
            'subject' => 'required|string',
            'email_body' => 'required|string',
            'status' => 'nullable|in:true,false',
        ]);
        
        try {
            //Check job exist or not
            $material_order = CompanyJob::where('id', $jobId)->first();
            if(!$material_order) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Company Job Not Created.'
                ], 422);
            }

            //here Seprate the emails
            $sendToEmails = array_map('trim', explode(',', $request->send_to));
            //Send Emails
            foreach($sendToEmails as $email)
            {
                dispatch(new ConfirmationJob($email,$request->subject,$request->email_body));
            }

            //Update Material Order
            MaterialOrderConfirmation::updateOrCreate([
                'company_job_id' => $jobId
            ],[
                'company_job_id' => $jobId,
                'type'=>1,
                'confirmation_email_sent'=>$request->status,
            ]);
            
            return response()->json([
                'status' => 200,
                'message' => 'Email Sent successfully',
                'data' => []
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function materialOrderconfirmationEmail(Request $request, $jobId)
    {
        // Validate Request
        $this->validate($request, [
            'send_to' => 'required|string',
            'subject' => 'required|string',
            'email_body' => 'required',
            'attachments' => 'nullable|array',
            'status' => 'required|in:true,false',
        ]);
        
        try {
            // Convert comma-separated string to array
            $sendToEmails = array_map('trim', explode(',', $request->send_to));

            // Validate each email in the array
            foreach ($sendToEmails as $email) {
                $this->validate($request, [
                    'send_to.*' => 'email', // Validate each email
                ]);
            }

            // Check Material Order
            $material_order = MaterialOrderConfirmation::where('company_job_id', $jobId)->first();
            if (!$material_order) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Material Order Not Found'
                ], 422);
            }
            
            // Handle Attachments
            $attachmentPaths = [];
            if (isset($request->attachments)) {
                foreach ($request->file('attachments') as $attachment) {
                    // Store each attachment and get its path
                    $attachmentPaths[] = $attachment->store('attachments'); // Adjust the path as needed
                }
            }
            
            // Dispatch the job with attachment paths
            dispatch(new MaterialOrderConfirmationJob($sendToEmails, $request->subject, $request->email_body, $attachmentPaths));
            
            // Update Material Order
            MaterialOrderConfirmation::updateOrCreate([
                'company_job_id' => $jobId
            ], [
                'company_job_id' => $jobId,
                'type' => 2,
                'material_confirmation_email_sent' => $request->status
            ]);
            
            return response()->json([
                'status' => 200,
                'message' => 'Email Sent successfully',
                'data' => []
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile()], 500);
        }
    }



    // public function confirmationEmail(Request $request, $id)
    // {
    //     //Validate Request
    //     $this->validate($request, [
    //         'send_to' => 'required|array',
    //         'send_to.*' => 'required|email',
    //         'subject' => 'required|string',
    //         'email_body' => 'required|string',
    //     ]);
        
    //     try {
            
    //         //Check Material Order
    //         $material_order = MaterialOrder::where('id', $id)->first();
    //         if(!$material_order) {
    //             return response()->json([
    //                 'status' => 422,
    //                 'message' => 'Please create Material Order.'
    //             ], 422);
    //         }
            
    //         //Send Emails
    //         foreach($request->send_to as $email)
    //         {
    //             dispatch(new ConfirmationJob($email,$request->subject,$request->email_body));
    //         }
            
    //         //Update Material Order
    //         MaterialOrderConfirmation::updateOrCreate([
    //             'material_order_id' => $id
    //         ],[
    //             'material_order_id' => $id,
    //             'confirmation_email' => $request->confirmation_email
    //         ]);
            
    //         return response()->json([
    //             'status' => 200,
    //             'message' => 'Email Sent successfully',
    //             'data' => []
    //         ], 200);
            
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
    //     }
    // }
    // public function materialOrderconfirmationEmail(Request $request, $id)
    // {
    //     //Validate Request
    //     $this->validate($request, [
    //         'send_to' => 'required|email',
    //         'subject' => 'required|string',
    //         'email_body' => 'required',
    //         'attachments' => 'nullable|array',
    //     ]);
        
    //     try {
            
    //         //Check Material Order
    //         $material_order = MaterialOrder::where('id', $id)->first();
    //         if(!$material_order) {
    //             return response()->json([
    //                 'status' => 422,
    //                 'message' => 'Material Order Not Found'
    //             ], 422);
    //         }
            
    //         //Send Email
    //         if(isset($request->attachments)) {
    //             $attachments = $request->file('attachments');
    //         } else {
    //             $attachments = [];
    //         }
    //         dispatch(new MaterialOrderConfirmationJob($request->send_to,$request->subject,$request->email_body,$attachments));
            
    //         //Update Material Order
    //         MaterialOrderConfirmation::updateOrCreate([
    //             'material_order_id' => $id
    //         ],[
    //             'material_order_id' => $id,
    //             'material_order_confirmation_email' => $request->material_order_confirmation_email
    //         ]);
            
    //         return response()->json([
    //             'status' => 200,
    //             'message' => 'Email Sent successfully',
    //             'data' => []
    //         ], 200);
            
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
    //     }
    // }
}
