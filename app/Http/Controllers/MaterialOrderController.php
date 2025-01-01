<?php

namespace App\Http\Controllers;

use Log;
use PDF;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Material;
use App\Models\CompanyJob;
use App\Models\BuildDetail;
use App\Models\ReadyToBuild;
use Illuminate\Http\Request;
use App\Jobs\ConfirmationJob;
use App\Models\MaterialOrder;
use App\Jobs\MaterialOrderJob;
use App\Models\CustomerAgreement;
use App\Models\MaterialSelection;
use App\Models\MaterialOrderMedia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\MaterialOrderMaterial;
use Illuminate\Support\Facades\Storage;
use App\Models\MaterialOrderConfirmation;
use App\Jobs\MaterialOrderConfirmationJob;
use App\Models\MaterialOrderDeliveryInformation;
use App\Notifications\MaterialOrderConfirmationNotification;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class MaterialOrderController extends Controller
{
    public function materialList()
    {
        // dd();
        $options = Material::with('subOptions')->get(); // Fetch options with sub-options
        return response()->json($options);
    }

    public function materialOrder(Request $request, $id) //add this in ready build now
    {
        //Validate Request
        $this->validate($request, [
            // 'supplier_id' => 'required|integer',
            // 'street' => 'nullable',
            // 'city' => 'nullable',
            // 'state' => 'nullable',
            // 'zip_code' => 'nullable',
            // 'insurance' => 'nullable',
            // 'claim_number' => 'nullable',
            // 'policy_number' => 'nullable',
            'date_needed' => 'nullable|date_format:m/d/Y',
            'square_count' => 'nullable',
            'total_perimeter' => 'nullable',
            'ridge_lf' => 'nullable',
            'build_date' => 'nullable|date_format:m/d/Y',
            'valley_sf' => 'nullable',
            'hip_and_ridge_lf' => 'nullable',
            'drip_edge_lf' => 'nullable',
            // 'status' => 'nullable|in:0,1',
            'materials' => 'nullable|array',
            'materials.*.material' => 'nullable|string',
            'materials.*.quantity' => 'nullable',
            'materials.*.color' => 'nullable',
            'materials.*.order_key' => 'nullable',
            'notes'=>'nullable|string',
            'file_name' => 'nullable|array',
            'file_name.*' => 'nullable',
            'media_type' => 'nullable|array',
            'media_type.*' => 'nullable|string',
            'media' => 'nullable|array',
            'media.*' => 'nullable|mimes:jpg,png,pdf|max:2048',
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


            /////////////get previous details////////////
            $customer_agreement = CompanyJob::with('summary:id,company_job_id,insurance,policy_number,claim_number')->where('id',$id)->select('id','name','email','phone','address')->first();
            // return response()->json([
            //     'data'=> $customer_agreement
            // ]);
          
            if ($customer_agreement) {
                $decodedAddress = json_decode($customer_agreement->address, true);

                // Decode the address JSON string into a PHP array
                $customer_agreement->city = $decodedAddress['city'] ?? null;
                $customer_agreement->postalCode = $decodedAddress['postalCode'] ?? null;
                $customer_agreement->street = $decodedAddress['street'] ?? null;
                $customer_agreement->state = $decodedAddress['state'] ?? null;
                $customer_agreement->formatedAddress = $decodedAddress['formatedAddress'] ?? null;
            
                unset($customer_agreement->address);

                  // Separate summary values
                if ($customer_agreement->summary) {
                    $customer_agreement->insurance = $customer_agreement->summary->insurance;
                    $customer_agreement->policy_number = $customer_agreement->summary->policy_number;
                    $customer_agreement->claim_number = $customer_agreement->summary->claim_number;

                    unset($customer_agreement->summary);
                }

            }
            //Generate PO number
            $poNumber = $this->generatePONumber();

            //Check Supplier
            // $supplier = User::where('id', $request->supplier_id)->where('role_id', 4)->first();
            // if(!$supplier) {
            //     return response()->json([
            //         'status' => 422,
            //         'message' => 'Supplier Not Found'
            //     ], 422);
            // }

            //Store Material Order
            $city = $decodedAddress['city'] ?? null;
            $postalCode = $decodedAddress['postalCode'] ?? null;
            $street = $decodedAddress['street'] ?? null;
            $state = $decodedAddress['state'] ?? null;
            $formatedAddress = $decodedAddress['formatedAddress'] ?? null;
        
            $insurance = $customer_agreement->summary->insurance ?? null;
            $claimnumber = $customer_agreement->summary->claim_number ?? null;
            $policynumber = $customer_agreement->summary->policy_number ?? null;

            $material_order = MaterialOrder::updateOrCreate([
                'company_job_id' => $id,
            ],[
                'company_job_id' => $id,
                'po_number' => $poNumber, 
                'supplier_id' => $request->supplier_id,
                'street' => $street,
                'city' =>  $city,
                'state' => $state,
                'zip_code' => $postalCode,
                'insurance' => $insurance,
                'claim_number' => $claimnumber,
                'policy_number' => $policynumber,
                'date_needed' => $request->date_needed,
                'square_count' => $request->square_count,
                'total_perimeter' => $request->total_perimeter,
                'ridge_lf' => $request->ridge_lf,
                'build_date' => $request->build_date,
                'valley_sf' => $request->valley_sf,
                'hip_and_ridge_lf' => $request->hip_and_ridge_lf,
                'drip_edge_lf' => $request->drip_edge_lf,
            ]);

            ////////save documents and notes////////////
            $savedPhotos = [];
            $medias = $request->media ?? [];

            foreach($medias as $index=>$media)
            {
                $image_filename = time(). '.'. $media->getClientOriginalName();
                $image_filePath = $media->storeAs('material_order_documents',$image_filename,'public');

                //store Path
                $media = new MaterialOrderMedia();
                $media->notes = $request->notes;
                $media->material_order_id = $material_order->id;
                $media->file_name = $request->file_name[$index] ?? null; // Use the corresponding file_name
                $media->media_type = $request->media_type[$index] ?? null;
                $media->media_url = Storage::url($image_filePath);
                $media->save();
                  // Collect saved photo details
                  $savedPhotos[] = [
                    'id' => $media->id,
                    'material_order_id' => $media->material_order_id,
                    'file_name' => $media->file_name,
                    'media_type' => $media->media_type,
                    'media_url' => $media->media_url,
                    'created_at' => $media->created_at,
                    'updated_at' => $media->updated_at,
                ];
            }

            ///end document and notes logic////

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
            // if(isset($request->status)) {
            //     $job->status_id = 10;
            //     $job->date = Carbon::now()->format('Y-m-d');
            //     $job->save();
            // }

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => 'Material Order Created Successfully',
                'data' =>     $material_order->load('materials', 'media'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    private function generatePONumber()
    {
        $latestOrder = MaterialOrder::latest('id')->first();
        $nextId = $latestOrder ? $latestOrder->id + 1 : 1; // Increment ID or start at 1
        return 'PO-' . str_pad($nextId, 8, '0', STR_PAD_LEFT); // Format: PO-00000001
    }

    //material Selections
    public function materialSelection(Request $request, $id)
    {
        $this->validate($request,[
            'name' => 'nullable|array',
            'name.*'=> 'nullable|string',
            'option' => 'nullable|array',
            'option.*' => 'nullable|string',
            'unit' => 'nullable|array',
            'unit.*' => 'nullable|string',
            'unit_cost' => 'nullable|array',
            'unit_cost.*' => 'nullable|string',
            'quantity' => 'nullable|array',
            'quantity.*' => 'nullable|string',
            'total' => 'nullable|array',
            'total.*' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->name as $index => $name) {
                $material = MaterialSelection::create(
                    [   
                        'material_order_id' => $id,
                        'name' => $name,
                        'option' => $request->option[$index] ?? null,
                        'unit' => $request->unit[$index] ?? null,
                        'unit_cost' => $request->unit_cost[$index] ?? null,
                        'quantity' => $request->quantity[$index] ?? null,
                        'total' => $request->total[$index] ?? null,
                    ]       
                );
            }
    
            DB::commit();
    
            return response()->json([
                'status' => 200,
                'message' => 'Material selection saved successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); 
    
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage() . ' on line ' . $e->getLine(),
            ]);
        }

    }

    public function getMaterialSelection($id)
    {
        $material_order = MaterialOrder::where('id',$id)->first();
        if(!$material_order)
        {
            return response()->json([
                'status' => 404,
                'message' => 'Material Order Not Exist.',
                'data' => []
            ]);
        }

        $material_order_selection = MaterialSelection::where('material_order_id',$id)->get();
        if($material_order_selection->isEmpty())
        {
            return response()->json([
                'status' =>404,
                'message' => 'Materail Order Selection not Found',
                'data' => []
            ]);
           
        }else{
            return response()->json([
                'status' => 200,
                'message' => 'Data Fetched Successfully',
                'data' => $material_order_selection
            ]);
        }
        
    }

    public function generatePdf($jobId)
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
            //here we will create a pdf 
            $material_order =MaterialOrder::with('materials','materialSelection')->where('company_job_id',$jobId)->first();
            if (!$material_order) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Material Order Not Found'
                ], 404);
            }
            $materialSelection=$material_order->materialSelection;
            // Log::info($material_order->materialSelection);
            //Generate PDF
            $pdf = PDF::loadView('pdf.material-order', [
                // Log::info($material_order),// Ensure all data is present

                'data' => $material_order,
                'materialSelection' => $materialSelection,
            ]);
            $pdf_fileName = time() . '.pdf';
            $pdf_filePath = 'material_order_pdf/' . $pdf_fileName;
            
           // Check if the old PDF exists and delete it
            if (!is_null($material_order->sign_pdf_url)) {
                $oldPdfPath = public_path($material_order->sign_pdf_url);
                if (file_exists($oldPdfPath) && is_file($oldPdfPath)) {
                    unlink($oldPdfPath);
                }
            }
 
            // Save the new PDF
            Storage::put('public/' . $pdf_filePath, $pdf->output());

            //Save PDF Path
            $pdf = MaterialOrder::updateOrCreate([
                'company_job_id' => $jobId 
            ],[
                'company_job_id' => $jobId,
                'sign_pdf_url' => '/storage/' . $pdf_filePath  //here saved the pdf file path
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'PDF Generated Successfully',
                'data' => $pdf
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function viewPdf()
    {
        $directory = storage_path('app/public/material_order_pdf');

        // Get all PDF files in the directory
        $files = glob($directory . '/*.pdf');

        if (empty($files)) {
            return response()->json([
                'status' => 422,
                'message' => 'No PDFs found'
            ], 404);
        }

        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        $latestPdfPath = $files[0]; 

        if (!file_exists($latestPdfPath)) {
            return response()->json([
                'status' => 404,
                'message' => 'Latest PDF not found'
            ], 404);
        }

        return response()->file($latestPdfPath);
    }

    public function deleteMaterialOrderMaterial($id)
    {
        try{
            $material=MaterialOrderMaterial::find($id);
            if (empty($material)) {
                return response()->json([
                    'status' => 422,
                    'message' => 'No Material Order Material found'
                ], 404);
            }
            $material->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Material Order Deleted Successfully',
                'data' => $material,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile()], 500);
        }

    }

    public function getMaterialOrder($id)
    {
        try {
            // $customer_agreement = CustomerAgreement::select('street','city','state','zip_code','insurance','claim_number','policy_number')->where('company_job_id', $id)->first();
            $customer_agreement = CompanyJob::with('summary:id,company_job_id,insurance,policy_number,claim_number')->where('id',$id)->select('id','name','email','phone','address')->first();
            if ($customer_agreement) {
                $decodedAddress = json_decode($customer_agreement->address, true);

                // return response()->json([
                //     'data'=>  $decodedAddress
                // ]);
                // Decode the address JSON string into a PHP array
                // $customer_agreement->address = json_decode($customer_agreement->address, true);
                $customer_agreement->city = $decodedAddress['city'] ?? null;
                // dd($decodedAddress['city']);
                $customer_agreement->postalCode = $decodedAddress['postalCode'] ?? null;
                $customer_agreement->street = $decodedAddress['street'] ?? null;
                $customer_agreement->state = $decodedAddress['state'] ?? null;
                $customer_agreement->formatedAddress = $decodedAddress['formatedAddress'] ?? null;
            
                // unset($customer_agreement->address);

                  // Separate summary values
                if ($customer_agreement->summary) {
                    $customer_agreement->insurance = $customer_agreement->summary->insurance;
                    $customer_agreement->policy_number = $customer_agreement->summary->policy_number;
                    $customer_agreement->claim_number = $customer_agreement->summary->claim_number;

                    // unset($customer_agreement->summary);
                }

            }
            // return response($customer_agreement->toArray());
            $job = CompanyJob::select('name', 'email', 'phone')->find($id);

            $material_order = MaterialOrder::where('company_job_id', $id)->with('materials', 'supplier')->first();

            // Prepare the response data
            $response_data = [];

            // Always include job data in the response at the same level
            $decodedAddress = json_decode($customer_agreement->address, true);

            if ($job) {
                $response_data['name'] = $job->name;
                $response_data['email'] = $job->email;
                $response_data['phone'] = $job->phone;
                $response_data['city'] = $decodedAddress['city'] ?? null;
                $response_data['street'] = $decodedAddress['street'] ?? null;
                $response_data['state'] = $decodedAddress['state'] ?? null;
                $response_data['postalCode'] = $decodedAddress['postalCode'] ?? null;
                $response_data['insurance'] = $customer_agreement->summary->insurance ?? null;
                $response_data['policy_number'] = $customer_agreement->summary->policy_number ?? null;
                $response_data['claim_number'] = $customer_agreement->summary->claim_number ?? null;

            }

            if ($material_order) {
                $response_data = array_merge($response_data, $material_order->toArray());
                $response_message = 'Material Order Found successfully';
            } 
            
            if (!$material_order && $customer_agreement) {
                $response_data = array_merge($response_data, $customer_agreement->toArray());
                $response_message = 'No Material Order found, Customer Agreement returned';
            } 
            
            if (!$material_order && !$customer_agreement) {
                $response_message = 'No Material Order or Customer Agreement found for this Job';
            }

            $status_code = ($material_order || $customer_agreement) ? 200 : 200;

            return response()->json([
                'status' => $status_code,
                'message' => $response_message,
                'data' => $response_data,
            ], $status_code);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile()], 500);
        }
    }

    public function updateMaterialOrder(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'sign_image' => 'nullable',
        ]);

        try {

            //Check Material Order
            $material_order = MaterialOrder::where('company_job_id',$id)->first();
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
                'material_order' => $material_order,
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
            'supplier_id' => 'nullable|integer',
            'sub_contractor_id' => 'nullable|integer',
            'material_order_id' => 'nullable|integer',
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
            $supplier = User::where('id', $request->supplier_id)->where('role_id', 4)->first();
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
            // $assigned_supplier = MaterialOrder::whereNotNull('supplier_id')->first();
            // if(!$assigned_supplier) {
            //     return response()->json([
            //         'status' => 422,
            //         'message' => 'Supplier Not Yet Assigned'
            //     ], 422);
            // }
            

            //Check Supplier
            $supplier = User::where('id', $material_order->supplier_id)->where('role_id', 4)->first();
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
            'confirmed' => 'nullable|in:true,false',
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
            // $readyBuild = ReadyToBuild::where('company_job_id', $jobId)->first();

            //Update Build Detail
            $build_detail = BuildDetail::updateOrCreate([
                'company_job_id' => $jobId
            ],[
                'company_job_id' => $jobId,
                'build_date' => $request->build_date,
                'build_time' => $request->build_time,
                // 'homeowner' => $readyBuild->home_owner ?? '',
                // 'homeowner_email' => $readyBuild->home_owner_email ?? '',
                'homeowner' => $request->homeowner,
                'homeowner_email' => $request->homeowner_email,
                'contractor' => $request->contractor,
                'contractor_email' => $request->contractor_email,
                'supplier' => $request->supplier,
                'supplier_email' => $request->supplier_email,
                'confirmed' => $request->confirmed,
            ]);

            //i am adding the supplier in material order from here
            // $supplier = User::where('email',$request->supplier_email)->where('role_id',4)->first();
            // $supplier_id = $supplier->id;
            // $build_detail = MaterialOrder::updateOrCreate([
            //     'company_job_id' => $jobId
            // ],[
            //     'company_job_id' => $jobId,
            //     'supplier_id' => $supplier_id,
            // ]);

            //Update Status
            if(isset($request->confirmed) && $request->confirmed == 'true') {
                $job->status_id = 10;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();
            }

            return response()->json([
                'status' => 200,
                'message' => 'Build Detail Updated Successfully',
                'data' => $build_detail
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function updateBuildDetailStatus(Request $request, $jobId)
    {
        $this->validate($request, [
            'confirmed' => 'nullable|in:true,false',
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

            //Update Build Detail
            $build_detail = BuildDetail::updateOrCreate([
                'company_job_id' => $jobId
            ],[
                'confirmed' => $request->confirmed,
            ]);
            //Update Status
            if(isset($request->confirmed) && $request->confirmed == 'true') {
                $job->status_id = 6;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();

                  //current stage save
                $build_detail->current_stage="yes";
                $build_detail->save();

            }if(isset($request->confirmed) && $request->confirmed == 'false') {
                $job->status_id = 10;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();

                  //current stage save 
                $build_detail->current_stage="no";
                $build_detail->save();
            }

            return response()->json([
                'status' => 200,
                'message' => 'Build Details Status Updated Successfully',
                'data' => [$build_detail]
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
            //  $readyBuild = ReadyToBuild::where('company_job_id', $jobId)->first();

            //  if (!$build_detail) {
            //     return response()->json([
            //         'status' => 200,
            //         'message' => 'Build Details Not Yet Created',
            //         'data' => []
            //     ], 200);
            // }
            // Return response with Ready To Build details
            return response()->json([
                'status' => 200,
                'message' => 'Build Details Found Successfully',
                'data' =>
                [
                        'homeowner' => $build_detail->homeowner ?? '',
                        'homeowner_email' => $build_detail->homeowner_email ?? '',
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
                        'confirmed' => $build_detail->confirmed ?? '',
                        'created_at' => $build_detail->created_at ?? '',
                        'updated_at' => $build_detail->updated_at ?? '',                    
                ]
            ], 200);
        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    
    public function confirmationEmail(Request $request, $jobId)
    {
        //Validate Request
        $this->validate($request, [
            'send_to' => 'nullable|string',
            'subject' => 'nullable|string',
            'email_body' => 'nullable|string',
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
            $confirmationEmailSent = $request->input('status', 'false');

            //Update Material Order
            MaterialOrderConfirmation::updateOrCreate([
                'company_job_id' => $jobId
            ],[
                'company_job_id' => $jobId,
                'type'=>1,
                'confirmation_email_sent' => $confirmationEmailSent,

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

    public function confirmationEmailStatus(Request $request, $jobId)
    {
         //Validate Request
         $this->validate($request, [
            'status' => 'nullable|in:true,false',
        ]);

        try{
            //Check job exist or not
            $material_order = CompanyJob::where('id', $jobId)->first();
            if(!$material_order) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Company Job Not Created.'
                ], 422);
            }

            $confirmationEmailSent = $request->input('status', 'false');
            //Update Material Order
            MaterialOrderConfirmation::updateOrCreate([
                'company_job_id' => $jobId
            ],[
                'company_job_id' => $jobId,
                'type'=>1,
                'confirmation_email_sent' => $confirmationEmailSent,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Staus Updated successfully',
                'data' => []
            ], 200);

        }catch(\Exception $e)
        {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getConfirmationEmailStatus(Request $request, $jobId)
    {
        try{
            //Check job exist or not
            $material_order = CompanyJob::where('id', $jobId)->first();
            if(!$material_order) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Company Job Not Created.'
                ], 422);
            }
            $material_status = MaterialOrderConfirmation::select('id','company_job_id','confirmation_email_sent','created_at','updated_at')->where('company_job_id', $jobId)->first();

            if ($material_status) {
                $status = $material_status->toArray();
                
                // Change the key name from confirmation_email_sent to status
                $status['status'] = $status['confirmation_email_sent'];
                unset($status['confirmation_email_sent']); // Remove the old key
            
                return response()->json([
                    'status' => 200,
                    'message' => 'Status Found successfully',
                    'data' => $status, // Return the updated data
                ], 200);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'Material status not found',
                ], 404);
            }

        }catch(\Exception $e)
        {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function materialOrderconfirmationEmail(Request $request, $jobId)
    {
        // Validate Request
        $this->validate($request, [
            'send_to' => 'nullable|string',
            'subject' => 'nullable|string',
            'email_body' => 'nullable',
            'attachments' => 'nullable|array',
            'status' => 'nullable|in:true,false',
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
             // Prepare Attachments
            // $attachmentPaths = [];
            // if ($request->hasFile('attachments')) {
            //     foreach ($request->file('attachments') as $attachment) {
            //         // Check if the uploaded item is indeed a file
            //         if ($attachment instanceof \Illuminate\Http\UploadedFile) {
            //             // Add attachment to the array
            //             $attachmentPaths[] = $attachment->store('attachments/temp'); // Store in a temporary folder
            //         }
            //     }
            // }

            $attachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('temp'); 
                    $attachments[] = $path; 
                }
            }
    
            // Create an instance of the custom notifiable
            $notifiable = new \App\CustomNotifiable($request->send_to);
    
            // Create the notification
            $notification = new MaterialOrderConfirmationNotification($request->subject, $request->email_body, $attachments);
            
            // Dispatch the job with attachment paths
            // dispatch(new MaterialOrderConfirmationJob($sendToEmails, $request->subject, $request->email_body, $attachmentPaths));
            
            $confirmationEmailSent = $request->input('status', 'false');

            // Update Material Order
            MaterialOrderConfirmation::updateOrCreate([
                'company_job_id' => $jobId
            ], [
                'company_job_id' => $jobId,
                'type' => 2,
                'material_confirmation_email_sent' => $confirmationEmailSent
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

    public function materialOrderconfirmationEmailStatus(Request $request, $jobId)
    {
        // Validate Request
        $this->validate($request, [
            'status' => 'nullable|in:true,false',
        ]);
        
        try {
            // Check Material Order
            $material_order = MaterialOrderConfirmation::where('company_job_id', $jobId)->first();
            if (!$material_order) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Material Order Not Found'
                ], 422);
            }            
            $confirmationEmailSent = $request->input('status', 'false');

            // Update Material Order
            MaterialOrderConfirmation::updateOrCreate([
                'company_job_id' => $jobId
            ], [
                'company_job_id' => $jobId,
                'type' => 2,
                'material_confirmation_email_sent' => $confirmationEmailSent
            ]);
            
            return response()->json([
                'status' => 200,
                'message' => 'Email Sent Successfully',
                'data' => $material_order
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile()], 500);
        }
    }

    public function getMaterialOrderconfirmationEmailStatus(Request $request, $jobId)
    {
        try{
            //Check job exist or not
            $material_order = CompanyJob::where('id', $jobId)->first();
            if(!$material_order) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Company Job Not Created.'
                ], 422);
            }
            $material_status = MaterialOrderConfirmation::select('id','company_job_id','material_confirmation_email_sent','created_at','updated_at')->where('company_job_id', $jobId)->first();

            if ($material_status) {
                $status = $material_status->toArray();
                
                // Change the key name from confirmation_email_sent to status
                $status['status'] = $status['material_confirmation_email_sent'];
                unset($status['material_confirmation_email_sent']); // Remove the old key
            
                return response()->json([
                    'status' => 200,
                    'message' => 'Status Found successfully',
                    'data' => $status,
                ], 200);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'Material status not found',
                ], 404);
            }

        }catch(\Exception $e)
        {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
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
