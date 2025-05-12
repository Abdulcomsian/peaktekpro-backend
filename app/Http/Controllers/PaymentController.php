<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\CompanyJobSummary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{

    public function addPaymentHistory($jobId, Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'payment_date' => 'nullable|date',
                'payment_amount' => 'required|numeric|min:1',
                'payment_type' => 'nullable|in:cheque,credit_card,ACH,cash',
                'check_number' => 'nullable',
                'attachment' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,txt',
                'file_name' => 'nullable|string',
            ]);

            $paid_amount = (int) $request->payment_amount;
            $filePath = null;

            // Handle existing file if needed
            // $insurance = Payment::where('company_job_id', $jobId)->first();
            // $existingFilePath = $insurance ? $insurance->pdf_path : null;

            // if ($request->hasFile('attachment')) {
            //     if ($existingFilePath) {
            //         $oldFilePath = str_replace('/storage/', 'public/', $existingFilePath);
            //         Storage::delete($oldFilePath);
            //     }

            //     $document = $request->file('attachment');
            //     $fileName = uniqid() . '_' . $document->getClientOriginalName();
            //     // $filePath = $document->storeAs('public/insurance_under_review', $fileName);
            //     $filePath = $document->storeAs('ClaimDetailsDocument', $fileName, 'public');

            // } else {
            //     $filePath = $existingFilePath;
            // }

            $filePath = null;

            if ($request->hasFile('attachment')) {
                $document = $request->file('attachment');
                $fileName = uniqid() . '_' . $document->getClientOriginalName();
                $filePath = $document->storeAs('ClaimDetailsDocument', $fileName, 'public');
            }


            // Get job summary
            $job_balance = CompanyJobSummary::where('company_job_id', $jobId)->first();
            if (!$job_balance) {
                return response()->json([
                    'status_code' => 401,
                    'message' => 'Job Summary Not Found',
                ]);
            }

            $job_total = (int) ($job_balance->job_total ?? 0);
            if (!$job_total) {
                return response()->json([
                    'status_code' => 401,
                    'message' => 'Job total value Not Found',
                ]);
            }

            // Calculate total paid so far (BEFORE this payment)
            $total_paid_before = Payment::where('company_job_id', $jobId)->sum('payment_amount');

            // Total after this payment
            $total_paid = $total_paid_before + $paid_amount;

            // Remaining balance
            $remaining_balance = $job_total - $total_paid;

            if ($remaining_balance < 0) {
                return response()->json([
                    'status_code' => 401,
                    'message' => 'Payment exceeds the remaining balance',
                ]);
            }

            // Save new payment
            $payment = new Payment();
            $payment->company_job_id = $jobId;
            $payment->payment_date = $request->payment_date;
            $payment->payment_amount = $paid_amount;
            $payment->payment_type = $request->payment_type;
            $payment->check_number = $request->check_number;
            $payment->pdf_path = $filePath ? Storage::url($filePath) : null;
            $payment->file_name = $request->file_name;
            $payment->remaining_balance = $remaining_balance;
            $payment->save();

            // Update job summary
            $job_balance->balance = $remaining_balance;
            $job_balance->is_fully_paid = $remaining_balance == 0 ? 'yes' : 'no';
            $job_balance->full_payment_date = $remaining_balance == 0 ? Carbon::now()->format('d/m/y') : null;
            $job_balance->save();

            DB::commit();

            return response()->json([
                'status_code' => 200,
                'message' => 'Payment Added Successfully',
                'data' => [
                    'id' => $payment->id,
                    'company_job_id' => $payment->company_job_id,
                    'payment_date' => $payment->payment_date,
                    'payment_amount' => $payment->payment_amount,
                    'payment_type' => $payment->payment_type,
                    'check_number' => $payment->check_number,
                    'remaining_balance' => $remaining_balance,
                    'is_fully_paid' => $job_balance->is_fully_paid,
                    'full_payment_date' => $job_balance->full_payment_date,
                    'attachment' => $payment->pdf_path,
                    'file_name' => $payment->file_name,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 500,
                'message' => 'Error occurred while adding Payment History',
                'error' => $e->getMessage()
            ]);
        }
    }


    public function deletePaymentHistory($jobId)
    {
        $payment = Payment::find($jobId);
        $payment->delete();

        return response()->json([
            'status_code'=> 200,
            'msg' => 'Deleted Successfully'
        ]);
    }

    public function getPaymentHistory($jobId)
    {
        $payment = Payment::where('company_job_id',$jobId)->get();
        if($payment)
        {
            $message = "Payment data Fetched Successfully";
            return response()->json([
                'status_code' =>200,
                'message' => $message,
                'data' => $payment
            ]);
        }
        
        $message = "Payment data Not Found";

        return response()->json([
            'status_code' =>200,
            'message' => $message,
        ]);
    }
}
