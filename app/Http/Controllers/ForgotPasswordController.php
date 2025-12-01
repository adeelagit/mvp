<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetOtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    // 1️⃣ Send OTP to email
    public function sendResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $otp = rand(100000, 999999);

        $user->update([
            'password_reset_otp' => $otp,
            'password_reset_otp_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new PasswordResetOtpMail($user));

        return response()->json([
            'message' => 'OTP sent successfully to your email.'
        ]);
    }

    // 2️⃣ Verify OTP before resetting password
    public function verifyResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|digits:6'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if ($user->password_reset_otp !== $request->otp) {
            return response()->json(['message' => 'Invalid OTP.'], 400);
        }

        if ($user->password_reset_otp_expires_at->isPast()) {
            return response()->json(['message' => 'OTP expired.'], 400);
        }

        return response()->json(['message' => 'OTP verified successfully.']);
    }

    // 3️⃣ Reset password after OTP verified
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'otp'      => 'required|digits:6',
            'password' => 'required|min:6|confirmed'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if ($user->password_reset_otp !== $request->otp) {
            return response()->json(['message' => 'Invalid OTP.'], 400);
        }

        if ($user->password_reset_otp_expires_at->isPast()) {
            return response()->json(['message' => 'OTP expired.'], 400);
        }

        // Reset password
        $user->update([
            'password' => Hash::make($request->password),
            'password_reset_otp' => null,
            'password_reset_otp_expires_at' => null,
        ]);

        return response()->json([
            'message' => 'Password reset successfully.'
        ]);
    }
}
