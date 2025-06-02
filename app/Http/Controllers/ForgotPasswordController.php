<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Otp;
use App\Models\User;
use App\Jobs\SendOTPJob;
use App\Mail\SendOTPMail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    public function sendOTP1(Request $request)
    {
        // dd("Sds");
        //Validate Request
        $this->validate($request, [
            'email' => 'required|email'
        ]);

        try {

            $user = User::where('email', $request->email)->first();
            if(!$user) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Email is not registered in our record'
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
            // dispatch(new SendOTPJob($user,$otp));
            Mail::to($user->email)->send(new SendOTPMail($user, $otp));

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
                    'message' => 'Email is not registered in our record'
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

      public function sendOTP(Request $request)
    {
        // dd("Sds");
        //Validate Request
        $this->validate($request, [
            'email' => 'required|email'
        ]);

        try {

            $user = User::where('email', $request->email)->first();
            if(!$user) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Email is not registered in our record'
                ], 422);
            }

            //Generate Random token
            $token = Str::random(64);

             // Store the token with the email in the password_resets table
            DB::table('password_resets')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => $token,
                    'created_at' => Carbon::now()
                ]
            );
        

            //Send Email
            // dispatch(new SendOTPJob($user,$otp));
            Mail::to($user->email)->send(new SendOTPMail($user,$token));

            return response()->json([
                'status' => 200,
                'message' => 'Reset Link Sent Successfully',
                'data' => []
            ], 200); 

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function changePassword(Request $request)
    {
        //Validate Request
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|string|min:8',
        ]);

        try {

            $user = User::where('email', $request->email)->first();
            if(!$user) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Email is not registered in our record' //According to Basit Khattak 
                ], 422);
            }

            if($request->password != $request->confirm_password) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Password & Confirm Password did not match'
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

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|confirmed|min:8'
        ]);

        $record = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Invalid token or email'], 400);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password reset successfully']);
    }

}
