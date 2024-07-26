<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use App\Models\User;
use App\Jobs\SendOTPJob;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends Controller
{
    public function sendOTP(Request $request)
    {
        //Validate Request
        $this->validate($request, [
            'email' => 'required|email'
        ]);

        try {

            $user = User::where('email', $request->email)->first();
            if(!$user) {
                return response()->json([
                    'status' => 422,
                    'message' => 'This Email Is Not Registered In Our Record'
                ], 422);
            }

            //Create OTP
            $otp = mt_rand(1000, 9999);
            Otp::updateOrCreate([
                'user_id' => $user->id,
            ],[
                'user_id' => $user->id,
                'otp' => $otp
            ]);

            //Send Email
            dispatch(new SendOTPJob($user,$otp));

            return response()->json([
                'status' => 200,
                'message' => 'OTP Sent Successfully',
                'data' => []
            ], 200); 

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function verifyOTP(Request $request)
    {
        //Validate Request
        $this->validate($request, [
            'otp' => 'required',
            'email' => 'required|email'
        ]);

        try {

            $user = User::where('email', $request->email)->first();
            if(!$user) {
                return response()->json([
                    'status' => 422,
                    'message' => 'This Email Is Not Registered In Our Record'
                ], 422);
            }

            //Verify OTP
            $otp = Otp::where('otp', $request->otp)->where('user_id', $user->id)->first();
            if($otp->otp == $request->otp) {
                return response()->json([
                    'status' => 200,
                    'message' => 'OTP Verify Successfully',
                    'data' => []
                ], 200); 
            }

            return response()->json([
                'status' => 422,
                'message' => 'OTP Incorrect',
                'data' => []
            ], 422); 

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function changePassword(Request $request)
    {
        //Validate Request
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|string|min:8'
        ]);

        try {

            $user = User::where('email', $request->email)->first();
            if(!$user) {
                return response()->json([
                    'status' => 422,
                    'message' => 'This Email Is Not Registered In Our Record'
                ], 422);
            }

            //Update Password
            $user->password = Hash::make($request->password);
            $user->save();

            //Delete OTP
            $otp = Otp::where('user_id', $user->id)->first();
            if($otp) {
                $otp->delete();
            }

            return response()->json([
                'status' => 200,
                'message' => 'Password Updated Successfully',
                'data' => []
            ], 200); 

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
