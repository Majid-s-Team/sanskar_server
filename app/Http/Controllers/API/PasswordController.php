<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Mail\OtpMail;
class PasswordController extends Controller
{
    use ApiResponse;

    // public function forgotPassword(Request $request)
    // {
    //     $request->validate([
    //         'primary_email' => 'required|email'
    //     ]);

    //     $user = User::where('primary_email', $request->primary_email)->first();

    //     if (!$user) {
    //         return $this->error('User not found', 404);
    //     }

    //     $otp = rand(100000, 999999);
    //     $user->update([
    //         'otp' => $otp,
    //         'otp_expires_at' => Carbon::now()->addMinutes(10),
    //         'is_otp_verified' => false
    //     ]);

    //     // Uncomment in production
    //     // Mail::raw("Your OTP is: $otp", function ($message) use ($user) {
    //     //     $message->to($user->primary_email)->subject('Password Reset OTP');
    //     // });

    //     return $this->success([
    //         'message' => 'OTP sent to your email',
    //         'otp' => $otp 
    //     ]);
    // }
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'primary_email' => 'required|email'
        ]);

        $user = User::where('primary_email', $request->primary_email)->first();

        if (!$user) {
            return $this->error('User not found', 404);
        }
         if (!$user->is_payment_done) {
            return $this->error('You have not done the payment', 403);
        }

        $otp = rand(100000, 999999);
        $user->update([
            'otp' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(10),
            'is_otp_verified' => false
        ]);

        Mail::to($user->primary_email)->send(new OtpMail($otp, $user));

        return $this->success([], 'OTP sent to your email');
    }

    public function testEmail()
    {
        $fakeUser = (object)['name' => 'Test User', 'primary_email' => 'hasanraza132002@gmail.com'];
        $otp = rand(100000, 999999);

        Mail::to("hasanraza132002@gmail.com")->send(new OtpMail($otp, $fakeUser));

        return "Test email sent with OTP: $otp";
    }


    public function verifyOtp(Request $request)
    {
        $request->validate([
            'primary_email' => 'required|email',
            'otp'   => 'required|string',
        ]);

        $user = User::where('primary_email', $request->primary_email)->first();

        if (!$user || $user->otp !== $request->otp) {
            return $this->error('Invalid email or OTP');
        }

        if (Carbon::now()->gt(Carbon::parse($user->otp_expires_at))) {
            return $this->error('OTP expired');
        }

        $user->update(['is_otp_verified' => true]);

        return $this->success([], 'OTP verified successfully');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'primary_email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::where('primary_email', $request->primary_email)->first();

        if (!$user || !$user->is_otp_verified) {
            return $this->error('OTP not verified or user not found');
        }

        $user->update([
            'password' => Hash::make($request->password),
            'otp' => null,
            'otp_expires_at' => null,
            'is_otp_verified' => false,
        ]);

        return $this->success([], 'Password reset successfully');
    }

    public function changePassword(Request $request)
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid — user is null',
                'error_code' => 401
            ]);
        }

        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return $this->error('Old password is incorrect');
        }

        if (Hash::check($request->new_password, $user->password)) {
            return $this->error('New password cannot be the same as the old password');
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return $this->success([], 'Password changed successfully');
    }
}
