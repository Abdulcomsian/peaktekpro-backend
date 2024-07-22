<?php

namespace App\Http\Controllers;

use App\Models\CompanyJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\RoofComponentGeneric;
use Illuminate\Support\Facades\Storage;
use App\Models\RoofComponentGenericType;
use App\Models\RoofComponentGenericMedia;

class RoofComponentController extends Controller
{
    public function storeRoofComponent(Request $request, $jobId)
    {
        //Validate Request
        $this->validate($request, [
            'acknowledge' => 'nullable|in:0,1',
            'title' => 'required|string|in:My PDFs,Shared PDFs,Single Use PDFs,Text Page',
            'content' => $request->input('title') === 'Text Page' ? 'required' : 'nullable',
            'pdfs' => 'required|array|min:1',
            'pdfs.*' => 'required|mimes:pdf|max:2048',
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

            //Save Payment Schedule
            $component = RoofComponentGeneric::updateOrCreate([
                'company_job_id' => $job->id,
            ],[
                'company_job_id' => $job->id,
                'acknowledge' => $request->acknowledge
            ]);

            //Save Payment Schedule Type
            $component_type = RoofComponentGenericType::updateOrCreate([
                'roof_component_generic_id' => $component->id,
                'title' => $request->title,
            ],[
                'roof_component_generic_id' => $component->id,
                'title' => $request->title,
                'content' => $request->content
            ]);

            //Save Payment Schedule Media
            if ($request->hasFile('pdfs')) {
                // Remove old PDFs
                $oldAttachments = RoofComponentGenericMedia::where('type_id', $component_type->id)->get();
                foreach ($oldAttachments as $oldAttachment) {
                    $oldFilePath = str_replace('/storage', 'public', $oldAttachment->url);
                    Storage::delete($oldFilePath);
                    $oldAttachment->delete();
                }

                //Add New
                foreach ($request->file('pdfs') as $file) {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs('public/roof_component', $fileName);

                    // Store Path
                    $media = new RoofComponentGenericMedia();
                    $media->type_id = $component_type->id;
                    $media->pdf_url = Storage::url($filePath);
                    $media->save();
                }
            }

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => 'Roof Component Added Successfully',
                'data' => []
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getRoofComponent($jobId)
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

            $get_roof_component = RoofComponentGeneric::where('company_job_id', $jobId)->with('types.pdfs')->first();
            if(!$get_roof_component) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Roof Component Not Yet Created',
                    'data' => (object) []
                ], 200);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Roof Component Found Successfully',
                'data' => $get_roof_component
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
