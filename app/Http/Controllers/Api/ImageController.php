<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;

class ImageController extends Controller
{
    /**
     * Get Cloudinary instance
     * 
     * @return Cloudinary
     */
    private function getCloudinary()
    {
        return new Cloudinary(
            Configuration::instance([
                'cloud' => [
                    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                    'api_key' => env('CLOUDINARY_API_KEY'),
                    'api_secret' => env('CLOUDINARY_API_SECRET'),
                    'url' => [
                        'secure' => true
                    ]
                ]
            ])
        );
    }

    /**
     * Upload an image to Cloudinary via the Laravel backend
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'image' => 'required|file|mimes:jpeg,jpg,png,gif|max:5120', // 5MB max
            ], [
                'image.required' => 'An image file is required',
                'image.file' => 'The uploaded file must be a valid image file',
                'image.mimes' => 'Only JPEG, JPG, PNG, and GIF images are allowed',
                'image.max' => 'The image must not be larger than 5MB'
            ]);

            // Get the image file
            $image = $request->file('image');

            if (!$image) {
                return response()->json([
                    'message' => 'No image file provided',
                    'errors' => ['image' => 'No image file was uploaded']
                ], 400);
            }

            // Check file size
            if ($image->getSize() > 5120 * 1024) { // 5MB in bytes
                return response()->json([
                    'message' => 'Image is too large',
                    'errors' => ['image' => 'The image must not be larger than 5MB']
                ], 400);
            }
            
            // Upload to Cloudinary
            $cloudinary = $this->getCloudinary();
            
            $result = $cloudinary->uploadApi()->upload(
                $image->getRealPath(),
                [
                    'folder' => 'car-images',
                    'resource_type' => 'image'
                ]
            );
            
            // Return the secure URL from Cloudinary
            return response()->json([
                'success' => true,
                'imageUrl' => $result['secure_url'],
                'message' => 'Image uploaded successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Image upload error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image: ' . $e->getMessage()
            ], 500);
        }
    }
}