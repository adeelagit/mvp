<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120', // max 5MB
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
            $path = $file->store('profiles', 'public'); // storage/app/public/profiles/..
            $data['profile_image'] = $path;
        }

        $data['password'] = Hash::make($data['password']);
        $verification_token = Str::random(64);
        $data['verification_token'] = $verification_token;
        $user = User::create($data);

        Mail::to($user->email)->send(new VerifyEmail($user));

        return response()->json([
            'message' => 'User registered successfully. Please verify your email',
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (is_null($user->email_verified_at)) {
            return response()->json(['message' => 'Please verify your email before login'], 403);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    /**
     * Verify email using token
     */
    public function verifyEmail($token)
    {
        $user = User::where('verification_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid or expired verification token.'], 400);
        }

        $user->update([
            'email_verified_at' => now(),
            'verification_token' => null
        ]);

        return response()->json(['message' => 'Email verified successfully! You can now log in.']);
    }

    /**
     * Log the user out (Invalidate the token).
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message'=>'Successfully logged out']);
        } catch (\Exception $e){
            return response()->json(['error'=>'Failed to logout'], 500);
        }
    }

    public function index(Request $request)
    {
        $users = User::all([
            'id', 
            'name', 
            'email', 
            'phone', 
            'profile_image', 
            'email_verified_at',
            'created_at'
        ]);

        return response()->json([
            'status' => 'success',
            'users' => $users,
        ]);
    }

    public function deleteUser($userId)
    {
        $user = user::find($userId);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Record not found',
            ], 404);
        }
        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'User deleted successfully',
        ], 200);
    }

}
