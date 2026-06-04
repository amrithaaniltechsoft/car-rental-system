<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $fillable = [
        'vehicle_id',
        'customer_id',
        'booking_date',
        'pickup_datetime',
        'return_datetime',
        'rental_duration',
        'pickup_location',
        'return_location',
        'total_amount',
        'status',
        'notes',
        'booking_id',
        'payment_type',
        // keep legacy date fields for backward compat
        'from_date',
        'to_date',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'pickup_datetime' => 'datetime',
            'return_datetime' => 'datetime',
            'from_date' => 'date',
            'to_date' => 'date',
            'total_amount' => 'decimal:2',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
