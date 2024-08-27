<?php

namespace App\Http\Controllers;

use App\Models\CompanyJob;
use Illuminate\Http\Request;
use App\Models\AuthorizationItem;
use Illuminate\Support\Facades\DB;
use App\Models\AuthorizationSection;
use App\Models\ProjectDesignAuthorization;

class ProjectDesignAuthorizationController extends Controller
{
    public function storeProjectDesignAuthorization(Request $request, $jobId)
    {
        //Validate Request
        $this->validate($request, [
            'disclaimer' => 'required',
            'signer_first_name' => 'required|string',
            'signer_last_name' => 'required|string',
            'signer_email' => 'required|email',
            'footer_notes' => 'required',
            'item1' => 'required|string',
            'item2' => 'required|string',
            'item3' => 'required|string',
            'section1' => 'required|string',
            'section2' => 'required|string',
            'section3' => 'required|string',
            'sections' => 'required|array',
            'sections.*.title' => 'required|string',
            'sections.*.section_total' => 'required',
            'sections.*.items' => 'required|array',
            'sections.*.items.*.item' => 'required|string',
            'sections.*.items.*.quantity' => 'required',
            'sections.*.items.*.price' => 'required',
            'sections.*.items.*.line_total' => 'required',
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

            // Store Authorization
            $authorization = ProjectDesignAuthorization::updateOrCreate([
                'company_job_id' => $jobId,
            ],[
                'company_job_id' => $jobId,
                'disclaimer' => $request->disclaimer,
                'signer_first_name' => $request->signer_first_name,
                'signer_last_name' => $request->signer_last_name,
                'signer_email' => $request->signer_email,
                'footer_notes' => $request->footer_notes,
                'item1' => $request->item1,
                'item2' => $request->item2,
                'item3' => $request->item3,
                'section1' => $request->section1,
                'section2' => $request->section2,
                'section3' => $request->section3,
            ]);


            foreach($request->sections as $section)
            {
                //Store Section
                if(isset($section['id'])) {
                    $update_section = AuthorizationSection::find($section['id']);
                    if($update_section) {
                        $update_section->authorization_id = $authorization->id;
                        $update_section->title = $section['title'];
                        $update_section->section_total = $section['section_total'];
                        $update_section->save();
                        
                        //Store Items
                        foreach($section['items']  as $item)
                        {
                            if(isset($item['id'])) {
                                $update_item = AuthorizationItem::find($item['id']);
                                if($update_item) {
                                    $update_item->authorization_section_id = $update_section->id;
                                    $update_item->item = $item['item'];
                                    $update_item->quantity = $item['quantity'];
                                    $update_item->price = $item['price'];
                                    $update_item->line_total = $item['line_total'];
                                    $update_item->save();    
                                }
                            } else {
                                $add_item = new AuthorizationItem;
                                $add_item->authorization_section_id = $update_section->id;
                                $add_item->item = $item['item'];
                                $add_item->quantity = $item['quantity'];
                                $add_item->price = $item['price'];
                                $add_item->line_total = $item['line_total'];
                                $add_item->save();   
                            }
                        }
                    }
                    
                } else {
                    $add_section = new AuthorizationSection;
                    $add_section->authorization_id = $authorization->id;
                    $add_section->title = $section['title'];
                    $add_section->section_total = $section['section_total'];
                    $add_section->save();
    
                    //Store Items
                    foreach($section['items']  as $item)
                    {
                        $add_item = new AuthorizationItem;
                        $add_item->authorization_section_id = $add_section->id;
                        $add_item->item = $item['item'];
                        $add_item->quantity = $item['quantity'];
                        $add_item->price = $item['price'];
                        $add_item->line_total = $item['line_total'];
                        $add_item->save();
                    }   
                }
            }

            DB::commit();
            return response()->json([
                'status' => 201,
                'message' => 'Authorization Added Successfully',
                'data' => []
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getProjectDesignAuthorization($jobId)
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

            $get_authorization = ProjectDesignAuthorization::where('company_job_id', $jobId)->with('sections.items')->first();
            if(!$get_authorization) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Authorization Not Yet Created',
                ], 422);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Authorization Found Successfully',
                'data' => $get_authorization
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    
    public function deleteAuthorizationSection(Request $request, $jobId)
    {
        $this->validate($request, [
            'section_id' => 'required|integer'
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
            
            //Check Quote
            $check_authorization = ProjectDesignAuthorization::where('company_job_id', $jobId)->first();
            if(!$check_authorization) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Authorization Not Found'
                ], 422);
            }
            
            //Check Section
            $section = AuthorizationSection::where('id', $request->section_id)->where('authorization_id', $check_authorization->id)->with('items')->first();
            if(!$section) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Section Not Found'
                ], 422);   
            }
            
            //Delete Section
            $section->items()->delete();
            $section->delete();
            
            $get_authorization = ProjectDesignAuthorization::where('company_job_id', $jobId)->with('sections.items')->first();
            
            return response()->json([
                'status' => 200,
                'message' => 'Section Deleted Successfully',
                'data' => $get_authorization
            ], 200);
            
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    
    public function deleteAuthorizationItem(Request $request, $jobId)
    {
        $this->validate($request, [
            'section_id' => 'required|integer',
            'item_id' => 'required|integer'
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
            
            //Check Quote
            $check_authorization = ProjectDesignAuthorization::where('company_job_id', $jobId)->first();
            if(!$check_authorization) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Authorization Not Found'
                ], 422);
            }
            
            //Check Section
            $section = AuthorizationSection::where('id', $request->section_id)->where('authorization_id', $check_authorization->id)->first();
            if(!$section) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Section Not Found'
                ], 422);   
            }
            
            //Check Item
            $item = AuthorizationItem::where('id', $request->item_id)->where('authorization_section_id', $request->section_id)->first();
            if(!$item) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Section Item Not Found'
                ], 422);   
            }
            
            //Delete Item
            $item->delete();
            
            $get_authorization = ProjectDesignAuthorization::where('company_job_id', $jobId)->with('sections.items')->first();
            
            return response()->json([
                'status' => 200,
                'message' => 'Item Deleted Successfully',
                'data' => $get_authorization
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
