<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Testimonial extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'designation',
        'company',

        'image',

        'rating',

        'testimonial',

        'sort_order',

        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
        'sort_order' => 'integer',
    ];
}