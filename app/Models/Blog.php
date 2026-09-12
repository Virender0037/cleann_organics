<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'blog_category_id',
        'user_id',

        'title',
        'slug',

        'featured_image',

        'short_description',
        'content',

        'view_count',

        'is_featured',

        'status',
        'published_at',

        'meta_title',
        'meta_description',
        'canonical_url',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tags()
    {
        return $this->belongsToMany(
            BlogTag::class,
            'blog_tag'
        );
    }

    /**
     * The public-visibility rule for the storefront — mirrors
     * Product::scopePublic()/Category::scopeActive(): status must be
     * 'published' AND, for a post scheduled for the future via published_at,
     * that date must have arrived. A published post with no published_at
     * set is treated as immediately visible.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }
}
