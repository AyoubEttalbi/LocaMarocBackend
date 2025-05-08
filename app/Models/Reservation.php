<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reservation extends Model
{
    protected $table = 'reservations';

    /** @use HasFactory<\Database\Factories\ReservationFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'startDate',
        'endDate',
        'totalPrice',
        'status',
        'statusUpdatedBy',
        'selectDriver',
        'vehicle_id',
        'location_id',
        'payment_id',
        'driver_id'
    ];

    protected $casts = [
        'startDate' => 'date',
        'endDate' => 'date',
        'totalPrice' => 'float'
    ];

    /**
     * Get the user that owns the reservation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the car that is reserved.
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class, 'vehicle_id');
    }

    /**
     * Get the user who updated the status.
     */
    public function statusUpdater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'statusUpdatedBy');
    }

    /**
     * Get the location associated with the reservation.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * Get the payment associated with the reservation.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    /**
     * Get the driver associated with the reservation.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id')
            ->where('role', 'driver');
    }
}
