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
                'image' => 'nullable|image|max:5120', // 5MB max
            ]);

            // Get the image file
            $image = $request->file('image');
            
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