<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Reel extends Model
{
    protected $fillable = [
        'title', 'thumbnail', 'video_path', 'video_url', 'instagram_url',
        'product_id', 'product_variant_id', 'sort_order', 'status',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /** Playable source: an uploaded file that actually exists, else the external URL. */
    public function videoSource(): ?string
    {
        if ($this->video_path && Storage::disk('public')->exists($this->video_path)) {
            return Storage::url($this->video_path);
        }

        return $this->video_url ?: null;
    }

    /**
     * The variant a "Add to cart" click adds: the reel's chosen variant if
     * still purchasable, else the product's first purchasable (default-first)
     * variant. Null when nothing is buyable — the card then shows View
     * Product only.
     */
    public function purchasableVariant(): ?ProductVariant
    {
        $product = $this->product;

        if (! $product) {
            return null;
        }

        $variants = $product->relationLoaded('variants')
            ? $product->variants
            : $product->variants()->where('status', 'active')->orderByDesc('is_default')->orderBy('sort_order')->get();

        if ($this->product_variant_id) {
            $chosen = $variants->firstWhere('id', $this->product_variant_id);

            if ($chosen?->isPurchasable()) {
                return $chosen;
            }
        }

        return $variants->first(fn (ProductVariant $v) => $v->isPurchasable());
    }
}
