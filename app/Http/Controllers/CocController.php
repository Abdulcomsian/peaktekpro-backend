<?php

namespace App\Http\Controllers;

use App\Models\Coc;
use App\Models\CompanyJob;
use Illuminate\Http\Request;

class CocController extends Controller
{
    public function storeCoc(Request $request, $jobId)
    {
        //Validate Rules
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:25',
            'street' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zip_code' => 'required',
            'insurance' => 'required',
            'claim_number' => 'required',
            'policy_number' => 'required',
            'company_representative' => 'required',
            'company_printed_name' => 'required',
            'company_signed_date' => 'required|date_format:d/m/Y',
            'job_total' => 'required',
            'customer_paid_upgrades' => 'required',
            'deductible' => 'required',
            'acv_check' => 'required',
            'rcv_check' => 'required',
            'supplemental_items' => 'required',
            'awarded_to' => 'required|string',
            'released_to' => 'required|string'
        ];

        // If updating an existing record, ignore the current record's email for uniqueness check
        $check_coc = Coc::where('company_job_id', $jobId)->first();
        if($check_coc) {
            $rules['email'] .= '|unique:cocs,email,' . $check_coc->id;
        } else {
            $rules['email'] .= '|unique:cocs,email';
        }

        // Validate the request
        $validatedData = $request->validate($rules, []);

        try {

            //Check Job
            $job = CompanyJob::find($jobId);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            //Update QC Inspection
            $coc = Coc::updateOrCreate([
                'company_job_id' => $jobId,
            ],[
                'company_job_id' => $jobId,
                'email' => $request->email,
                'name' => $request->name,
                'phone' => $request->phone,
                'street' => $request->street,
                'city' => $request->city,
                'state' => $request->state,
                'zip_code' => $request->zip_code,
                'insurance' => $request->insurance,
                'claim_number' => $request->claim_number,
                'policy_number' => $request->policy_number,
                'company_representative' => $request->company_representative,
                'company_printed_name' => $request->company_printed_name,
                'company_signed_date' => $request->company_signed_date,
                'job_total' => $request->job_total,
                'customer_paid_upgrades' => $request->customer_paid_upgrades,
                'deductible' => $request->deductible,
                'acv_check' => $request->acv_check,
                'rcv_check' => $request->rcv_check,
                'supplemental_items' => $request->supplemental_items,
                'awarded_to' => $request->awarded_to,
                'released_to' => $request->released_to,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'COC Added Successfully',
                'data' => $coc
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getCoc($jobId)
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

            $get_coc = Coc::where('company_job_id', $jobId)->first();
            if(is_null($get_coc)) {

                // Create a new stdClass object
                $coc = new \stdClass();
                $coc->name = $job->name;
                $coc->email = $job->email;
                $coc->phone = $job->phone;

                return response()->json([
                    'status' => 200,
                    'message' => 'COC Not Found',
                    'data' => $coc
                ], 200);
            }

            return response()->json([
                'status' => 200,
                'message' => 'COC Found Successfully',
                'data' => $get_coc
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
