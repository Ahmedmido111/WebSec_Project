<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user and create token
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            // Basic validation
            if (!$request->email || !$request->password) {
                return response()->json([
                    'message' => 'Email and password are required'
                ], 422);
            }

            // Find user by email
            $user = User::where('email', $request->email)->first();

            // Check if user exists and password is correct
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Generate token without revoking existing ones first
            $token = $user->createToken('api-token')->plainTextToken;

            // Return user data and token
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'token' => $token
            ]);
        } catch (\Exception $e) {
            // Detailed error response for debugging
            return response()->json([
                'message' => 'Login failed',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Get all users (requires authentication)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function users()
    {
        try {
            $users = User::all();
            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user (revoke token)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Successfully logged out'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during logout',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
