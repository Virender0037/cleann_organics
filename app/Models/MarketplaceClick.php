<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One outbound click on a marketplace link. Intentionally holds no personal data (no IP, user agent or user).
 */
class MarketplaceClick extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'product_id',
        'product_marketplace_price_id',
        'marketplace',
        'source',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function price()
    {
        return $this->belongsTo(ProductMarketplacePrice::class, 'product_marketplace_price_id');
    }
}
