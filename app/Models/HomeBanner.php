<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * One generic homepage slider item. `section` picks the slider: 'hero'
 * (main slideshow) or 'benefit' (the trust strip under it). Future homepage
 * sliders reuse this table with a new section value.
 */
class HomeBanner extends Model
{
    public const SECTION_HERO = 'hero';

    public const SECTION_BENEFIT = 'benefit';

    public const SECTIONS = [self::SECTION_HERO, self::SECTION_BENEFIT];

    public const SECTION_LABELS = [
        self::SECTION_HERO => 'Hero Slides',
        self::SECTION_BENEFIT => 'Benefits Strip',
    ];

    public const LINK_TYPES = ['url', 'product', 'category', 'tag', 'none'];

    /** Built-in benefit icons (rendered by <x-frontend.benefit-icon>) → admin label. */
    public const ICONS = [
        'truck' => 'Delivery truck',
        'headset' => 'Support headset',
        'shield' => 'Secure payment shield',
        'money-back' => 'Money back',
        'leaf' => 'Leaf',
        'gift' => 'Gift',
        'tag' => 'Price tag',
        'star' => 'Star',
    ];

    /** Subtitle placeholder replaced with the live free-shipping threshold. */
    public const THRESHOLD_TOKEN = '{free_shipping_threshold}';

    protected $fillable = [
        'section', 'title', 'subtitle', 'button_text', 'image', 'mobile_image', 'icon', 'alt_text',
        'link_type', 'product_id', 'category_id', 'tag_id', 'link_url', 'opens_new_tab',
        'sort_order', 'status',
    ];

    protected $casts = [
        'opens_new_tab' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeSection(Builder $query, string $section): Builder
    {
        return $query->where('section', $section);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Subtitle with the {free_shipping_threshold} token resolved, so a
     * "Free Shipping on Orders Above ₹399" card always follows the real
     * Storefront & Offers setting instead of a number typed into the text.
     */
    public function renderedSubtitle(string $formattedThreshold): ?string
    {
        return $this->subtitle === null
            ? null
            : str_replace(self::THRESHOLD_TOKEN, $formattedThreshold, $this->subtitle);
    }

    public function hasLink(): bool
    {
        return $this->link_type !== 'none' && $this->destinationUrl() !== null;
    }

    /**
     * Resolves the click-through destination from the configured target so a
     * banner never points at a hardcoded URL. A banner whose product/
     * category/tag was deleted (FKs are nullOnDelete) falls back to the shop
     * rather than rendering a dead link. Null only for link_type 'none'.
     */
    public function destinationUrl(): ?string
    {
        return match ($this->link_type) {
            'none' => null,
            'product' => $this->product?->slug ? route('products.show', $this->product->slug) : route('shop'),
            'category' => $this->category?->slug ? route('category.show', $this->category->slug) : route('shop'),
            'tag' => $this->tag?->slug ? route('shop', ['tag' => $this->tag->slug]) : route('shop'),
            default => $this->link_url ?: route('shop'),
        };
    }
}
