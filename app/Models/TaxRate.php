<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    protected $fillable = [
        'name',
        'rate',
        'status',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}