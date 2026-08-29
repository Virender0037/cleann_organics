<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    public const TOPICS = [
        'Products & Ingredients',
        'Orders & Payment',
        'Shipping & Delivery',
        'Returns & Refunds',
        'Offers & Discounts',
        'Sustainability & Eco Values',
        'How to Use Products',
        'Child Safety',
    ];

    protected $fillable = [
        'question',
        'answer',
        'topic',
        'sort_order',
        'status',
    ];
}