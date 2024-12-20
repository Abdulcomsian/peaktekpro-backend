<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\CompanyJobSummary;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function addPaymentHistory($jobId, Request $request)
    {
        try{
            DB::beginTransaction();
            $request->validate([
                'payment_date'=>'nullable|date',
                'payment_amount' => 'nullable',
                'payment_type' => 'nullable|in:cheque,credit_card,ACH,cash',
                'check_number' => 'nullable',
                // 'remaining_balance' => 'nullable',
    
            ]);

                $payment = new Payment;
                $payment->company_job_id = $jobId;
                $payment->payment_date = $request->payment_date;
                $payment->payment_amount = $request->payment_amount;
                $payment->payment_type = $request->payment_type;
                $payment->check_number = $request->check_number;
                // $payment->remaining_balance = $request->remaining_balance;
                $payment->save();

                $message = "Payment Added Successfully";

                $paid_amount = $request->payment_amount;
                $job_balance = CompanyJobSummary::where('company_job_id', $jobId)->first();
                if (!$job_balance) {
                    return response()->json([
                        'status_code' =>401,
                        'message' => 'Jon Summary Not Found',
                    ]);                
                }
                $job_total = $job_balance->balance;
                $job_total_value = $job_balance->job_total;

                if(!$job_total_value)
                {
                    return response()->json([
                        'status_code' =>401,
                        'message' => 'Job total value Not Found',
                    ]);  
                }
                // dd($job_total);
              
                $remaining_balance = $job_total-$paid_amount;
                $payment->remaining_balance = $remaining_balance;
                $payment->save();

                if ($remaining_balance < 0) {
                    return response()->json([
                        'status_code' =>401,
                        'message' => 'Payment exceeds more then remining balance',
                    ]);                
                }

                if($paid_amount==0)
                {
                    return response()->json([
                        'status_code' =>401,
                        'message' => 'Payment value will be more then 0',
                    ]);
                }

                $conpany_job_summaries = CompanyJobSummary::updateOrCreate([
                    'company_job_id' => $jobId,
                ],
                [
                    'company_job_id' => $jobId,
                    'balance' => $remaining_balance,
                    'is_fully_paid' => 'no',
                    'full_payment_date' => null

                ]);

                if($remaining_balance == 0)
                {
                    $conpany_job_summaries->is_fully_paid = "yes";
                    $conpany_job_summaries->full_payment_date =Carbon::now()->format('d/m/y');
                    $conpany_job_summaries->save();
                }

                $response=[
                    'id' => $payment->id,
                    'company_job_id'=> $payment->company_job_id,
                    'payment_date'=> $payment->payment_date,
                    'payment_amount'=> $payment->payment_amount,
                    'payment_type' => $payment->payment_type,
                    'check_number' => $payment->check_number,
                    'remaining_balance' => $remaining_balance,
                    'is_fully_paid' => $conpany_job_summaries->is_fully_paid,
                    'full_payment_date' => $conpany_job_summaries->full_payment_date,
                ];

                DB::commit();
            return response()->json([
                'status_code' =>200,
                'message' => $message,
                'data' => $response
            ]);
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json([
                'status_code' =>200,
                'message' => 'error occured while adding Payment History',
                'message' => $e->getMessage()
            ]);
        }

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
