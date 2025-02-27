<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function storeEmailTemplate($companyId, Request $request) //it is company id
    {
        $request->validate([
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

            $emailContent = EmailTemplate::updateOrCreate([
                'company_id' => $companyId,
            ],[
                'company_id' => $companyId,
                'content' => $request->content,
            ]);

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

    public function getEmailTemplate($companyId)
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
    
            $emailTemplate= EmailTemplate::where('company_id',$companyId)->first();
            if($emailTemplate)
            {
                return response()->json([
                    'status' => 200,
                    'message'=> 'Email Content Found Successfully',
                    'data' => $emailTemplate
                ]);
            }

        }catch(\Exception $e){
            return response()->json([
                'status' => 200,
                'message'=> $e->getMessage(),
            ]);
        }

    }
}
