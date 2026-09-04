<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantImage extends Model
{
    protected $fillable = [
        'product_variant_id',
        'image',
        'media_type',
        'is_primary',
        'sort_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function isVideo(): bool
    {
        return $this->media_type === 'video';
    }

    public function isImage(): bool
    {
        return $this->media_type === 'image';
    }
}
