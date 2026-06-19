<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'featured_image',
        'sort_order',
        'status',
        'meta_title',
        'meta_description',
        'canonical_url',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];
}