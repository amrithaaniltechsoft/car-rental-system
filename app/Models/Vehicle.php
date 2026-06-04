<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = [
        'name',
        'model',
        'brand',
        'type',
        'registration_number',
        'fuel_type',
        'rc_book_details',
        'insurance_details',
        'seating_capacity',
    ];

    protected $casts = [
        'seating_capacity' => 'integer',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
