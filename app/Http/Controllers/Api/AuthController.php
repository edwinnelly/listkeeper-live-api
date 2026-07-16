<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

// use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
// use Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            Log::warning('User registration validation failed', [
                'errors' => $validator->errors()->toArray(),
                'input' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check for duplicate email
        if (User::where('email', $request->email)->exists()) {
            Log::info('Attempted registration with duplicate email', [
                'email' => $request->email,
            ]);

            return response()->json([
                'message' => 'This email is already registered. Please use another email.'
            ], 409);
        }

        try {
            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'business_key' => (string) Str::uuid(),
            ]);

            // Return success response
            return response()->json([
                'message' => 'User registered successfully!',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('User registration failed', [
                'message' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Registration failed, please try again later.'
            ], 500);
        }
    }

    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|email',
    //         'password' => 'required',
    //     ]);

    //     $user = User::where('email', $request->email)->first();

    //     if (!$user || !Hash::check($request->password, $user->password)) {
    //         Log::warning('Failed login attempt', [
    //             'email' => $request->email,
    //             'ip' => $request->ip(),
    //         ]);
    //         return response()->json(['message' => 'Invalid credentials'], 401);
    //     }

    //     // Create new Sanctum token
    //     $token = $user->createToken('auth-token')->plainTextToken;

    //     // Log the token
    //     Log::info('User logged in successfully', [
    //         'user_id' => $user->id,
    //         'email' => $user->email,
    //         'token' => $token,
    //         'ip' => $request->ip(),
    //         'user_agent' => $request->userAgent(),
    //         'timestamp' => now()->toDateTimeString(),
    //     ]);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Logged in successfully',
    //         'token' => $token,
    //         'token_type' => 'Bearer',
    //         'user' => [
    //             'id' => $user->id,
    //             'name' => $user->name,
    //             'email' => $user->email,
    //             'business_key' => $user->business_key,
    //             'business_name' => $user->business_name ?? null,
    //             'role' => $user->role ?? null,
    //         ],
    //     ]);
    // }


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'remember' => 'nullable|boolean',
        ]);

        $user = User::where('email', $request->email)->first();

        // Check credentials
        if (!$user || !Hash::check($request->password, $user->password)) {
            Log::warning('Failed login attempt', [
                'email' => $request->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Delete all old tokens - Single session per user
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken(
            'auth-token',
            ['*'],
            $request->remember
                ? now()->addDays(7)   // 7 days if "remember me"
                : now()->addHours(8)  // 8 hours (typical work shift)
        )->plainTextToken;

        // Record login history for audit
        // $this->recordLoginHistory($user, $request);

        // Log successful login
        Log::info('User logged in successfully', [
            'user_id' => $user->id,
            'email' => $user->email,
            'business_key' => $user->business_key,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'remember' => $request->remember ?? false,
            'timestamp' => now()->toDateTimeString(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Logged in successfully',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'business_key' => $user->business_key,
                'business_name' => $user->business_name ?? null,
            ],
        ]);
    }



    // Login existing user
    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|email',
    //         'password' => 'required',
    //     ]);

    //     $user = User::where('email', $request->email)->first();

    //     if (!$user || !Hash::check($request->password, $user->password)) {
    //         return response()->json(['message' => 'Invalid credentials'], 401);
    //     }

    //     // Delete old tokens (optional - remove if you want to keep multiple tokens)
    //     $user->tokens()->delete();

    //     // Create new Sanctum token
    //     $token = $user->createToken('auth-token')->plainTextToken;

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Logged in successfully',
    //         'token' => $token,
    //         'token_type' => 'Bearer',
    //         'user' => [
    //             'id' => $user->id,
    //             'name' => $user->name,
    //             'email' => $user->email,
    //             'business_key' => $user->business_key,
    //             'business_name' => $user->business_name ?? null,
    //             'role' => $user->role ?? null,
    //         ],
    //     ]);
    // }
    // public function login(Request $request)
    // {
    //     $user = User::where('email', $request->email)->first();

    //     if (!$user || !Hash::check($request->password, $user->password)) {
    //         return response()->json(['message' => 'Invalid credentials'], 401);
    //     }

    //     Auth::login($user); // login user

    //     return response()->json(['message' => 'Logged in successfully']);
    // }

    // Logout user
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully']);
    }


    // Get authenticated user
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
