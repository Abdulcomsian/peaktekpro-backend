<?php

namespace App\Http\Controllers;

use App\Models\Contractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Contractor\StoreRequest;
use App\Http\Requests\Contractor\UpdateRequest;
use Illuminate\Support\Facades\Auth;

class ContractorController extends Controller
{
    public function getContractor()
    {
          try{
            $user = Auth::user();
            $companyId = $user->company_id;
            $contractor = Contractor::where('company_id', $companyId)->get();

            return  response()->json([
                'msg' => 'Contractor Fetched Sucessfully',
                'status_code' => 200,
                'data' => $contractor
            ]);

        }catch(\Exception $e){

            return  response()->json([
                'msg' => 'Issue Occured' . $e->getMessage(),
                'status_code' => 500,
            ]);
        }
    }

    public function addContractor(StoreRequest $request)
    {
        try{
            $user = Auth::user();

            $companyId=$user->company_id;

            if($request->hasFile('file_path'))
            {
                $file = $request->file('file_path');
                $fileName = rand(0000,9999) . '_' . time(). '/' . $file->getClientOriginalName();
                $filePath = $file->storeAs('public/contractorCOI', $fileName);

            }

            $contractor = Contractor::create([
                'company_id' => $companyId,
                'name' => $request->name,
                'email' => $request->email,
                'company_name' => $request->company_name,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'subject' => $request->subject,
                'content' => $request->content,
                'file_path' => Storage::url($filePath),
            ]);

            return  response()->json([
                'msg' => 'Contractor Added Sucessfully',
                'status_code' => 200,
                'data' => $contractor
            ]);

        }catch(\Exception $e){

            return  response()->json([
                'msg' => 'Issue Occured' . $e->getMessage(),
                'status_code' => 500,
            ]);
        }
    }

    public function updateContractor(UpdateRequest $request,$id)
    {
        // dd([$request->all()]);
        try{
            $contractor = Contractor::find($id);
            $oldFile = $contractor->file_path;
            if (!$contractor) {
                return response()->json([
                    'msg' => 'Contractor not found',
                    'status_code' => 404,
                ]);
            }

            $filePath = $contractor->file_path;
            if($request->hasFile('file_path'))
            {
                  if ($contractor->file_path) {
                    $relativePath = str_replace('/storage/', '', $contractor->file_path);
                    Storage::delete('public/' . $relativePath);
                }
                $file = $request->file('file_path');
                $fileName = rand(0000,9999) . '_' . time(). '/' . $file->getClientOriginalName();
                $storedPath = $file->storeAs('public/contractorCOI', $fileName);
                $filePath = Storage::url($storedPath);

            }
            $user = Auth::user();

            $companyId=$user->company_id;
            $contractor->update([
                'company_id' => $companyId,
                'name' => $request->name,
                'email' => $request->email,
                'company_name' => $request->company_name,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'subject' => $request->subject,
                'content' => $request->content,
                'file_path' => $filePath,
            ]);

            return  response()->json([
                'msg' => 'Contractor Updated Sucessfully',
                'status_code' => 200,
                'data' => $contractor
            ]);

        }catch(\Exception $e){

            return  response()->json([
                'msg' => 'Issue Occured' . $e->getMessage(),
                'status_code' => 500,
            ]);
        }
    }

    public function deleteContractor($id)
    {
   try{
        $contractor = Contractor::find($id);

        if (!$contractor) {
            return response()->json([
                'msg' => 'Contractor not found',
                'status_code' => 404,
            ]);
        }

        $contractor->delete();

        return  response()->json([
            'msg' => 'Contractor Deleted Sucessfully',
            'status_code' => 200,
            'data' => []
        ]);

        }catch(\Exception $e){

            return  response()->json([
                'msg' => 'Issue Occured' . $e->getMessage(),
                'status_code' => 500,
            ]);
        }
    }
}
