<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CrewEmailTemplate;
use App\Models\Company;

class CrewEmailTemplateController extends Controller
{
    public function storeCrewEmailTemplate($companyId, Request $request) //it is company id
    {
        $request->validate([
            'title' => 'string',
            'content'=> 'string'
        ]);

        try{
            $user = Auth()->user();
            // $companyId = $user->company_id;
            $company = Company::find($companyId); //check this company exist or not
            // dd($company);
            if(!$company)
            {
                return response()->json([
                    'status' => 404,
                    'message'=> 'Company Not Found'
                ]);
            }

            $emailContent = CrewEmailTemplate::updateOrCreate([
                'company_id' => $companyId,
            ],[
                'company_id' => $companyId,
                'content' => $request->content,
            ]);

            // $emailContent = EmailTemplate::create([
             
            //     'company_id' => $companyId,
            //     'title' => $request->title,
            //     'content' => $request->content,
            // ]);

            return response()->json([
                'status' => 200,
                'message'=> 'Email Content Added Successfully',
                'data' => $emailContent
            ]);

        }catch(\Exception $e){
            return response()->json([
                'status' => 200,
                'message'=> $e->getMessage(),
            ]);

        }
        
    }

    public function getCrewEmailTemplate($companyId)
    {
        try{
            $company = Company::find($companyId);
            if(!$company)
            {
                return response()->json([
                    'status' => 404,
                    'message'=> 'Company Not Found'
                ]);
            }
    
            $emailTemplate= CrewEmailTemplate::where('company_id',$companyId)->first();
            if($emailTemplate)
            {
                return response()->json([
                    'status' => 200,
                    'message'=> 'Email Content Found Successfully',
                    'data' => $emailTemplate
                ]);
            }
            return response()->json([
                'status' => 200,
                'message'=> ' No Email Content Found ',
            ]);

        }catch(\Exception $e){
            return response()->json([
                'status' => 200,
                'message'=> $e->getMessage(),
            ]);
        }

    }

    public function updateCrewEmailTemplate($companyId, Request $request)
    {
        $request->validate([
            'title' => 'string',
            'content' => 'string'
        ]);

        try {
            $company = Company::find($companyId);

            if (!$company) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Company Not Found'
                ]);
            }

            $emailTemplate = CrewEmailTemplate::where('company_id', $companyId)
                ->first();

            if (!$emailTemplate) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Email Template Not Found'
                ]);
            }

            // Update the email template
            $emailTemplate->update([
                'title' => $request->title,
                'content' => $request->content
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Email Template Updated Successfully',
                'data' => $emailTemplate
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function deleteCrewEmailTemplate($companyId)
    {
        try {
            $company = Company::find($companyId);

            if (!$company) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Company Not Found'
                ]);
            }

            $emailTemplate = CrewEmailTemplate::where('company_id', $companyId)
                ->first();

            if (!$emailTemplate) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Email Template Not Found'
                ]);
            }

            // Delete the email template
            $emailTemplate->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Email Template Deleted Successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
