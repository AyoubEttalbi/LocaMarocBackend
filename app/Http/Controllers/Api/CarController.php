<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
class CarController extends Controller
{   
    private function getCloudinary()
    {
        return new Cloudinary(
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
    }
    /**
     * Display a listing of the cars.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cars = Car::all();
        return response()->json($cars);
    }

    /**
     * Store a newly created car in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required|string',
            'brand' => 'required|string',
            'model' => 'required|string',
            'seats' => 'required|integer|min:1',
            'gearType' => 'required|string',
            'mileage' => 'required|integer|min:0',
            'pricePerDay' => 'required|numeric|min:0',
            'availability' => 'required|boolean',
            'fuelType' => 'required|string',
            'color' => 'required|string',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'image' => 'required|url|regex:/https:\/\/res\.cloudinary\.com\/.+/',
            'insuranceExpiryDate' => 'required|date',
            'serviceDueDate' => 'required|date',
            'features' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        // Handle image upload to Cloudinary
        if ($request->hasFile('image')) {
            $cloudinary = $this->getCloudinary();
            $uploadedFile = $request->file('image');
            $result = $cloudinary->uploadApi()->upload(
                $uploadedFile->getRealPath(),
                ['folder' => 'cars']
            );
            $data['image'] = $result['secure_url'];
        }

        // Convert features to JSON if present
        if (isset($data['features']) && is_array($data['features'])) {
            $data['features'] = json_encode($data['features']);
        }

        $car = Car::create($data);

        return response()->json($car, 201);
    }

    /**
     * Display the specified car.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $car = Car::findOrFail($id);
        return response()->json($car);
    }

    /**
     * Update the specified car in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $car = Car::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'category' => 'sometimes|required|string',
            'brand' => 'sometimes|required|string',
            'model' => 'sometimes|required|string',
            'seats' => 'sometimes|required|integer|min:1',
            'gearType' => 'sometimes|required|string',
            'mileage' => 'sometimes|required|integer|min:0',
            'pricePerDay' => 'sometimes|required|numeric|min:0',
            'availability' => 'sometimes|required|boolean',
            'fuelType' => 'sometimes|required|string',
            'color' => 'sometimes|required|string',
            'year' => 'sometimes|required|integer|min:1900|max:' . (date('Y') + 1),
            'image' => 'nullable|url|regex:/https:\/\/res\.cloudinary\.com\/.+/',
            'insuranceExpiryDate' => 'sometimes|required|date',
            'serviceDueDate' => 'sometimes|required|date',
            'features' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        // Handle image upload to Cloudinary
        if ($request->hasFile('image')) {
            // No need to delete old image from Cloudinary as they handle asset management
            // Upload new image to Cloudinary
            $cloudinary = $this->getCloudinary();
            $uploadedFile = $request->file('image');
            $result = $cloudinary->uploadApi()->upload(
                $uploadedFile->getRealPath(),
                ['folder' => 'cars']
            );
            $data['image'] = $result['secure_url'];
        }

        // Convert features to JSON if present
        if (isset($data['features']) && is_array($data['features'])) {
            $data['features'] = json_encode($data['features']);
        }

        $car->update($data);

        return response()->json($car);
    }

    /**
     * Remove the specified car from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $car = Car::findOrFail($id);
        
        // Note: We're not deleting images from Cloudinary here
        // If you want to delete images from Cloudinary, you would use:
        // $cloudinary = $this->getCloudinary();
        // $publicId = /* extract public_id from the URL */;
        // $cloudinary->uploadApi()->destroy($publicId);
        
        $car->delete();
        
        return response()->json(null, 204);
    }

    /**
     * Search for cars with filters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $query = Car::query();

        // Filter by category
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Filter by search query (brand, model, or category)
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('brand', 'like', "%{$searchTerm}%")
                  ->orWhere('model', 'like', "%{$searchTerm}%")
                  ->orWhere('category', 'like', "%{$searchTerm}%");
            });
        }

        // Filter by price range
        if ($request->has('minPrice')) {
            $query->where('pricePerDay', '>=', $request->minPrice);
        }
        if ($request->has('maxPrice')) {
            $query->where('pricePerDay', '<=', $request->maxPrice);
        }

        // Filter by brands
        if ($request->has('brands') && is_array($request->brands) && count($request->brands) > 0) {
            $query->whereIn('brand', $request->brands);
        }

        // Filter by gear types
        if ($request->has('gearTypes') && is_array($request->gearTypes) && count($request->gearTypes) > 0) {
            $query->whereIn('gearType', $request->gearTypes);
        }

        // Filter by fuel types
        if ($request->has('fuelTypes') && is_array($request->fuelTypes) && count($request->fuelTypes) > 0) {
            $query->whereIn('fuelType', $request->fuelTypes);
        }

        // Filter by years
        if ($request->has('years') && is_array($request->years) && count($request->years) > 0) {
            $query->whereIn('year', $request->years);
        }

        // Filter by minimum seats
        if ($request->has('minSeats') && $request->minSeats > 0) {
            $query->where('seats', '>=', $request->minSeats);
        }

        // Filter by availability
        if ($request->has('availability')) {
            $query->where('availability', $request->availability);
        }

        // Filter by features
        if ($request->has('features') && is_array($request->features) && count($request->features) > 0) {
            foreach ($request->features as $feature) {
                $query->whereJsonContains('features', $feature);
            }
        }

        // Apply sorting
        if ($request->has('sortBy')) {
            switch ($request->sortBy) {
                case 'price-low':
                    $query->orderBy('pricePerDay', 'asc');
                    break;
                case 'price-high':
                    $query->orderBy('pricePerDay', 'desc');
                    break;
                case 'year-new':
                    $query->orderBy('year', 'desc');
                    break;
                case 'year-old':
                    $query->orderBy('year', 'asc');
                    break;
                case 'mileage-low':
                    $query->orderBy('mileage', 'asc');
                    break;
                default:
                    // Default sorting (recommended)
                    $query->orderBy('id', 'desc');
                    break;
            }
        } else {
            // Default sorting
            $query->orderBy('id', 'desc');
        }

        // Pagination
        $perPage = $request->has('perPage') ? (int) $request->perPage : 10;
        $cars = $query->paginate($perPage);

        return response()->json($cars);
    }

    /**
     * Display the car reservation page.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showReservation($id)
    {
        $car = Car::findOrFail($id);
        
        // You can add additional data needed for the reservation page
        $data = [
            'car' => $car,
            'availableDates' => $this->getAvailableDates($car),
        ];
        
        return response()->json($data);
    }
    
    /**
     * Get available dates for a car.
     * This is a placeholder method - in a real application, you would check
     * existing bookings to determine which dates are available.
     *
     * @param  \App\Models\Car  $car
     * @return array
     */
    private function getAvailableDates(Car $car)
    {
        // This is a placeholder implementation
        // In a real application, you would check existing bookings
        
        $availableDates = [];
        
        // If the car is available, return the next 30 days as available
        if ($car->availability) {
            $startDate = now();
            $endDate = now()->addDays(30);
            
            for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                $availableDates[] = $date->format('Y-m-d');
            }
        }
        
        return $availableDates;
    }

    /**
     * Display a listing of the cars for admin.
     * This can include additional information not shown to regular users.
     *
     * @return \Illuminate\Http\Response
     */
    public function adminIndex()
    {
        $cars = Car::all();
        return response()->json($cars);
    }
}