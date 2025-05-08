<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;

    /**
     * Get all of the reservations for the Payment
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'payment_id');
    }
}
