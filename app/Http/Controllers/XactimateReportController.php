<?php

namespace App\Http\Controllers;

use App\Models\CompanyJob;
use Illuminate\Http\Request;
use App\Models\XactimateReport;
use Illuminate\Support\Facades\DB;
use App\Models\XactimateReportType;
use App\Models\XactimateReportMedia;
use Illuminate\Support\Facades\Storage;

class XactimateReportController extends Controller
{
    public function storeXactimateReport(Request $request, $jobId)
    {
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
            $report = XactimateReport::updateOrCreate([
                'company_job_id' => $job->id,
            ],[
                'company_job_id' => $job->id,
                'acknowledge' => $request->acknowledge
            ]);

            //Save Payment Schedule Type
            $report_type = XactimateReportType::updateOrCreate([
                'xactimate_report_id' => $report->id,
                'title' => $request->title,
            ],[
                'xactimate_report_id' => $report->id,
                'title' => $request->title,
                'content' => $request->content
            ]);

            //Save Payment Schedule Media
            if ($request->hasFile('pdfs')) {
                // Remove old PDFs
                $oldAttachments = XactimateReportMedia::where('type_id', $report_type->id)->get();
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
                    $media = new XactimateReportMedia();
                    $media->type_id = $report_type->id;
                    $media->pdf_url = Storage::url($filePath);
                    $media->save();
                }
            }

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => 'Xactimate Report Added Successfully',
                'data' => []
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getXactimateReport($jobId)
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

            $get_xactimate_report = XactimateReport::where('company_job_id', $jobId)->with('types.pdfs')->first();

            return response()->json([
                'status' => 200,
                'message' => 'Xactimate Report Found Successfully',
                'data' => $get_xactimate_report
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
