<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'tax_rate_id',
        'name',
        'slug',
        'short_description',
        'description',
        'brand',
        'is_returnable',
        'return_days',
        'is_featured',
        'is_latest',
        'is_best_seller',
        'average_rating',
        'review_count',
        'view_count',
        'sort_order',
        'status',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function taxRate()
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function defaultVariant()
    {
        return $this->hasOne(ProductVariant::class)->where('is_default', true);
    }

    public function specifications()
    {
        return $this->hasMany(ProductSpecification::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'product_tag');
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * The image to show as this product's thumbnail (e.g. the admin product
     * list), or null if none exists anywhere. Never returns a video.
     *
     * Walks the product's active variants in priority order (default variant
     * first, then sort_order) and returns the first one's primary image, or
     * its first image by sort_order if it has no primary — falling through
     * to the next active variant if this one has no image at all.
     *
     * Expects `variants` (constrained to active, ordered is_default desc
     * then sort_order) and each variant's `images` to already be
     * eager-loaded by the caller; querying media type/order is done here in
     * PHP defensively (belt-and-braces against a caller that eager-loaded
     * images unconstrained) rather than assumed from the eager-load shape.
     */
    public function thumbnailImage(): ?ProductVariantImage
    {
        foreach ($this->variants as $variant) {
            if ($variant->status !== 'active') {
                continue;
            }

            $images = $variant->images->where('media_type', 'image');

            $image = $images->firstWhere('is_primary', true)
                ?? $images->sortBy('sort_order')->first();

            if ($image) {
                return $image;
            }
        }

        return null;
    }
}
