<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
            'age' => 'required|integer|min:18',
            'image' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Handle image upload to Cloudinary
        if ($request->hasFile('image')) {
            $cloudinary = new Cloudinary(
                Configuration::instance([
                    'cloud' => [
                        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                        'api_key' => env('CLOUDINARY_API_KEY'),
                        'api_secret' => env('CLOUDINARY_API_SECRET'),
                    ],
                    'url' => [
                        'secure' => true
                    ]
                ])
            );
            $uploadedFile = $request->file('image');
            $result = $cloudinary->uploadApi()->upload(
                $uploadedFile->getRealPath(),
                ['folder' => 'users']
            );
            $image_url = $result['secure_url'];
        } else {
            $image_url = $request->image;
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'image' => $image_url,
            'age' => $request->age,
            'password' => Hash::make($request->password),
        ]);

        // Only create token if this is a regular registration (not admin creating user)
        if (!$request->has('admin')) {
            // Create token
            $token = $user->createToken('auth_token')->plainTextToken;
            
            // Return response with token
            return response()->json([
                'user' => $user,
                'token' => $token,
                'message' => 'User registered successfully'
            ], 201);
        }

        // For admin creating user, return without token
        return response()->json([
            'status' => true,
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

    /**
     * Login user and create token
     */
    public function login(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Check credentials
        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Get user and create token
        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return token
        return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'Logged in successfully'
        ]);
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request)
    {
        // Revoke all tokens
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
} 