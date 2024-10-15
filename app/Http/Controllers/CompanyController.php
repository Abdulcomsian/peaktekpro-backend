<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Company;
use App\Models\UserRole;
use Illuminate\Validation\Rule;
use App\Enums\PermissionLevel;

class CompanyController extends Controller
{
    public function createCompany(Request $request)
    {
        //Validate Request
        $this->validate($request, [
            'name' => 'required|string',
            'website' => 'required|string',
            'site_admin_name' => 'required|string',
            'site_admin_email' => 'required|email|unique:users,email',
            'permission_level' => 'nullable|integer|in:' . implode(',', array_column(PermissionLevel::cases(), 'value')),
            'status' => 'required|string|in:active,inactive'
        ]);
        
        try {

            $user = Auth::user();
            if($user->role_id == 7 || $user->role_id == 2 )
            {
                // Create Company
                $company = new Company;
                $company->name = $request->name;
                $company->website = $request->website;
                $company->save();
        
                // Create a new user
                $create_user = User::create([
                    // 'role_id' => 1,
                    'company_id' => $company->id,
                    'name' => $request->site_admin_name,
                    'email' => $request->site_admin_email,
                    'password' => Hash::make('Abc@123!'),
                    'role_id'=>$request->permission_level,
                    'created_by' => $company->id,
                    'status' => $request->status
                ]);
    
                //Assign Role
                $user_role = UserRole::updateOrCreate([
                    'user_id' => $create_user->id,
                    'company_id' => $company->id
                ],[
                    'user_id' => $create_user->id,
                    'company_id' => $company->id
                ]);
                
                return response()->json([
                    'status' => 201,
                    'message' => 'Company Created Successfully',
                    'data' => $company,
                ], 201);
            }
            
            return response()->json([
                'status' => 422,
                'message' => 'Permission Denied!',
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    // public function createCompany1(Request $request)
    // {
    //     //Validate Request
    //     $this->validate($request, [
    //         'name' => 'required|string',
    //         'website' => 'required|string',
    //         'site_admin_name' => 'required|string',
    //         'site_admin_email' => 'required|email|unique:users,email',
    //         'status' => 'required|string|in:active,inactive'
    //     ]);
        
    //     try {
            
    //         $user = Auth::user();
    //         if($user->role_id == 7)
    //         {
    //             // Create Company
    //             $company = new Company;
    //             $company->name = $request->name;
    //             $company->website = $request->website;
    //             $company->save();
        
    //             // Create a new user
    //             $create_user = User::create([
    //                 'role_id' => 1,
    //                 'company_id' => $company->id,
    //                 'name' => $request->site_admin_name,
    //                 'email' => $request->site_admin_email,
    //                 'password' => Hash::make('Abc@123!'),
    //                 'created_by' => $company->id,
    //                 'status' => $request->status
    //             ]);
    
    //             //Assign Role
    //             $user_role = UserRole::updateOrCreate([
    //                 'user_id' => $create_user->id,
    //                 'company_id' => $company->id
    //             ],[
    //                 'user_id' => $create_user->id,
    //                 'company_id' => $company->id
    //             ]);
                
    //             return response()->json([
    //                 'status' => 201,
    //                 'message' => 'Company Created Successfully',
    //                 'data' => $company,
    //             ], 201);
    //         }
            
    //         return response()->json([
    //             'status' => 422,
    //             'message' => 'Permission Denied!',
    //         ], 422);
            
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
    //     }
    // }
    
    public function getCompany($id)
    {
        try {
            
            $user = Auth::user();
            if($user->role_id == 7)
            {
                $company = Company::find($id);
                if(!$company) {
                    return response()->json([
                        'status' => 422,
                        'message' => 'Company Not Found',
                    ], 422);
                }
                
                return response()->json([
                    'status' => 201,
                    'message' => 'Company Found Successfully',
                    'data' => $company,
                ], 201);
            }
            
            return response()->json([
                'status' => 422,
                'message' => 'Permission Denied!',
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    
    public function updateCompany(Request $request, $id)
    {
        
        try {
            
            $user = Auth::user();
            if($user->role_id == 7)
            {
                // Check Company
                $company = Company::find($id);
                if(!$company) {
                    return response()->json([
                        'status' => 422,
                        'message' => 'Company Not Found',
                    ], 422);
                }
                
                $update_user = User::where('company_id', $id)->first();
                
                $this->validate($request, [
                    'name' => 'required|string',
                    'website' => 'required|string',
                    'site_admin_name' => 'required|string',
                    'site_admin_email' => 'required|email|unique:users,email,' . $update_user->id,
                    'status' => 'required|string|in:active,inactive'
                ]);
                
                // Update Company
                $company->name = $request->name;
                $company->website = $request->website;
                $company->save();
        
                // Update user
                if($update_user) {
                    $update_user->name = $request->site_admin_name;
                    $update_user->email = $request->site_admin_email;
                    $update_user->status = $request->status;
                    $update_user->save();
                }
                
                return response()->json([
                    'status' => 200,
                    'message' => 'Company Updated Successfully',
                    'data' => $company,
                ], 200);
            }
            
            return response()->json([
                'status' => 422,
                'message' => 'Permission Denied!',
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    
    public function getCompanyUsers(Request $request)
    {
        try {

            $user = Auth::user();
            // Determine the company ID
            $companyId = ($user->created_by == 0) ? 1 : $user->created_by;

            // Get pagination parameters
            $results = $request->input('results', 15);
            $page = $request->input('page', 1);

            // Calculate the offset for skipping
            $skipped = ($page - 1) * $results;

            $usersQuery = User::where('created_by', $companyId)
                ->where('role_id', 5)->with('role');

            if (isset($request->results) && isset($request->page)) {
                $users = $usersQuery->skip($skipped)->take($results)->get();
            } else {
                $users = $usersQuery->get();
            }

            return response()->json([
                'status' => 200,
                'message' => 'Users Found Successfully',
                'data' => $users
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getCompanySubContractors(Request $request)
    {
        try {
            $data = [];

            $user = Auth::user();
            // Determine the company ID
            $companyId = ($user->created_by == 0) ? 1 : $user->created_by;

            // Get pagination parameters
            $results = $request->input('results', 15);
            $page = $request->input('page', 1);

            // Calculate the offset for skipping
            $skipped = ($page - 1) * $results;

            $usersQuery = User::where('created_by', $companyId)
                ->where('role_id', 3)->with('role');

            if (isset($request->results) && isset($request->page)) {
                $users = $usersQuery->skip($skipped)->take($results)->get();
            } else {
                $users = $usersQuery->get();
            }

            return response()->json([
                'status' => 200,
                'message' => 'Sub Contractors Found Successfully',
                'data' => $users
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getCompanySuppliers(Request $request)
    {
        try {

            $user = Auth::user();
            // Determine the company ID
            $companyId = ($user->created_by == 0) ? 1 : $user->created_by;

            // Get pagination parameters
            $results = $request->input('results', 15);
            $page = $request->input('page', 1);

            // Calculate the offset for skipping
            $skipped = ($page - 1) * $results;

            $usersQuery = User::where('created_by', $companyId)
                ->where('role_id', 4)->with('role');

            if (isset($request->results) && isset($request->page)) {
                $users = $usersQuery->skip($skipped)->take($results)->get();
            } else {
                $users = $usersQuery->get();
            }

            return response()->json([
                'status' => 200,
                'message' => 'Suppliers Found Successfully',
                'data' => $users
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getCompanyAdjustors(Request $request)
    {
        try {

            $user = Auth::user();
            // Determine the company ID
            $companyId = ($user->created_by == 0) ? 1 : $user->created_by;

            // Get pagination parameters
            $results = $request->input('results', 15);
            $page = $request->input('page', 1);

            // Calculate the offset for skipping
            $skipped = ($page - 1) * $results;

            $usersQuery = User::where('created_by', $companyId)
                ->where('role_id', 6)->with('role');

            if (isset($request->results) && isset($request->page)) {
                $users = $usersQuery->skip($skipped)->take($results)->get();
            } else {
                $users = $usersQuery->get();
            }

            return response()->json([
                'status' => 200,
                'message' => 'Adjustors Found Successfully',
                'data' => $users
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
