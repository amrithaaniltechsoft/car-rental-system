<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer_id',
        'booking_id',
        'amount',
        'rate',
        'vat',
        'vat_amount',
        'subtotal',
        'total',
        'invoice_date',
        'due_date',
        'status',
        'description',
        'total_usd',
        'exchange_rate',
        'total_omr',
        'subtotal_usd',
        'vat_amount_usd',
        'rate_type',
        'extra_kms_charges',
        'security_deposit',
        'insurance_fee',
        'additional_driver_fee',
        'delivery_charge',
        'fuel_charge',
        'gps_charges',
        'salik_toll_charges',
        'discount_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'rate' => 'decimal:2',
        'vat' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'invoice_date' => 'date',
        'due_date' => 'date',
        'total_usd' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'total_omr' => 'decimal:2',
        'subtotal_usd' => 'decimal:2',
        'vat_amount_usd' => 'decimal:2',
        'extra_kms_charges' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'insurance_fee' => 'decimal:2',
        'additional_driver_fee' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'fuel_charge' => 'decimal:2',
        'gps_charges' => 'decimal:2',
        'salik_toll_charges' => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function bill(): HasOne
    {
        return $this->hasOne(Bill::class);
    }
}
