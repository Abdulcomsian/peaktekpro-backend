<?php

namespace App\Http\Controllers;

use App\Models\CompanyJob;
use Illuminate\Http\Request;
use App\Models\XactimateReport;
use Illuminate\Support\Facades\DB;
use App\Models\XactimateReportMedia;
use Illuminate\Support\Facades\Storage;

class XactimateReportController extends Controller
{
    public function storeXactimateReport(Request $request, $jobId)
    {
        //Validate Request
        $this->validate($request, [
            'acknowledge' => 'nullable|in:0,1',
            'title' => 'nullable|string|in:My PDFs,Shared PDFs,Single Use PDFs,Text Page',
            'content' => $request->input('title') === 'Text Page' ? 'required' : 'nullable',
            'pdfs' => 'nullable|array|min:1',
            'pdfs.*' => 'nullable|mimes:pdf|max:2048',
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
                'acknowledge' => $request->acknowledge,
                'title' => $request->title,
                'content' => $request->content
            ]);

            //Save Payment Schedule Media
            if ($request->hasFile('pdfs')) {

                //Add New
                foreach ($request->file('pdfs') as $file) {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs('public/xactimate-report', $fileName);

                    // Store Path
                    $media = new XactimateReportMedia();
                    $media->xactimate_report_id = $report->id;
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

            $get_xactimate_report = XactimateReport::where('company_job_id', $jobId)->with('pdfs')->first();
            if(!$get_xactimate_report) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Xactimate Report Not Yet Created',
                ], 422);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Xactimate Report Found Successfully',
                'data' => $get_xactimate_report
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    
    public function changeXactimateReportFileName(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'file_name' => 'required|string'
        ]);

        try {

            //Check Xactimate Report
            $check_xactimate_report = XactimateReportMedia::find($id);
            if(!$check_xactimate_report) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Xactimate Report Not Found'
                ], 422);
            }

            //Update File Name
            $check_xactimate_report->file_name = $request->file_name;
            $check_xactimate_report->save();

            return response()->json([
                'status' => 200,
                'message' => 'File Name Updated Successfully',
                'data' => []
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    
    public function deleteXactimateReportMedia(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'image_url' => 'required|string'
        ]);

        try {

            //Check Xactimate Report
            $check_xactimate_report = XactimateReportMedia::find($id);
            if(!$check_xactimate_report) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Xactimate Report Not Found'
                ], 422);
            }

            //Delete Media
            $oldImagePath = str_replace('/storage', 'public', $check_xactimate_report->pdf_url);
            Storage::delete($oldImagePath);
            $check_xactimate_report->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Media Deleted Successfully',
                'data' => $check_xactimate_report
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
