<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Check if user session is active
     *
     * @return JsonResponse
     */
    public function session()
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            if (! $user) {
                return response()->json([
                    'authenticated' => false,
                ], 401);
            }

            return response()->json([
                'data' => [
                    'authenticated' => true,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'data' => [
                    'authenticated' => false,
                    'message' => 'Server error: '.$e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Refresh authentication token
     *
     * @return JsonResponse
     */
    public function refresh()
    {
        try {
            $token = Auth::refresh();

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to refresh token: '.$e->getMessage()], 500);
        }
    }

    /**
     * Register a new user.
     *
     * @unauthenticated
     *
     * @return JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }
        $validated = $validator->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'data' => $user,
        ], 201);
    }

    /**
     * Login user and create token.
     *
     * @unauthenticated
     *
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'message' => 'Authentication failed',
                'errors' => ['email' => ['This email is not registered. Please sign up first.']],
            ], 404);
        }

        // Email ada, cek password
        if (! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Authentication failed',
                'errors' => ['password' => ['Password is incorrect.']],
            ], 401);
        }
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'data' => $user,
        ], 200);
    }

    /**
     * Forgot password
     *
     * @unauthenticated
     *
     * @return JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ], [
            'email.required' => 'Email is required.',
            'email.email' => 'Email format is invalid.',
            'email.exists' => 'Email is not registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            $user->update([
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'message' => 'Password updated successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Server error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get authenticated user data
     *
     * @return JsonResponse
     */
    public function me()
    {
        $authUser = Auth::user();

        if (! $authUser) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'message' => 'User found',
            'data' => $authUser,
        ], 200);
    }

    /**
     * Logout user
     *
     * @return JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }
}
