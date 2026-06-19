<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
        'status',
        'meta_title',
        'meta_description',
        'canonical_url',
    ];

    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }
}