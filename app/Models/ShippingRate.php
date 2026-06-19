<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    protected $fillable = [
        'shipping_zone_id',
        'min_weight',
        'max_weight',
        'shipping_charge',
        'free_shipping_above',
        'status',
    ];

    protected $casts = [
        'min_weight' => 'decimal:2',
        'max_weight' => 'decimal:2',
        'shipping_charge' => 'decimal:2',
        'free_shipping_above' => 'decimal:2',
    ];

    public function zone()
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }
}