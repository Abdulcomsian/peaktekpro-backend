<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\UserRole;
use App\Models\CompanyJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SupplierController extends Controller
{
    public function storeSupplierold(Request $request, $jobId) //it was old method where handle the supplier on the base of job_id
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

    public function storeSupplier(Request $request, $Id) //here handle on the base of company id
    {
        //Validate Request
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        try {

            //Check Job
            $job = Company::find($Id);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Company not found'
                ], 422);
            }   

            $user = Auth::user();
            $created_by = $user->company_id; //here we save the company id
            $name = explode(' ', $request->name, 2);
            $firstName = $name[0];
            $lastName = isset($name[1]) ? $name[1] : '';
            // Create a new user
            $user = User::create([
                'role_id' => 4,
                'name' => $request->name,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'company_id'=> $Id,
                'created_by'=> $created_by,
                'status' => 'active'
            ]);

            //Assign Role
            $user_role = new UserRole;
            $user_role->user_id = $user->id;
            $user_role->company_id = $Id;
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

    public function getSuppliers($Id)
    {
        try {

            //Check Job
            $job = Company::find($Id);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Company not found'
                ], 422);
            }

            $suppliers = User::where('role_id', 4)->whereHas('userRoles', function($query) use ($Id){
                $query->where('company_id', $Id); //if you want to get all supplier just commer this line
            })->get();

            if($suppliers->isEmpty())
            {
                return response()->json([
                    'status' => 404,
                    'message' => 'Suppliers not Found against this Company id',
                    'user' =>[],
                ], 404);
            }

            return response()->json([
                'status' => 201,
                'message' => 'Suppliers Found Successfully',
                'data' => $suppliers,
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getSuppliersold($jobId) //here checking on the base of company job
    {
        try {
            //Check Job
            $job = CompanyJob::find($jobId);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job not found'
                ], 422);
            }

            $suppliers = User::where('role_id', 4)->whereHas('userRoles', function($query) use ($jobId){
                $query->where('company_id', $jobId);
            })->get();

            if($suppliers->isEmpty())
            {
                return response()->json([
                    'status' => 404,
                    'message' => 'Suppliers not Found against this job id',
                    'user' => $suppliers,
                ], 404);

            }

            return response()->json([
                'status' => 201,
                'message' => 'Suppliers Found Successfully',
                'user' => $suppliers,
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
