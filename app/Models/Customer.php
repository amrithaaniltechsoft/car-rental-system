<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'customer_id',
        'customer_type',
        'name',
        'first_name',
        'last_name',
        'date_of_birth',
        'nationality',
        'company_name',
        'address',
        'residential_address',
        'phone_number',
        'email',
        'id_card_number',
        'passport_number',
        'driving_license_number',
        'license_expiry_date',
        'license_issue_country',
        'company_registration_id',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'license_expiry_date' => 'date',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
