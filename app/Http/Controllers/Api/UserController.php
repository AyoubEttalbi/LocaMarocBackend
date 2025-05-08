<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Cloudinary\Cloudinary;
use App\Http\Controllers\API\ImageController;

class UserController extends Controller
{
    /**
     * Get Cloudinary instance
     * 
     * @return Cloudinary
     */
    private function uploadImage(Request $request)
    {
        try {
            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                    'api_key' => env('CLOUDINARY_API_KEY'),
                    'api_secret' => env('CLOUDINARY_API_SECRET'),
                ],
            ]);

            $result = $cloudinary->uploadApi()->upload($request->file('image'), [
                'folder' => 'user-profiles',
                'resource_type' => 'image'
            ]);
            return $result->secure_url;
        } catch (\Exception $e) {
            Log::error('Image upload error: ' . $e->getMessage());
            return null;
        }
    }

    public function index(Request $request)
    {
        try {
            // Only admins can access this endpoint
            if (!Auth::user() || Auth::user()->role != 'admin') {
                throw ValidationException::withMessages([
                    'message' => 'Unauthorized access'
                ]);
            }

            $users = User::with('reservations')
                ->when($request->has('search'), function ($query) use ($request) {
                    $search = $request->input('search');
                    return $query->where(function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%')
                            ->orWhere('role', 'like', '%' . $search . '%');
                    });
                })
                ->paginate(10);

            return response()->json($users);
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'message' => 'Failed to fetch users: ' . $e->getMessage()
            ]);
        }
    }

    public function show($id)
    {
        try {
            // Only admins can access this endpoint
            if (!Auth::user() || Auth::user()->role != 'admin') {
                throw ValidationException::withMessages([
                    'message' => 'Unauthorized access'
                ]);
            }

            $user = User::with('reservations')->findOrFail($id);
            return response()->json($user);
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'message' => 'User not found'
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            // Only admins can create new users
            if (!Auth::user() || !Auth::user()->role !== 'admin') {
                throw ValidationException::withMessages([
                    'message' => 'Unauthorized access'
                ]);
            }

            // Validate request
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'phone' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'age' => 'required|integer|min:18',
                'image' => 'nullable|file|image|max:2048', // Allow file upload
                'role' => 'required|string',
                'password' => 'required|string|min:8',
                'email_verified_at' => 'nullable|date',
            ]);

            // Handle image upload
            $image_url = null;
            if ($request->hasFile('image')) {
                $image_url = $this->uploadImage($request);
            } else if ($request->has('image') && is_string($request->image)) {
                $image_url = $request->image;
            }

            // Create user with image URL
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'age' => $validated['age'],
                'role' => $validated['role'],
                'password' => Hash::make($validated['password']),
                'email_verified_at' => $validated['email_verified_at'] ?? null,
                'image' => $image_url
            ]);

            // Make sure the image URL is saved
            if ($image_url) {
                $user->image = $image_url;
                $user->save();
            }

            // Return the user data without creating a token (to prevent admin logout)
            return response()->json([
                'status' => true,
                'message' => 'User created successfully',
                'user' => $user
            ], 201);

            // Return the user data without creating a token (to prevent admin logout)
            return response()->json([
                'status' => true,
                'message' => 'User created successfully',
                'user' => $user->fresh() // Get fresh user data with image URL if set
            ], 201);

        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'message' => 'Failed to create user: ' . $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Only admins can update users
            if (!Auth::user() || Auth::user()->role !== 'admin') {
                throw ValidationException::withMessages([
                    'message' => 'Unauthorized access'
                ]);
            }

            $user = User::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $id,
                'phone' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'age' => 'nullable|integer|min:18',
                'image' => 'nullable|file|image|max:2048|string|url', // Allow both file upload and URL
                'password' => 'nullable|string|min:8',
                'email_verified_at' => 'nullable|date_format:Y-m-d H:i:s|nullable', // Allow null values
                'currentPassword' => 'nullable|required_with:password|string',
                'confirmPassword' => 'nullable|required_with:password|string|same:password',
            ]);

            // Only allow role changes if user is admin
            if (Auth::user()->role === 'admin') {
                $validated['role'] = $request->role;
            } else {
                $validated['role'] = $user->role; // Keep existing role
            }

            // Handle image upload - optional
            $image_url = null;
            try {
                if ($request->hasFile('image')) {
                    $image_url = $this->uploadImage($request);
                } else if ($request->has('image') && is_string($request->image)) {
                    $image_url = $request->image;
                }
            } catch (\Exception $e) {
                Log::error('Image upload error: ' . $e->getMessage());
            }

            // Update user with all data
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'age' => $validated['age'],
                'role' => $validated['role'],
                'password' => $validated['password'] ? Hash::make($validated['password']) : $user->password,
                'email_verified_at' => $request->has('email_verified_at') ? $validated['email_verified_at'] : $user->email_verified_at,
                'image' => $image_url
            ]);

            return response()->json([
                'message' => 'User updated successfully',
                'user' => $user
            ]);

        } catch (\Exception $e) {
            Log::error('User update error: ' . $e->getMessage());
            throw ValidationException::withMessages([
                'message' => 'Failed to update user: ' . $e->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            // Only admins can delete users
            if (!Auth::user() || !Auth::user()->role === 'admin') {
                throw ValidationException::withMessages([
                    'message' => 'Unauthorized access'
                ]);
            }

            $user = User::findOrFail($id);
            $user->delete();
            return response()->json([
                'status' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'message' => 'Failed to delete user: ' . $e->getMessage()
            ]);
        }
    }

    public function search(Request $request)
    {
        try {
            // Only admins can search users
            if (!Auth::user() || !Auth::user()->role === 'admin') {
                throw ValidationException::withMessages([
                    'message' => 'Unauthorized access'
                ]);
            }

            $search = $request->query('q');
            $users = User::with('reservations')
                ->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('role', 'like', '%' . $search . '%');
                })
                ->paginate(10);

            return response()->json($users);
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'message' => 'Failed to search users: ' . $e->getMessage()
            ]);
        }
    }
    public function getDrivers()
    {
        try {
            $drivers = User::where('role', 'driver')->get();
            return response()->json($drivers);
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
               'message' => 'Failed to get drivers: '. $e->getMessage()
            ]);
        }
    }
}
