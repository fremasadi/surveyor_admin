<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Handle the login request for surveyors only.
     */
    public function login(Request $request)
    {
        // Validate the request data
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Find user by email
        $user = User::where('email', $credentials['email'])->first();

        // Check if user exists, role is surveyor, and password is correct
        if (!$user || $user->role !== 'surveyor' || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect or the user is not authorized.'],
            ]);
        }

        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'data' => [
                'userId' => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
            ],
            'token' => $token,
        ]);
    }

    /**
     * Handle logout and revoke all tokens for current user.
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout berhasil',
        ]);
    }
}
