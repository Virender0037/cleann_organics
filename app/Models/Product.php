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
}