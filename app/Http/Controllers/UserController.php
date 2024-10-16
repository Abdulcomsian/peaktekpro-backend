<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Auth;
use App\Enums\PermissionLevel;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{
    public function addUser(Request $request)
    {
        // dd($request->all());
        $this->validate($request, [
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'email' => 'nullable|string',
            'company_id' => 'nullable|exists:companies,id',
            // 'permission_level_id' => 'nullable|exists:roles,id',
            'permission_level_id' => 'nullable|integer|in:' . implode(',', array_column(PermissionLevel::cases(), 'value')),
            'status' => 'nullable|in:active,inactive',
        ]);

        try{
            $user = Auth::user();
            if($user->role_id == 2 || $user->role_id == 1)
            {
                $add_user = new User;
                $add_user->first_name = $request->first_name;
                $add_user->last_name = $request->last_name;
                $add_user->email = $request->email;
                // $add_user->company_id = $user->company_id; //logged in user company
                $add_user->company_id = $request->company_id;
                $add_user->role_id = $request->permission_level_id;
                $add_user->status = $request->status;
                $add_user->save();

                return response()->json([
                    'status_code'=> 200,
                    'status' => true,
                    'message' => 'User Created Succcessfully',
                    // 'data' => []
                ]);
            }else{
                return response()->json([
                    'status_code'=> 422,
                    'status' => true,
                    'message' => 'Not allowed',
                    // 'data' => []
                ]);
            }
        
        }catch(Exception $e){
                return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

        public function getUser()
        {
            try {
                $user = Auth::user();
            
                if($user->role_id == 2 || $user->role_id == 9 || $user->role_id == 1)
                {
                    $getusers = User::with('company')
                    ->where('company_id',$user->company_id)
                    ->get();
            
                    return response()->json([
                        'status_code' => 200,
                        'status' => true,
                        'data' => $getusers,
                    ]);
                }elseif($user->role_id == 7)
                {
                    $getusers =User::with('company')
                    ->get();
                    return response()->json([
                        'status_code' => 200,
                        'status' => true,
                        'data' => $getusers,
                    ]);
                }
                else{
                    return response()->json([
                        'status_code' => 200,
                        'status' => true,
                        'message' => 'Not Allowed',
                    ]);
                }
            } catch (Exception $e) {
                return response()->json([
                    'status_code' => 500,
                    'status' => false,
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

    public function updateUser(Request $request, $id)
    {
        // Validate the incoming request
        $this->validate($request, [
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'email' => 'nullable|string|email|unique:users,email,' . $id, // Unique check excluding current user
            'company_id' => 'nullable|exists:companies,id',
            'permission_level_id' => 'nullable|integer|in:' . implode(',', array_column(PermissionLevel::cases(), 'value')),
            'status' => 'nullable|in:active,inactive',
        ]);

        try {
            $user = Auth::user();
            if($user->role_id == 2 || $user->role_id == 1)
            {
                $update_user = User::findOrFail($id);

                // Update user
                $update_user->first_name = $request->first_name ?? $update_user->first_name;
                $update_user->last_name = $request->last_name ?? $update_user->last_name;
                $update_user->email = $request->email ?? $update_user->email;
                $update_user->company_id = $request->company_id ?? $update_user->company_id;
                $update_user->role_id = $request->permission_level_id ?? $update_user->role_id;
                $update_user->status = $request->status ?? $update_user->status;

                // Save changes
                $user->save();

                return response()->json([
                    'status_code' => 200,
                    'status' => true,
                    'message' => 'User updated successfully',
                    'data' => $user,
                ]);
            }else{
                return response()->json([
                    'status_code' => 422,
                    'status' => true,
                    'message' => 'Not Allowed',
                ]);
            }
            
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status_code' => 404,
                'status' => false,
                'message' => 'User not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function filterUsersByPermission(Request $request)
    {
        $this->validate($request, [
            'permission_level' => 'required|integer|in:' . implode(',', array_column(PermissionLevel::cases(), 'value')),
        ]);

        try {
            $user= Auth::user();
            if($user->role_id == 2 || $user->role_id == 9 || $user->role_id == 1)
            {
                $permissionLevel = $request->input('permission_level');
                // Filter users by the specified permission level
                $users = User::where('role_id', $permissionLevel)
                ->where('company_id',$user->company_id)
                ->get();
    
                return response()->json([
                    'status_code' => 200,
                    'status' => true,
                    'data' => $users,
                ]);
            }elseif($user->role_id == 7)
            {
                $permissionLevel = $request->input('permission_level');
                // Filter users by the specified permission level
                $users = User::where('role_id', $permissionLevel)
                ->get();
    
                return response()->json([
                    'status_code' => 200,
                    'status' => true,
                    'data' => $users,
                ]);
            }
            else{
                return response()->json([
                    'status_code' => 422,
                    'status' => true,
                    'message' => 'Not allowed',
                ]);
            }
           
            
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function searchUsers(Request $request)
    {
        $this->validate($request, [
            'search_term' => 'required|string|max:255',
        ]);

        try {
            $searchTerm = $request->input('search_term');

            $users = User::where(function($query) use ($searchTerm) {
                $query->where('first_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('email', 'LIKE', "%{$searchTerm}%");
            })->get();

            return response()->json([
                'status_code' => 200,
                'status' => true,
                'data' => $users,
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
}
