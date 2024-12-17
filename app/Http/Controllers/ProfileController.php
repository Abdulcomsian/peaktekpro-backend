<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\OverheadPercentage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function updateProfile(Request $request,$id)
    {
        //Validate Request
        $this->validate($request, [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'market_segment' => 'nullable|string|max:255',
            'profile_image' => 'nullable|image'
        ]);

        try { 

            $path = null;
           
            if($request->hasFile('profile_image'))
            {
                $user = Auth::user();
                if (!empty($user->profile_image)) {
                    $existingImagePath = storage_path('app/public/user_profile_image/' . $user->profile_image);
                    
                    // Delete the existing image file
                    if (file_exists($existingImagePath)) {
                        unlink($existingImagePath);
                    }
                }

                $image = $request->file('profile_image');
                $imageName = time(). '.'.$image->getClientOriginalExtension();
                $path = $image->storeAs('public/user_profile_image', $imageName);
            }else {
                $user = User::find($id);
                $path = $user->profile_image; 
            }
            $user= User::find($id);
            $user->first_name=$request->first_name ?? $user->first_name;
            $user->last_name=$request->last_name ?? $user->last_name;
            $user->email=$request->email ?? $user->email;
            $user->phone=$request->phone ?? $user->phone;
            $user->job_title=$request->job_title ?? $user->job_title;
            $user->market_segment=$request->market_segment ?? $user->market_segment;
            $user->profile_image = Storage::url($path);

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


    public function changePassword(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        // ], [
        //     'new_password.confirmed' => 'The new password and confirmation do not match.',
        ]);
    
        // Check if the current password is correct
        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return response()->json([
                'status' => false,
                'status_code' => 422,
                'message' => 'The provided password does not match our records.',
            ]);
        }
    
        // Change the password
        Auth::user()->update(['password' => Hash::make($request->new_password)]);
    
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Password changed successfully.',
        ]);
    }
    

    public function addOverheadPercentage(Request $request)
    {
        $request->validate([
            'overhead_percentage'=> 'nullable'
        ]);

        $percentage = OverheadPercentage::query()->update(
        [
        'overhead_percentage'=> $request->overhead_percentage,
        ]);
        
        $updatedData = OverheadPercentage::first();
        $message = 'Data Saved Successfully';
        return response()->json([
            'status'=> 200,
            'message' => $message,
            'data' => $updatedData
        ]);
    }
}
