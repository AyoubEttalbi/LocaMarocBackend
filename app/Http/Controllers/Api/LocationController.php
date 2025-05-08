<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Get all locations
     */
    public function index()
    {
        return Location::select('id', 'city', 'address', 'latitude', 'longitude')
            ->orderBy('city')
            ->get();
    }
}
