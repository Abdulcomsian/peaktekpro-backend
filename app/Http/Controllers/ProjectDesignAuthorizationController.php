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
            'signer_email' => 'required|email|unique:project_design_authorizations,signer_email',
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

            return response()->json([
                'status' => 200,
                'message' => 'Authorization Found Successfully',
                'data' => $get_authorization
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
