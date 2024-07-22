<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserRole;
use App\Models\CompanyJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SupplierController extends Controller
{
    public function storeSupplier(Request $request, $jobId)
    {
        //Validate Request
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        try {

            //Check Job
            $job = CompanyJob::find($jobId);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job not found'
                ], 422);
            }

            // Create a new user
            $user = User::create([
                'role_id' => 3,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            //Assign Role
            $user_role = new UserRole;
            $user_role->user_id = $user->id;
            $user_role->company_id = $jobId;
            $user_role->save();

            return response()->json([
                'status' => 201,
                'message' => 'Supplier Added Successfully',
                'user' => $user,
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
