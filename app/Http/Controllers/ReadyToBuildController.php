<?php

namespace App\Http\Controllers;

use Log;
// use PDF;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Verdict;
use App\Models\CompanyJob;
use App\Models\ReadyToBuild;
use Illuminate\Http\Request;
use App\Models\MaterialOrder;
use App\Jobs\MaterialOrderJob;
use App\Mail\ReadyToBuildEmail;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CompanyJobSummary;
use App\Models\CustomerAgreement;
use App\Models\ReadyToBuildMedia;
use Illuminate\Support\Facades\Mail;
use App\Models\MaterialOrderMaterial;
use Illuminate\Support\Facades\Storage;

class ReadyToBuildController extends Controller
{
    public function storeReadyToBuild(Request $request, $jobId)
    {
        // Validation Request
        $this->validate($request, [
            // 'home_owner' => 'nullable|string|max:255',
            // 'home_owner_email' => 'nullable|email',
            // 'date' => 'nullable|date_format:m/d/Y',
            'notes' => 'nullable|string',
            'attachements.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,txt',
            'status' => 'nullable|in:true,false',
            //material order square count//
            'square_count' => 'nullable',
            'total_perimeter' => 'nullable',
            'ridge_lf' => 'nullable',
            //quantity and color
            'materials' => 'nullable|array',
            'materials.*.material' => 'nullable|string',
            'materials.*.quantity' => 'nullable',
            'materials.*.color' => 'nullable',
            'materials.*.order_key' => 'nullable',
            //supplier selection
            'supplier_id' => 'nullable',
        ]);

        // dd($request->all());
        try {
            // Check Job
            $job = CompanyJob::find($jobId);
            if (!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job not found',
                ], 422);
            }

            // Update Ready To Build
            $ready_to_build = ReadyToBuild::updateOrCreate([
                'company_job_id' => $jobId,
            ], [
                'company_job_id' => $jobId,
                // 'home_owner' => $request->home_owner,
                // 'home_owner_email' => $request->home_owner_email,
                // 'date' => $request->date,
                'notes' => $request->notes,
                'status' => "true",
                'supplier_id' => $request->supplier_id
            ]);

            if (isset($request->attachements) && count($request->attachements) > 0) {
                // New attachments are present in the request
                // Step 1: Delete old attachments
                $existingAttachments = ReadyToBuildMedia::where('ready_build_id', $ready_to_build->id)->get();
                foreach ($existingAttachments as $attachment) {
                    // Delete file from storage
                    $filePath = str_replace('/storage/', 'public/', $attachment->image_url);
                    Storage::delete($filePath);
                    $attachment->delete(); // Delete the record from the database
                }
            
                // Step 2: Store new attachments
                foreach ($request->attachements as $documents) {
                    $fileName = time() . '_' . $documents->getClientOriginalName();
                    $filePath = $documents->storeAs('public/ready_to_build', $fileName);
            
                    // Store Path
                    $media = new ReadyToBuildMedia();
                    $media->ready_build_id = $ready_to_build->id;
                    $media->image_url = Storage::url($filePath);
                    $media->file_name = $documents->getClientOriginalName();
                    $media->save( );
                }
            } else {
                // No new attachments in the request, retain existing attachments
                $existingAttachments = ReadyToBuildMedia::where('ready_build_id', $ready_to_build->id)->get();
            
                // If you need to return the existing attachments, format them
                $attachments = $existingAttachments->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        'image_url' => $attachment->image_url,
                        'file_name' => $attachment->file_name,
                    ];
                });
            }            
       
            //Generate PO number
            $poNumber = $this->generatePONumber();
            
            //here add  square count of material order
            $material_order = MaterialOrder::updateOrCreate([
                'company_job_id' => $jobId,
            ],[
                'company_job_id' => $jobId,
                'po_number' => $poNumber, 
                'date_needed' => $request->date_needed,
                'square_count' => $request->square_count,
                'total_perimeter' => $request->total_perimeter,
                'ridge_lf' => $request->ridge_lf,

                'build_date'=> $request->build_date,
                'valley_sf' => $request->valley_sf,
                'hip_and_ridge_lf' => $request->hip_and_ridge_lf,
                'drip_edge_lf' => $request->drip_edge_lf,
            ]);

            if($material_order)
            {
               $materialOrder =  MaterialOrder::select('id','po_number','date_needed','square_count','total_perimeter','ridge_lf','build_date','valley_sf','hip_and_ridge_lf','drip_edge_lf')->where('company_job_id',$jobId)->first();
            }

            MaterialOrderMaterial::where('material_order_id', $material_order->id)->delete();

            ///material order quanity and color add
            if($request->materials)
            {
                // Step 1: Delete old materials

            // Step 2: Insert new materials
            // if ($request->materials) {
                foreach ($request->materials as $material) {
                    $add_material = new MaterialOrderMaterial;
                    $add_material->material_order_id = $material_order->id;
                    $add_material->material = $material['material'];
                    $add_material->quantity = isset($material['quantity']) ? $material['quantity'] : null;
                    $add_material->color = isset($material['color']) ? $material['color'] : null;
                    $add_material->order_key = isset($material['order_key']) ? $material['order_key'] : null;
                    $add_material->save();
                }
            }
            $ready_to_build->load('documents');
            $material_order->load('materials');
            $agreement = CompanyJob::with('aggrement')->where('id', $jobId)->first();
            
            //create the pdf and share the link also for signature
            $pdf = Pdf::loadView('pdf.readyTobuild',['data'=>$agreement,'readybuild' => $ready_to_build,'material_order'=>$material_order]);
            $pdf->setPaper('A4', 'portrait');

            $pdf_fileName = time() . '.pdf';
            $pdf_filePath = 'ready_to_build_pdf/' . $pdf_fileName;
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
            // return $pdf->stream('readytobuild.pdf');

            $supplier = User::where('id',$request->supplier_id)->first();
            //after saving data and making pdf file now send the pdf to supplier for signature               
             Mail::to($supplier->email)->send(new ReadyToBuildEmail($agreement, $ready_to_build, $material_order));

            return response()->json([
                'status' => 200,
                'message' => 'Ready To Build Added Successfully',
                'data' => [
                    'customer_info' => $agreement,
                    'readybuild'=>$ready_to_build, 
                     'material_order' => $material_order
                    // 'material_order'=>$materialOrder
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile()], 500);
        }
    }

    ///sedn email to supplier///
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
            $material_order = MaterialOrder::with('materialSelection')->where('company_job_id', $jobId)->with('job','materials')->first();
            $materialSelection = $material_order->materialSelection;
            // return response($material_order);
            if(!$material_order) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Material Order Not Found'
                ], 422);
            }

            //Check if Supplier is assigned
            // $assigned_supplier = ReadyToBuild::whereNotNull('supplier_id')->first();
            // if(!$assigned_supplier) {
            //     return response()->json([
            //         'status' => 422,
            //         'message' => 'Supplier Not Yet Assigned'
            //     ], 422);
            // }

            //in rady to build check supplier
            $ready_to_build = ReadyToBuild::where('company_job_id', $jobId)->first();
            if(!$ready_to_build)
            {
                return response()->json([
                    'status' => 422,
                    'message' => 'Build is Not Found'
                ], 422);
            }
            //Check Supplier
            $supplier = User::where('id', $ready_to_build->supplier_id)->where('role_id', 4)->first();
            // dd($supplier);
            if(!$supplier) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Supplier Not Found'
                ], 422);
            }

            //Generate PDF
            $pdf = PDF::loadView('pdf.material-order', [
                'materialSelection'=>$materialSelection,
                'data' => $material_order]);
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
                'data' => $material_order
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    private function generatePONumber()
    {
        $latestOrder = MaterialOrder::latest('id')->first();
        $nextId = $latestOrder ? $latestOrder->id + 1 : 1; // Increment ID or start at 1
        return 'PO-' . str_pad($nextId, 8, '0', STR_PAD_LEFT); // Format: PO-00000001
    }

    public function storeReadyToBuildStatus(Request $request, $jobId)
    {
        // Validation Request
        $this->validate($request, [
            'status' => 'nullable|in:true,false',
        ]);
        
        try {
            // Check Job
            $job = CompanyJob::find($jobId);
            if (!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job not found',
                ], 422);
            }

            // Update Ready To Build
            $ready_to_build = ReadyToBuild::updateOrCreate([
                'company_job_id' => $jobId,
            ], [
                'status' => $request->status,
            ]);

            //Update Status
            if(isset($request->status) && $request->status == "true") {
                $job->status_id = 10;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save(); 
                 //current stage save
                 $ready_to_build->current_stage="yes";
                 $ready_to_build->save();

            }elseif(isset($request->status) && $request->status == "false"){
                $job->status_id = 9;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();

             //current stage save
                $ready_to_build->current_stage="no";
                $ready_to_build->save();
            }

            return response()->json([
                'status' => 200,
                'message' => 'Ready To Build Status Updated Successfully',
                'data' => $ready_to_build,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile()], 500);
        }
    }


    public function getReadyToBuilddata($jobId) //here we are getting all info before pdf url, get whole data of ready to build
    {
        try {
            // Check Job
            $job = CompanyJob::find($jobId);
            if (!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job not found'
                ], 422);
            }

            //here i will get customer information
            $customer_info = CompanyJob::with('summary:id,company_job_id,insurance,policy_number,claim_number')->where('id',$jobId)->select('id','name','email','phone','address')->first();
            // return response()->json([
            //     'data'=> $customer_info
            // ]);
            if($customer_info)
            {
                $customer_info->address = json_decode($customer_info->address, true);
            }

            // Retrieve Ready To Build
            $readyToBuild = ReadyToBuild::with('documents')->where('company_job_id', $jobId)->first();
            
            if (!$readyToBuild) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Ready To Build Not Yet Created',
                    // 'data' => (object)[] 
                    'data' =>  $customer_info
                ], 200);
            }

            $material_order =  MaterialOrder::select('id','po_number','date_needed','square_count','total_perimeter','ridge_lf','build_date','valley_sf','hip_and_ridge_lf','drip_edge_lf','sign_pdf_url')->where('company_job_id',$jobId)->first();

        
            // Check if material order exists
            if ($material_order) {
                $material_order->load('materials');
            }
            
            // Return response with Ready To Build details
            return response()->json([
                'status' => 200,
                'message' => 'Ready To Build Found Successfully',
                'data' => [
                    'readybuild' => $readyToBuild,
                    'customer_info' => $customer_info,
                    'material_order' => $material_order, // This will return null if not found
                ],
            ], 200);
        

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

   
    public function getReadyToBuild($jobId) //use it to get the document and customer name in ready to build instead of whole data
    {
        try {
            // Check Job
            $job = CompanyJob::find($jobId);
            if (!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job not found'
                ], 422);
            }

            // Get customer information
            $customer_info = CompanyJob::where('id', $jobId)->select('name')->first();
            
            $material_order = MaterialOrder::select('sign_pdf_url')->where('company_job_id', $jobId)->first();

            $readyToBuild = ReadyToBuild::with('documents')->where('company_job_id', $jobId)->first();

            // Return response with updated key names
            return response()->json([
                'status' => 200,
                'message' => 'Ready To Build Found Successfully',
                'data' => [
                    'custome_name' => $customer_info->name ?? null, // Change key from 'name' to 'custome_name'
                    'sign_pdf_url' => $material_order->sign_pdf_url ?? null, // Keep same key
                    'status' => $readyToBuild->status,
                ],
            ], 200);
        
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }


    //file name saved
    public function changeReadyToBuildFileName(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'file_name' => 'required|string'
        ]);
        
        try {
            
            //Check Adjustor Meeting Media
            $ready_to_build = ReadyToBuildMedia::find($id);
            if(!$ready_to_build) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Ready To Build Media Not Found'
                ], 422);
            }

            //Update File Name
            $ready_to_build->file_name = $request->file_name;
            $ready_to_build->save();

            return response()->json([
                'status' => 200,
                'message' => 'File Name Updated Successfully',
                'data' => []
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    //delete file name
    public function deleteReadyToBuildMedia(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'image_url' => 'required|string'
        ]);
        
        try {
            
            //Check Ready to build Media
            $check_ready_to_build = ReadyToBuildMedia::find($id);
            if(!$check_ready_to_build) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Ready To Build Media Not Found'
                ], 422);
            }

            //Delete Media
            $oldImagePath = str_replace('/storage', 'public', $check_ready_to_build->media_url);
            Storage::delete($oldImagePath);
            $check_ready_to_build->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Media Deleted Successfully',
                'data' => $check_ready_to_build
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }


    public function storeVerdictStatus(Request $request,$jobId)
    {
        // Validation Request
        $this->validate($request, [
            'status' => 'nullable|in:true,false',
        ]);
        
        try {
            // Check Job
            $job = CompanyJob::find($jobId);
            if (!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job not found',
                ], 422);
            }

            // Update Ready To Build
            $ready_to_build = Verdict::updateOrCreate([
                'company_job_id' => $jobId,
            ], [
                'status' => $request->status,
            ]);

            //Update Status
            if(isset($request->status) && $request->status == "true") {
                $job->status_id = 9;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save(); 
              

            }elseif(isset($request->status) && $request->status == "false"){
                $job->status_id = 6;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();

        
            }

            return response()->json([
                'status' => 200,
                'message' => 'Approved Status Updated Successfully',
                'data' => $ready_to_build,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile()], 500);
        }
    }


    // public function getReadyToBuild($jobId)
    // {
    //     try {

    //         //Check Job
    //         $job = CompanyJob::find($jobId);
    //         if(!$job) {
    //             return response()->json([
    //                 'status' => 422,
    //                 'message' => 'Job not found'
    //             ], 422);
    //         }

    //         $get_ready_to_build = ReadyToBuild::where('company_job_id', $jobId)->with('subContractor')->first();
    //         if(!$get_ready_to_build) {
    //             return response()->json([
    //                 'status' => 200,
    //                 'message' => 'Ready To Build Not Yet Created',
    //                 'data' => []
    //             ], 200);
    //         }

    //         return response()->json([
    //             'status' => 200,
    //             'message' => 'Ready To Build Found Successfully',
    //             'data' => $get_ready_to_build
    //         ], 200);

    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
    //     }
    // }

    // public function storeReadyToBuild(Request $request, $jobId)
    // {
    //     //Validation Request
    //     $this->validate($request, [
    //         'recipient' => 'required|string|max:255',
    //         'time' => 'required|date_format:h:i A', // 12-hour format
    //         'date' => 'required|date_format:m/d/Y',
    //         'text' => 'required',
    //         'sub_contractor_id' => 'required|integer',
    //         'completed' => 'nullable|in:1,0'
    //     ]);

    //     try {

    //         //Check Job
    //         $job = CompanyJob::find($jobId);
    //         if(!$job) {
    //             return response()->json([
    //                 'status' => 422,
    //                 'message' => 'Job not found'
    //             ], 422);
    //         }

    //         //Check Sub Contractor
    //         $sub_contractor = User::whereId($request->sub_contractor_id)->where('role_id', 3)->first();
    //         if(!$sub_contractor) {
    //             return response()->json([
    //                 'status' => 422,
    //                 'message' => 'Sub Contractor not found'
    //             ], 422);
    //         }

    //         //Update Ready To Build
    //         $ready_to_build = ReadyToBuild::updateOrCreate([
    //             'company_job_id' => $jobId,
    //         ],[
    //             'company_job_id' => $jobId,
    //             'sub_contractor_id' => $request->sub_contractor_id,
    //             'recipient' => $request->recipient,
    //             'date' => $request->date,
    //             'time' => $request->time,
    //             'text' => $request->text,
    //         ]);
            
    //         //Update Status
    //         if(isset($request->completed) && $request->completed == true) {
    //             $job->status_id = 9;
    //             $job->date = Carbon::now()->format('Y-m-d');
    //             $job->save();
    //         }

    //         return response()->json([
    //             'status' => 200,
    //             'message' => 'Ready To Build Added Successfully',
    //             'data' => $ready_to_build
    //         ], 200);

    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
    //     }
    // }
}
