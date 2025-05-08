<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    /** @use HasFactory<\Database\Factories\LocationFactory> */
    use HasFactory;

    /**
     * Get all of the reservations for the Location
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'location_id');
    }
}
