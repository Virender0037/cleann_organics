<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogTag extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    public function blogs()
    {
        return $this->belongsToMany(
            Blog::class,
            'blog_tag'
        );
    }
}