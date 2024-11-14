<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
class PaymentController extends Controller
{
    public function addPaymentHistory($jobId, Request $request)
    {
        try{
            $request->validate([
                'payment_date'=>'nullable|date',
                'payment_amount' => 'nullable',
                'payment_type' => 'nullable|in:check,credit_card,ACH,cash',
                'check_number' => 'nullable',
                'remaining_balance' => 'nullable',
    
            ]);
    
            $payment = new Payment;
            $payment->company_job_id = $jobId;
            $payment->payment_date = $request->payment_due;
            $payment->payment_amount = $request->payment_amount;
            $payment->payment_type = $request->payment_type;
            $payment->check_number = $request->check_number;
            $payment->remaining_balance = $request->remaining_balance;
            $payment->save();

            return response()->json([
                'status_code' =>200,
                'message' => 'Payment History Added Successfully',
                'data' => $payment
            ]);
        }catch(\Exception $e){
            return response()->json([
                'status_code' =>200,
                'message' => 'error occured while adding Payment History',
                'data' => $payment
            ]);
        }

    }
}
