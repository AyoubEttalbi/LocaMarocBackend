<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    /** @use HasFactory<\Database\Factories\DriverFactory> */
    use HasFactory;

    /**
     * Get all of the reservations for the Driver
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'driver_id');
    }
}
