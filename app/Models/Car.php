<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'brand',
        'model',
        'seats',
        'gearType',
        'mileage',
        'pricePerDay',
        'availability',
        'fuelType',
        'color',
        'year',
        'image',
        'insuranceExpiryDate',
        'serviceDueDate',
        'features',
    ];

    protected $casts = [
        'availability' => 'boolean',
        'insuranceExpiryDate' => 'date',
        'serviceDueDate' => 'date',
        'features' => 'array',
    ];

    /**
     * Get all of the reservations for the Car
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
