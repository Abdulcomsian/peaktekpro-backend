<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\CompanyJob;
use App\Models\CustomerAgreement;
use App\Jobs\SignEmailJob;

class CustomerAgreementController extends Controller
{
    public function customerAgreement(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'street' => 'required',
                'city' => 'required',
                'state' => 'required',
                'zip_code' => 'required',
                'insurance' => 'required',
                'claim_number' => 'required',
                'policy_number' => 'required',
                'company_signature' => 'required',
                'company_printed_name' => 'required',
                'company_date' => 'required|date',
                'customer_signature' => 'required',
                'customer_printed_name' => 'required',
                'customer_date' => 'required|date',
            ]);

            $job = CompanyJob::find($id);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            $agreement = new CustomerAgreement;
            $agreement->company_job_id = $id;
            $agreement->street = $request->street;
            $agreement->city = $request->city;
            $agreement->state = $request->state;
            $agreement->zip_code = $request->zip_code;
            $agreement->insurance = $request->insurance;
            $agreement->claim_number = $request->claim_number;
            $agreement->policy_number = $request->policy_number;
            $agreement->company_signature = $request->company_signature;
            $agreement->company_printed_name = $request->company_printed_name;
            $agreement->company_date = $request->company_date;
            $agreement->customer_signature = $request->customer_signature;
            $agreement->customer_printed_name = $request->customer_printed_name;
            $agreement->customer_date = $request->customer_date;
            $agreement->save();

            return response()->json([
                'status' => 200,
                'message' => 'Agreement Created Successfully',
                'agreement' => $agreement
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getCustomerAgreement($id)
    {
        try {
            $agreement = CustomerAgreement::find($id);
            if(!$agreement) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Agreement Not Found'
                ], 422);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Agreement Found Successfully',
                'agreement' => $agreement
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateCustomerAgreement(Request $request, $id)
    {
        $this->validate($request, [
            'sign_image' => 'required',
        ]);

        $agreement = CustomerAgreement::find($id);
        if(!$agreement) {
            return response()->json([
                'status' => 422,
                'message' => 'Agreement Not Found'
            ], 422);
        }

        // Get base64 image data
        $base64Image = $request->input('sign_image');
        $data = substr($base64Image, strpos($base64Image, ',') + 1);
        $decodedImage = base64_decode($data);

        // Generate a unique filename
        $filename = 'image_' . time() . '.png';
        Storage::disk('public')->put('customer_agreement_signature/' . $filename, $decodedImage);
        $imageUrl = '/storage/customer_agreement_signature/' . $filename;

        $agreement->sign_image_url = $imageUrl;
        $agreement->save();

        //Update Job Status
        $job = CompanyJob::find($agreement->company_job_id);
        $job->status_id = 2;
        $job->save();

        return response()->json([
            'status' => 200,
            'message' => 'Signature Image Added Successfully',
            'agreement' => $agreement
        ], 200);

    }

    public function signCustomerAgreementByEmail($id)
    {
        $agreement = CustomerAgreement::find($id);
        if(!$agreement) {
            return response()->json([
                'status' => 422,
                'message' => 'Agreement Not Found'
            ], 422);
        }

        $customer = CompanyJob::find($agreement->company_job_id);
        dispatch(new SignEmailJob($customer));

        return response()->json([
            'status' => 200,
            'message' => 'Email Sent Successfully',
        ], 200);
    }
}
