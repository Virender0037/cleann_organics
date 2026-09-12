<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'transaction_id',
        'payment_method',
        'amount',
        'status',
        'admin_note',
        'failure_reason',
        'meta',
        'gateway_order_id',
        'gateway_payment_id',
        'gateway_signature',
        'paid_at',
        'refunded_at',
        'upi_reference',
        'proof_path',
        'submitted_at',
        'rejected_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'meta' => 'array',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
        'submitted_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
