<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    protected $fillable = [
        'name',
        'code',
        'estimated_delivery',
        'description',
        'sort_order',
        'status',
    ];
}
