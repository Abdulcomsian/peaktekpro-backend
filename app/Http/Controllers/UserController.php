<?php

namespace App\Http\Controllers;

use Auth;
use Mail;
use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Enums\PermissionLevel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Mail\UserPasswordMail;

class UserController extends Controller
{
    public function getUser()
    {
        try {
            $user = Auth::user();
        
            if($user->role_id == 2 || $user->role_id == 9 || $user->role_id == 1 || $user->role_id == 8)
            {
                $getusers = User::with('company')
                ->where('company_id',$user->company_id)
                ->whereIn('role_id',[2,8,9])
                // ->whereNotIn('role_id',[7,2,1])
                ->get();
        
                return response()->json([
                    'status_code' => 200,
                    'status' => true,
                    'data' => $getusers,
                ]);
            }elseif($user->role_id == 7)
            {
                $getusers =User::with('company')
                ->whereIn('role_id',[2,8,9])
                // ->whereNotIn('role_id',[7])
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

    public function addUser(Request $request)
    {
        try{
            $this->validate($request, [
                    'first_name' => 'nullable|string',
                    'last_name' => 'nullable|string',
                    'email' => 'nullable|string|unique:users,email',
                    'company_id' => 'nullable|exists:companies,id',
                    // 'permission_level_id' => 'nullable|exists:roles,id',
                    'password' => 'required|string', 
                    'confirm_password' => 'required|string|same:password',
                    'permission_level_id' => 'nullable|integer|in:' . implode(',', array_column(PermissionLevel::cases(), 'value')),
                    'status' => 'nullable|in:active,inactive',
                ]);
            }catch (ValidationException $e) {
                return response()->json([
                    'status_code' => 422,
                    'status' => false,
                    'errors' => $e->validator->errors(),
                ], 422);
            }
        
        try{
            $user = Auth::user();
            if($user->role_id == 2 || $user->role_id == 1)
            {
                $password = $request->password;
                $add_user = new User;
                $add_user->first_name = $request->first_name;
                $add_user->last_name = $request->last_name;
                $add_user->name = $request->first_name.' '.$request->last_name;
                $add_user->email = $request->email;
                // $add_user->company_id = $user->company_id; //logged in user company
                $add_user->company_id = $request->company_id;
                $add_user->role_id = $request->permission_level_id;
                $add_user->status = $request->status;
                $add_user->password =  Hash::make($password);

                $add_user->save();

                // Send the password email
                // Mail::to($request->email)->send(new UserPasswordMail($request->email, $add_user->password));
            
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
    
    public function updateUser(Request $request, $id)
    {
        // dd($id);
        // Validate the incoming request
        try {
            $this->validate($request, [
                'first_name' => 'nullable|string',
                'last_name' => 'nullable|string',
                'email' => 'nullable|string|email|unique:users,email,' . $id, // Exclude current user's email
                'company_id' => 'nullable|exists:companies,id',
                'permission_level_id' => 'nullable|integer|in:' . implode(',', array_column(PermissionLevel::cases(), 'value')),
                'status' => 'nullable|in:active,inactive',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status_code' => 422,
                'status' => false,
                'errors' => $e->validator->errors(),
            ], 422);
        }

        try {
            $user = Auth::user();

            if ($user->role_id == 2 || $user->role_id == 1) {
                $update_user = User::findOrFail($id);
                // dd($update_user);

                // Update user details
                $update_user->first_name = $request->first_name ?? $update_user->first_name;
                $update_user->last_name = $request->last_name ?? $update_user->last_name;
                $update_user->email = $request->email ?? $update_user->email; // This line is fine
                $update_user->company_id = $request->company_id ?? $update_user->company_id;
                $update_user->role_id = $request->permission_level_id ?? $update_user->role_id;
                $update_user->status = $request->status ?? $update_user->status;

                // Save changes
                $update_user->save();

                return response()->json([
                    'status_code' => 200,
                    'status' => true,
                    'message' => 'User updated successfully',
                    'data' => $update_user,
                ]);
            } else {
                return response()->json([
                    'status_code' => 422,
                    'status' => false,
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
        try{
            $this->validate($request, [
                'permission_level' => 'required|integer|in:' . implode(',', array_column(PermissionLevel::cases(), 'value')),
            ]);

            $user= Auth::user();
            if($user->role_id == 2 || $user->role_id == 9 || $user->role_id == 1)
            {
                $permissionLevel = $request->input('permission_level');
                // Filter users by the specified permission level
                $users = User::with('company')->where('role_id', $permissionLevel)
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
                $users = User::with('company')->where('role_id', $permissionLevel)
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
            
        } catch (ValidationException $e) {
            return response()->json([
                'status_code' => 422,
                'status' => false,
                'message' => $e->validator->errors(),
            ], 422);
        }
        catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status' => false,
                'message' => $e->getMessage(),
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
            $user = Auth::user();
            
            $usersQuery = User::with('company')->where(function($query) use ($searchTerm) {
                $query->where('first_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('email', 'LIKE', "%{$searchTerm}%");
            });

            if (in_array($user->role_id, [1, 2])) {
                $users = $usersQuery->whereNotIn('role_id', [7, 1])->get();
            } elseif ($user->role_id == 7) {
                $users = $usersQuery->whereNotIn('role_id', [7])->get();
            } else {
                $users = collect(); // Return an empty collection if no roles match
            }
            
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
