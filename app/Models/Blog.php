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
}