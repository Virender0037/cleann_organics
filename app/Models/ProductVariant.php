<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'variant_name',
        'sku',
        'barcode',
        'unit',
        'size',
        'weight',
        'color',
        'pack_quantity',
        'enable_tiered_pricing',
        'single_quantity',
        'single_price',
        'standard_quantity',
        'standard_price',
        'discount_quantity',
        'discount_price',
        'stock_quantity',
        'low_stock_quantity',
        'stock_status',
        'is_default',
        'sort_order',
        'status',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * All media (images and videos) for this variant, in gallery order.
     * Kept named `images` for backward compatibility with existing callers;
     * it now returns every media type, not images exclusively.
     */
    public function images()
    {
        return $this->hasMany(ProductVariantImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductVariantImage::class)
            ->where('is_primary', true)
            ->where('media_type', 'image');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
