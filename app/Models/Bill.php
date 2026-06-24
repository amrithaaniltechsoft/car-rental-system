<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bill extends Model
{
    protected $fillable = [
        'bill_number',
        'invoice_id',
        'amount',
        'bill_date',
        'due_date',
        'status',
        'notes',
        'amount_usd',
        'exchange_rate',
        'amount_omr',
        'billing_details',
        'net_profit',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'bill_date' => 'date',
        'due_date' => 'date',
        'amount_usd' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'amount_omr' => 'decimal:2',
        'billing_details' => 'array',
        'net_profit' => 'decimal:3',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
