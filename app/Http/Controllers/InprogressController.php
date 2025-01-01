<?php

namespace App\Http\Controllers;
use PDF;

use Carbon\Carbon;
use App\Models\CompanyJob;
use App\Models\Inprogress;
use Illuminate\Http\Request;
use App\Models\InprogressMedia;
use App\Models\BuildPacketChecklist;
use Illuminate\Support\Facades\Storage;
// use Barryvdh\DomPDF\Facade as PDF;

class InprogressController extends Controller
{
    public function updateInprogress(Request $request, $jobId)
    {
        // Validate Request
        $this->validate($request, [
            'build_start_date' => 'nullable|date_format:m/d/Y',
            'build_end_date' => 'nullable|date_format:m/d/Y',
            'notes' => 'nullable',
            // 'status' => 'nullable',

            //this is required photos section
            // 'images' => 'nullable|array',
            'morningPhotos' => 'nullable|array',
            'morningPhotos.*.label' => 'nullable|string',
            'morningPhotos.*.image' => 'nullable|file|mimes:jpg,jpeg,png',

            'compliancePhotos' => 'nullable|array',
            'compliancePhotos.*.label' => 'nullable|string',
            'compliancePhotos.*.image' => 'nullable|file|mimes:jpg,jpeg,png',

            'completionPhotos' => 'nullable|array',
            'completionPhotos.*.label' => 'nullable|string',
            'completionPhotos.*.image' => 'nullable|file|mimes:jpg,jpeg,png',

            //this is photo upload Fields Section
            // 'photos'=> 'nullable|array',
            // 'photos.*' => 'nullable|image',
            // 'labels' => 'nullable|array',
            // 'labels.*' => 'nullable|string',

            'production_sign_url' => 'nullable|string',
            'homeowner_signature' => 'nullable|string'
        ]);

        try {
            $job = CompanyJob::find($jobId);
            if (!$job) {
                return response()->json(['status' => 422, 'message' => 'Job not found'], 422);
            }

            // Update Inprogress
            $in_progress = Inprogress::updateOrCreate(
                ['company_job_id' => $jobId],
                [
                    'company_job_id' => $jobId,
                    'build_start_date' => $request->build_start_date,
                    'build_end_date' => $request->build_end_date,
                    'notes' => $request->notes,
                    // 'status' => $request->status,
                ]
            );

        // Save photos and store in their respective categories
            $morningPhotos = [];
            $compliancePhotos = [];
            $completionPhotos = [];

            $imageCategories = ['morningPhotos', 'compliancePhotos', 'completionPhotos'];
            foreach ($imageCategories as $category) {
                if (isset($request->$category) && is_array($request->$category)) {
                    foreach ($request->$category as $imageData) {
                        if (isset($imageData['image']) && $imageData['image']->isValid()) {
                            $filePath = $imageData['image']->store('public/inprogress_media');
                            $url = str_replace('public/', '/storage/', $filePath);

                            $media = new InprogressMedia();
                            $media->company_job_id = $jobId;
                            $media->labels = $imageData['label'] ?? null;
                            $media->image_path = $url;
                            $media->category = $category;
                            $media->save();

                            // Add to specific category arrays
                            if ($category == 'morningPhotos') {
                                $morningPhotos[] = [
                                    'labels' => $media->labels,
                                    'image_paths' => $url,
                                ];
                            } elseif ($category == 'compliancePhotos') {
                                $compliancePhotos[] = [
                                    'labels' => $media->labels,
                                    'image_paths' => $url,
                                ];
                            } elseif ($category == 'completionPhotos') {
                                $completionPhotos[] = [
                                    'labels' => $media->labels,
                                    'image_paths' => $url,
                                ];
                            }
                        }
                    }
                }
            }

            // Handle Base64 Signatures
            if ($request->production_sign_url) {
                $in_progress->production_sign_url = $this->saveBase64Image($request->production_sign_url, 'inprogress_signature');
            }
            if ($request->homeowner_signature) {
                $in_progress->homeowner_signature = $this->saveBase64Image($request->homeowner_signature, 'inprogress_signature');
            }

            // Generate and Save PDF
            $pdf = PDF::loadView('pdf.inprogress', ['data' => $in_progress, 'saved_photos' => $completionPhotos]);
            $pdf_fileName = time() . '.pdf';
            $pdf_filePath = 'inprogress_pdf/' . $pdf_fileName;

            if ($in_progress->pdf_url) {
                Storage::delete('public/' . str_replace('/storage/', '', $in_progress->pdf_url));
            }

            Storage::put('public/' . $pdf_filePath, $pdf->output());
            $in_progress->pdf_url = '/storage/' . $pdf_filePath;
            $in_progress->save();

            // Update Job Status
            if (isset($request->status) && $request->status == true) {
                $job->status_id = 11;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();
            }

            return response()->json([
                'status' => 200,
                'message' => 'Inprogress Build Updated Successfully',
                'data' => $in_progress,
                'morningPhotos' => $morningPhotos,
                'compliancePhotos' => $compliancePhotos,
                'completionPhotos' => $completionPhotos,
                // 'Photos' => $savedPhotos,

            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile()], 500);
        }
    }

    public function updateInprogress1(Request $request, $jobId)
    {
        // Validate Request
        $this->validate($request, [
            'build_start_date' => 'nullable|date_format:m/d/Y',
            'build_end_date' => 'nullable|date_format:m/d/Y',
            'notes' => 'nullable',
            // 'status' => 'nullable',

            //this is required photos section
            'images' => 'nullable|array',
            'images.morningPhotos' => 'nullable|array',
            'images.morningPhotos.*.label' => 'nullable|string',
            'images.morningPhotos.*.image' => 'nullable|file|mimes:jpg,jpeg,png',

            'images.compliancePhotos' => 'nullable|array',
            'images.compliancePhotos.*.label' => 'nullable|string',
            'images.compliancePhotos.*.image' => 'nullable|file|mimes:jpg,jpeg,png',

            'images.completionPhotos' => 'nullable|array',
            'images.completionPhotos.*.label' => 'nullable|string',
            'images.completionPhotos.*.image' => 'nullable|file|mimes:jpg,jpeg,png',

            //this is photo upload Fields Section
            // 'photos'=> 'nullable|array',
            // 'photos.*' => 'nullable|image',
            // 'labels' => 'nullable|array',
            // 'labels.*' => 'nullable|string',

            'production_sign_url' => 'nullable|string',
            'homeowner_signature' => 'nullable|string'
        ]);

        try {
            $job = CompanyJob::find($jobId);
            if (!$job) {
                return response()->json(['status' => 422, 'message' => 'Job not found'], 422);
            }

            // Update Inprogress
            $in_progress = Inprogress::updateOrCreate(
                ['company_job_id' => $jobId],
                [
                    'company_job_id' => $jobId,
                    'build_start_date' => $request->build_start_date,
                    'build_end_date' => $request->build_end_date,
                    'notes' => $request->notes,
                    // 'status' => $request->status,
                ]
            );

            // $savedPhotos = [];
            // $photos = $request->photos ?? [];

            // foreach($photos as $index => $image)
            // {
            //     $image_filename = time().'.'.$image->getClientOriginalName();
            //     $image_filePath = $image->storeAS('ProgressPhotos',$image_filename,'public');

            //     $media = new InprogressMedia;
            //     $media->company_job_id = $jobId;
            //     $media->labels = $request->labels[$index] ?? null;
            //     $media->image_path = Storage::url($image_filePath);
            //     $media->category = "photo";
            //     $media->save();

            //     $savedPhotos[] = [
            //         'id' => $media->id,
            //         'company_job_id' => $media->company_job_id,
            //         'labels' =>$media->labels,
            //         'image_path'=> $media->image_path,
            //         'created_at' => $media->created_at,
            //         'updated_at' => $media->updated_at,
            //         'category' => $media->category,
            //     ];
               
            // }
        // Save photos and store in their respective categories
            $morningPhotos = [];
            $compliancePhotos = [];
            $completionPhotos = [];

            $imageCategories = ['morningPhotos', 'compliancePhotos', 'completionPhotos'];
            foreach ($imageCategories as $category) {
                if (isset($request->images[$category]) && is_array($request->images[$category])) {
                    foreach ($request->images[$category] as $imageData) {
                        if (isset($imageData['image']) && $imageData['image']->isValid()) {
                            $filePath = $imageData['image']->store('public/inprogress_media');
                            $url = str_replace('public/', '/storage/', $filePath);

                            $media = new InprogressMedia();
                            $media->company_job_id = $jobId;
                            $media->labels = $imageData['label'] ?? null;
                            $media->image_path = $url;
                            $media->category = $category;
                            $media->save();

                            // Add to specific category arrays
                            if ($category == 'morningPhotos') {
                                $morningPhotos[] = [
                                    'labels' => $media->labels,
                                    'image_paths' => $url,
                                ];
                            } elseif ($category == 'compliancePhotos') {
                                $compliancePhotos[] = [
                                    'labels' => $media->labels,
                                    'image_paths' => $url,
                                ];
                            } elseif ($category == 'completionPhotos') {
                                $completionPhotos[] = [
                                    'labels' => $media->labels,
                                    'image_paths' => $url,
                                ];
                            }
                        }
                    }
                }
            }



            // Handle Base64 Signatures
            if ($request->production_sign_url) {
                $in_progress->production_sign_url = $this->saveBase64Image($request->production_sign_url, 'inprogress_signature');
            }
            if ($request->homeowner_signature) {
                $in_progress->homeowner_signature = $this->saveBase64Image($request->homeowner_signature, 'inprogress_signature');
            }

            // Generate and Save PDF
            $pdf = PDF::loadView('pdf.inprogress', ['data' => $in_progress, 'saved_photos' => $completionPhotos]);
            $pdf_fileName = time() . '.pdf';
            $pdf_filePath = 'inprogress_pdf/' . $pdf_fileName;

            if ($in_progress->pdf_url) {
                Storage::delete('public/' . str_replace('/storage/', '', $in_progress->pdf_url));
            }

            Storage::put('public/' . $pdf_filePath, $pdf->output());
            $in_progress->pdf_url = '/storage/' . $pdf_filePath;
            $in_progress->save();

            // Update Job Status
            if (isset($request->status) && $request->status == true) {
                $job->status_id = 11;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();
            }

            return response()->json([
                'status' => 200,
                'message' => 'Inprogress Build Updated Successfully',
                'data' => $in_progress,
                'morningPhotos' => $morningPhotos,
                'compliancePhotos' => $compliancePhotos,
                'completionPhotos' => $completionPhotos,
                // 'Photos' => $savedPhotos,

            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile()], 500);
        }
    }


/**
 * Save Base64 Encoded Image
 *
 * @param string $base64Image
 * @param string $directory
 * @return string
 */
    private function saveBase64Image($base64Image, $directory)
    {
        $data = substr($base64Image, strpos($base64Image, ',') + 1);
        $decodedImage = base64_decode($data);

        // Generate a unique filename
        $filename = 'image_' . time() . '.png';

        // Save the new image
        Storage::disk('public')->put($directory . '/' . $filename, $decodedImage);

        return '/storage/' . $directory . '/' . $filename;
    }


    public function addInprogressPhotos($jobId, Request $request)
    {
        $this->validate($request,[
            'photos'=> 'nullable|array',
            'photos.*' => 'nullable|image',
            'labels' => 'nullable|array',
            'labels.*' => 'nullable|string'
        ]);

        try{
            $savedPhotos = [];
            $photos = $request->photos ?? [];

            foreach($photos as $index => $image)
            {
                $image_filename = time().'.'.$image->getClientOriginalName();
                $image_filePath = $image->storeAS('ProgressPhotos',$image_filename,'public');

                $media = new InprogressMedia;
                $media->company_job_id = $jobId;
                $media->labels = $request->labels[$index] ?? null;
                $media->image_path = Storage::url($image_filePath);
                $media->save();

                $savedPhotos[] = [
                    'id' => $media->id,
                    'company_job_id' => $media->company_job_id,
                    'labels' =>$media->labels,
                    'image_path'=> $media->image_path,
                    'created_at' => $media->created_at,
                    'updated_at' => $media->updated_at,
                ];
               
            }
            return response()->json([
                'status' => 200,
                'message' => 'Inprogress Photos Added Successfully',
                'data' => $savedPhotos,
            ]);
        }catch(\Exception $e){

            return response()->json([
                'status' => 500,
                'message' => 'An issue occurred: ' . $e->getMessage(),
                'data' => [],
            ]);

        }


    }

    public function getInprogressPhotos($jobId)
    {
        $photos = InprogressMedia::where('company_job_id',$jobId)->get();
        if($photos->isNotEmpty())
        {
            return response()->json([
                'status' =>200,
                'message' =>'in progres media fetched successfully',
                'data' => $photos
            ]);
        }
        return response()->json([
            'status' =>200,
            'message' =>'in progres media Not Found',
            'data' => []
        ]);

    }
    
    public function updateInprogressStatus(Request $request, $jobId)
    {
        //Validate Request
        $this->validate($request, [
            'status' => 'nullable'
        ]);
        
        try {
            //Check Job
            $job = CompanyJob::find($jobId);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job not found'
                ], 422);
            }
            
            //Update Inprogress
            $in_progress = Inprogress::updateOrCreate([
                'company_job_id' => $jobId,
            ],[
                'status' => $request->status,
            ]);
            
            if(isset($request->status) && $request->status == true) {
                $job->status_id = 12;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();

                   //current stage save
                $in_progress->current_stage="yes";
                $in_progress->save();
            }elseif(isset($request->status) && $request->status == false) {
                $job->status_id = 11;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();

                 //current stage save
                 $in_progress->current_stage="no";
                 $in_progress->save();
            }
            
            return response()->json([
                'status' => 200,
                'message' => 'Inprogress Build status Updated Successfully',
                'data' => $in_progress
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }


    public function getInprogress($jobId)
    {
        try {
            
            //Check Job
            $job = CompanyJob::find($jobId);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job not found'
                ], 422);
            }
            
            $in_progress = Inprogress::where('company_job_id', $jobId)->first();
            if(!$in_progress) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Inprogress Build Not Yet Created'
                ], 422);
            }

            $photos = InprogressMedia::where('company_job_id',$jobId)->get();
            
            return response()->json([
                'status' => 200,
                'message' => 'Inprogress Build Found Successfully',
                'data' => $in_progress,
                'photos' => $photos
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function buildPacketSidebar($jobId, Request $request)
    {
        $company = CompanyJob::find($jobId);
        if(!$company)
        {
            return response()->json([
                'status' => 404,
                'message' => 'company not found',
                'data' => []
            ]);
        }

        $request->validate([
            'project_overview'=>'nullable|string|in:true,false',
            'scope_of_work'=>'nullable|string|in:true,false',
            'customer_preparation'=>'nullable|string|in:true,false',
            'photo_documentation'=>'nullable|string|in:true,false',
            'product_selection'=>'nullable|string|in:true,false',
            'authorization'=>'nullable|string|in:true,false',
            'terms_condition'=>'nullable|string|in:true,false',
        ]);

        try{
                $company_job_checklist = BuildPacketChecklist::updateOrCreate([
                    'company_job_id' => $jobId
                ],
                [
                    'company_job_id' => $jobId,
                    'project_overview' => $request->project_overview,
                    'scope_of_work' => $request->scope_of_work,
                    'customer_preparation' => $request->customer_preparation,
                    'photo_documentation' => $request->photo_documentation,
                    'product_selection' => $request->product_selection,
                    'authorization' => $request->authorization,
                    'terms_condition' => $request->terms_condition,
                ]
            );

                return response()->json([
                    'status' => 200,
                    'message' => 'Build packet sidebar updated successfully',
                    'data' => $company_job_checklist
                ]);
            }
        
        catch(\Exception $e){
             return response()->json([
                    'status' => 500,
                    'message' => 'Internal Server Error',
                    'error' => $e->getMessage()
                ]);
        }
        

    }

    public function getBuildPacketSidebar($jobId)
    {
        $company = CompanyJob::find($jobId);
        if(!$company)
        {
            return response()->json([
                'status' => 404,
                'message' => 'Company not Found',
                'data' => []
            ]);
        }

        //now check in buildpacket table
        $build_packet = BuildPacketChecklist::where('company_job_id',$jobId)->first();
        if($build_packet)
        {
            return response()->json([
                'status' => 404,
                'message' => 'Data Fetched Successfully',
                'data' => $build_packet
            ]);
        }
        return response()->json([
            'status' => 404,
            'message' => 'Build Packet Not Found',
            'data' => []
        ]);

    }

    public function markBuildPacketComplete($jobId, Request $request)
    {
        $request->validate([
            'status' => 'nullable|string|in:true,false'
        ]);

        $company = CompanyJob::find($jobId);
        if(!$company)
        {
            return response()->json([
                'status' => 404,
                'message' => 'Company not Found',
                'data' => []
            ]);
        }

        //now check in buildpacket table
        $build_packet = BuildPacketChecklist::where('company_job_id',$jobId)->first();
        if($build_packet && $request->status == "true")
        {
            $build_packet->is_complete = "true";
            $build_packet->status = "Build Complete";
            $build_packet->save();

            return response()->json([
                'status' => 404,
                'message' => 'Build Packet Updated',
                'data' => $build_packet
            ]);

        } else if($build_packet && $request->status == "false")
        {
            $build_packet->is_complete = "false";
            $build_packet->status = "Approved";
            $build_packet->save();

            return response()->json([
                'status' => 404,
                'message' => 'Build Packet Updated',
                'data' => $build_packet
            ]);

        }
        return response()->json([
            'status' => 404,
            'message' => 'Build Packet Not Found',
            'data' => []
        ]);
    }

    public function getProjectStatus($jobId)
    {
        $company = CompanyJob::find($jobId);
        if(!$company)
        {
            return response()->json([
                'status' => 404,
                'message' => 'Company not Found',
                'data' => []
            ]);
        }

        $build_packet = BuildPacketChecklist::select('id','company_job_id','is_complete','status','created_at','updated_at')->where('company_job_id',$jobId)->first();
        if($build_packet)
        {
            return response()->json([
                'status' => 404,
                'message' => 'Build Packet Fetched',
                'data' => $build_packet
            ]);
        }
        return response()->json([
            'status' => 404,
            'message' => 'Build Packet Not Found',
            'data' => $build_packet
        ]);

    }

    public function signBuildPacket($jobId, Request $request)
    {
        $this->validate($request, [
            'sign_image' => 'nullable',
        ]);

        try{
            $build_packet = BuildPacketChecklist::where('company_job_id',$jobId)->first();
            if(!$build_packet) {
                return response()->json([
                    'status' => 422,
                    'message' => 'build_packet Not Found',
                    'data' => []
                ], 422);
            }
             // Get base64 image data
             $base64Image = $request->input('sign_image');
             $data = substr($base64Image, strpos($base64Image, ',') + 1);
             $decodedImage = base64_decode($data);

             // Generate a unique filename
            $filename = 'image_' . time() . '.png';
            // Check if the old image exists and delete it
            if ($build_packet->sign_image_url) {
                $oldImagePath = public_path($build_packet->sign_image_url);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
             
            //save new image
            Storage::disk('public')->put('build_packet_sign/' . $filename, $decodedImage);
            $imageUrl = '/storage/build_packet_sign/' . $filename;

            //Save Image Path and Update the Status also
            $build_packet->sign_image_url = $imageUrl;
            $build_packet->status = "Ready to Build";
            $build_packet->save();

            // //Generate PDF
            // $pdf = PDF::loadView('pdf.build_packet', ['data' => $build_packet]);
            // $pdf_fileName = time() . '.pdf';
            // $pdf_filePath = 'build_packet_pdf/' . $pdf_fileName;
            // // Check if the old PDF exists and delete it
            // if ($build_packet->pdf_url) {
            //     $oldPdfPath = public_path($build_packet->sign_pdf_url);
            //     if (file_exists($oldPdfPath)) {
            //         unlink($oldPdfPath);
            //     }
            // }
            //  // Save the new PDF
            //  Storage::put('public/' . $pdf_filePath, $pdf->output());
            //  //Save PDF Path
            //  $build_packet->sign_pdf_url = '/storage/' . $pdf_filePath;
            //  $build_packet->save();

            //Update Job Status
            $job = CompanyJob::find($build_packet->company_job_id);
            $job->status_id = 9;
            $job->date = Carbon::now()->format('Y-m-d');
            $job->save();

            return response()->json([
                'status' => 200,
                'message' => 'Signature Added Successfully',
                'data' => $build_packet
            ], 200);


        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);

        }
    }


}
