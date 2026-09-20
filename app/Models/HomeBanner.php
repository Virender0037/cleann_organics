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

    public const TEXT_POSITIONS = ['left', 'right'];

    protected $fillable = [
        'section', 'title', 'subtitle', 'button_text', 'text_position', 'image', 'mobile_image', 'icon', 'alt_text',
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

    /**
     * width / height of the stored image (or its mobile version), so the hero
     * frame can match the uploaded artwork instead of cropping it into a fixed
     * banner ratio. Null when the file is missing/unreadable. Cached per file
     * version, so getimagesize() runs once per upload, not per request.
     */
    public function imageRatio(bool $mobile = false): ?float
    {
        $path = $mobile ? ($this->mobile_image ?: $this->image) : $this->image;

        if (! $path) {
            return null;
        }

        return \Illuminate\Support\Facades\Cache::rememberForever('home-banner-ratio.'.md5($path.'|'.$this->updated_at?->timestamp), function () use ($path) {
            $disk = \Illuminate\Support\Facades\Storage::disk('public');

            if (! $disk->exists($path)) {
                return null;
            }

            $size = @getimagesize($disk->path($path));

            return $size && $size[1] > 0 ? round($size[0] / $size[1], 4) : null;
        });
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
