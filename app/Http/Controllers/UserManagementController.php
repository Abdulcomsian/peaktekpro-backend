<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserRole;
use App\Models\CompanyJob;
use App\Models\Company;
use App\Models\Role;

class UserManagementController extends Controller
{
    public function addUser(Request $request)
    {
        //Validate Request
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'required|integer|exists:roles,id',
            'company_id' => 'required|integer|exists:companies,id',
            'status' => 'required|string|in:active,inactive'
        ]);
        
        try {
            
            $user = Auth::user();
            if($user->role_id == 1 || $user->role_id == 2)
            {
                
                // Create a new user
                $create_user = User::create([
                    'role_id' => $request->role_id,
                    'company_id' => $request->company_id,
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'created_by' => $request->company_id,
                    'status' => $request->status
                ]);
    
                //Assign Role
                $user_role = UserRole::updateOrCreate([
                    'user_id' => $create_user->id,
                    'company_id' => $request->company_id
                ],[
                    'user_id' => $create_user->id,
                    'company_id' => $request->company_id
                ]);
                
                return response()->json([
                    'status' => 201,
                    'message' => 'User Created Successfully',
                    'data' => $create_user,
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
    
    public function getUser($id)
    {
        try {
            
            $user = Auth::user();
            if($user->role_id == 1 || $user->role_id == 2)
            {
                $get_user = User::whereId($id)->with('role')->first();
                
                return response()->json([
                    'status' => 200,
                    'message' => 'User Found Successfully',
                    'data' => $get_user,
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
    
    public function updateUser(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $id,
            'role_id' => 'required|integer|exists:roles,id',
            'company_id' => 'required|integer|exists:companies,id',
            'status' => 'required|string|in:active,inactive'
        ]);
        
        try {
            
            $user = Auth::user();
            if($user->role_id == 1 || $user->role_id == 2)
            {
                
                //update user
                $update_user = User::find($id);
                $update_user->role_id = $request->role_id;
                $update_user->company_id = $request->company_id;
                $update_user->email = $request->email;
                $update_user->name = $request->name;
                $update_user->created_by = $request->company_id;
                $update_user->status = $request->status;
                $update_user->save();
                
                $user_role = UserRole::updateOrCreate([
                    'user_id' => $update_user->id,
                    'company_id' => $request->company_id
                ],[
                    'user_id' => $update_user->id,
                    'company_id' => $request->company_id
                ]);
                
                return response()->json([
                    'status' => 200,
                    'message' => 'User Updated Successfully',
                    'data' => $update_user,
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
    
    public function deleteUser($id)
    {
        try {
            
            $user = Auth::user();
            if($user->role_id == 1 || $user->role_id == 2)
            {
                $get_user = User::whereId($id)->with('role')->first();
                $get_jobs = $get_user->jobs()->pluck('company_job_id');
                if(count($get_jobs) > 0) {
                    return response()->json([
                        'status' => 422,
                        'message' => 'User cant be deleted because jobs are associated with it!',
                    ], 422);
                }
                $get_user->delete();
                
                return response()->json([
                    'status' => 200,
                    'message' => 'User Deleted Successfully',
                    'data' => $get_user,
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
    
    public function getCompanies()
    {
        try {
            $user = Auth::user();
            if($user->role_id == 1 || $user->role_id == 2)
            {
                $companies = Company::all();
                
                 return response()->json([
                    'status' => 200,
                    'message' => 'Companies Found Successfully',
                    'data' => $companies,
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
    
    public function getRoles()
    {
        try {
            $user = Auth::user();
            if($user->role_id == 1 || $user->role_id == 2)
            {
                $roles = Role::all();
                
                 return response()->json([
                    'status' => 200,
                    'message' => 'Roles Found Successfully',
                    'data' => $roles,
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
}
