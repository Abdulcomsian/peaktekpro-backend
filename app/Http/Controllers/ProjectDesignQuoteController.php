<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Section;
use App\Models\CompanyJob;
use Illuminate\Http\Request;
use App\Models\ProjectDesignQuote;
use Illuminate\Support\Facades\DB;

class ProjectDesignQuoteController extends Controller
{
    public function storeProjectDesignQuote(Request $request, $jobId)
    {
        //Validate Request
        $this->validate($request, [
            'profit_margin' => 'required',
            'quote_sub_total' => 'required',
            'quote_total' => 'required',
            'notes' => 'nullable',
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

            // Store Quote
            $quote = ProjectDesignQuote::updateOrCreate([
                'company_job_id' => $jobId,
            ],[
                'company_job_id' => $jobId,
                'profit_margin' => $request->profit_margin,
                'quote_sub_total' => $request->quote_sub_total,
                'quote_total' => $request->quote_total,
                'notes' => $request->notes,
            ]);


            foreach($request->sections as $section)
            {
                //Store Section
                $add_section = new Section;
                $add_section->quote_id = $quote->id;
                $add_section->title = $section['title'];
                $add_section->section_total = $section['section_total'];
                $add_section->save();

                //Store Items
                foreach($section['items']  as $item)
                {
                    $add_item = new Item;
                    $add_item->section_id = $add_section->id;
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
                'message' => 'Quote Details Added Successfully',
                'data' => []
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getProjectDesignQuote($jobId)
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

            $get_quote = ProjectDesignQuote::where('company_job_id', $jobId)->with('sections.items')->first();

            return response()->json([
                'status' => 200,
                'message' => 'Quote Details Found Successfully',
                'data' => $get_quote
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function updateSectionStatus(Request $request, $jobId)
    {
        //Validate Request
        $this->validate($request, [
            'quote_id' => 'required|integer',
            'section_id' => 'required|integer',
            'status' => 'required|in:0,1'
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
            $get_quote = ProjectDesignQuote::find($request->quote_id);
            if(!$get_quote) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Quote Details Not Found'
                ], 422);
            }

            //Check Section
            $get_section = Section::where('id', $request->section_id)->where('quote_id', $request->quote_id)->first();
            if(!$get_section) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Section Not Found'
                ], 422);
            }

            $get_section->status = $request->status;
            $get_section->save();

            return response()->json([
                'status' => 200,
                'message' => 'Status Updated Successfully',
                'data' => []
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
