<?php

namespace App\Http\Controllers;

use App\Models\CompanyJob;
use Illuminate\Http\Request;
use App\Models\PaymentSchedule;
use Illuminate\Support\Facades\DB;
use App\Models\PaymentScheduleMedia;
use Illuminate\Support\Facades\Storage;

class PaymentScheduleController extends Controller
{
    public function storePaymentSchedule(Request $request, $jobId)
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
            $schedule = PaymentSchedule::updateOrCreate([
                'company_job_id' => $job->id,
            ],[
                'company_job_id' => $job->id,
                'acknowledge' => $request->acknowledge,
                'title' => $request->title,
                'content' => $request->content
            ]);

            //Save Payment Schedule Type
            // $schedule_type = PaymentScheduleType::updateOrCreate([
            //     'payment_schedule_id' => $schedule->id,
            //     'title' => $request->title,
            // ],[
            //     'payment_schedule_id' => $schedule->id,
            //     'title' => $request->title,
            //     'content' => $request->content
            // ]);

            //Save Payment Schedule Media
            if ($request->hasFile('pdfs')) {
                // Remove old PDFs
                $oldAttachments = PaymentScheduleMedia::where('payment_schedule_id', $schedule->id)->get();
                foreach ($oldAttachments as $oldAttachment) {
                    $oldFilePath = str_replace('/storage', 'public', $oldAttachment->url);
                    Storage::delete($oldFilePath);
                    $oldAttachment->delete();
                }

                //Add New
                foreach ($request->file('pdfs') as $file) {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs('public/payment_schedule', $fileName);

                    // Store Path
                    $media = new PaymentScheduleMedia();
                    $media->payment_schedule_id = $schedule->id;
                    $media->pdf_url = Storage::url($filePath);
                    $media->save();
                }
            }

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => 'Payment Schedule Added Successfully',
                'data' => []
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getPaymentSchedule($jobId)
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

            $get_payment_schedule = PaymentSchedule::where('company_job_id', $jobId)->with('pdfs')->first();
            if(!$get_payment_schedule) {
                return response()->json([
                    'status' => 200, 
                    'message' => 'Payment Schedule Not Yet Created',
                    'data' => (object) []
                ], 200);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Payment Schedule Found Successfully',
                'data' => $get_payment_schedule
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
