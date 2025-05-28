<?php

namespace App\Http\Controllers;

use App\Http\Resources\EmailTemplateResource;
use App\Models\Company;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use App\Models\User;
class EmailTemplateController extends Controller
{
    public function storeEmailTemplate($companyId, Request $request) //it is company id
    {
        $request->validate([
            'title' => 'nullable|string',
            'content'=> 'string',
            'supplier_id' =>'integer',

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

            $user = User::where('id',$request->supplier_id)
            ->first();
            if(!$user)
            {
                return response()->json([
                    'status' => 404,
                    'message'=> 'supplier Not Found'
                ]);
            }

            $supplier = User::where('id',$request->supplier_id)->first();
            $emailContent = EmailTemplate::create([
             
                'company_id' => $companyId,
                'title' => $supplier->name,
                'content' => $request->content,
                'supplier_id'=> $request->supplier_id,
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
    
            $emailTemplate= EmailTemplate::where('company_id',$companyId)->get();
            if($emailTemplate)
            {
                return response()->json([
                    'status' => 200,
                    'message'=> 'Email Content Found Successfully',
                    'data' => EmailTemplateResource::collection($emailTemplate)
                ]);
            }

        }catch(\Exception $e){
            return response()->json([
                'status' => 200,
                'message'=> $e->getMessage(),
            ]);
        }

    }

    public function updateEmailTemplate($companyId, $templateId, Request $request)
    {
        $request->validate([
            'title' => 'string',
            'content' => 'string',
            'supplier_id' => 'integer',
        ]);

        try {
            $company = Company::find($companyId);

            if (!$company) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Company Not Found'
                ]);
            }

            $user = User::where('id',$request->supplier_id)
            ->first();
            if(!$user)
            {
                return response()->json([
                    'status' => 404,
                    'message'=> 'supplier Not Found'
                ]);
            }

            $emailTemplate = EmailTemplate::where('company_id', $companyId)
                ->where('id', $templateId)
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
                'content' => $request->content,
                'supplier_id'=> $request->supplier_id,
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

    public function deleteEmailTemplate($companyId, $templateId)
    {
        try {
            $company = Company::find($companyId);

            if (!$company) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Company Not Found'
                ]);
            }

            $emailTemplate = EmailTemplate::where('company_id', $companyId)
                ->where('id', $templateId)
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
