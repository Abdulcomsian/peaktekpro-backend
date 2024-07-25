<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        //Validate Request
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        DB::beginTransaction();
        try {

            // Create a new user
            $user = User::create([
                'role_id' => 4,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            //Assign Role
            $user_role = new UserRole;
            $user_role->user_id = $user->id;
            $user_role->company_id = 1;
            $user_role->save();

            // Generate a token for the user
            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();
            return response()->json([
                'status' => 201,
                'message' => 'User Registered Successfully',
                'user' => $user,
                'token' => $token
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function login(Request $request)
    {
        //Validate Request
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        
        try {

            //Check on Email
            $userExists = User::where('email', $request->email)->exists();
            if (!$userExists) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Email Not Found'
                ], 422);
            }

            if (Auth::attempt($request->only('email', 'password'))) {
                $user = Auth::user();
                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'status' => 200,
                    'message' => 'Login Successfully',
                    'user' => $user,
                    'token' => $token
                ], 200);
            }

            // Authentication failed
            return response()->json([
                'message' => 'Invalid Credentials'
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getUser()
    {
        $user = Auth::user();
        return response()->json(['status' => 200, 'user' => $user]);
    }

    public function checkOldPassword(Request $request)
    {
        //Validate Request
        $this->validate($request, [
            'old_password' => 'required'
        ]);

        try {

            $user = Auth::user();
            $old_password = $request->old_password;
            $user_password = $user->password;
            if(Hash::check($old_password, $user_password)) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Old Password Match Successfully',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'status' => 400,
                'message' => 'Old Password Did Not Match',
            ], 400);


        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        //Validate Request
        $this->validate($request, [
            'name' => 'nullable|string|max:255',
            'profile' => 'nullable|image',
            'new_password' => 'nullable|string|min:8',
        ]);
        
        try {
            $user = Auth::user();
            //Update Name
            if(isset($request->name)) {
                $user->name = $request->name;
            }
            
            //Update Profile
            if(isset($request->profile)){
                //Remove Old Profile
                if(!is_null($user->profile)) {
                    $oldFilePath = str_replace('/storage', 'public', $user->profile);
                    Storage::delete($oldFilePath);
                }

                //Store New Profile
                $file = $request->file('profile');
                $fileName = $file->getClientOriginalName();
                $fileName = time() . '_' . $fileName;
                $path = $file->storeAs('public/profiles', $fileName);

                $user->profile = Storage::url($path);
            }

            //Update Password
            if(isset($request->new_password)) {
                $user->password = Hash::make($request->new_password);
            }

            $user->save();

            return response()->json([
                'status' => 200,
                'message' => 'Profile Updated Successfully',
                'data' => $user
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Logout Successfully',
                'data' => []
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
