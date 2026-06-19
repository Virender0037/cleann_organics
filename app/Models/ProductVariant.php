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

    public function images()
    {
        return $this->hasMany(ProductVariantImage::class);
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductVariantImage::class)->where('is_primary', true);
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