<?php

namespace App\Models;

use App\Support\Marketplaces;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A manually maintained price for one product (optionally one specific variant) on one external
 * marketplace. Marketplaces themselves are defined in config/marketplaces.php.
 */
class ProductMarketplacePrice extends Model
{
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'marketplace',
        'marketplace_product_name',
        'mrp',
        'selling_price',
        'product_url',
        'affiliate_url',
        'is_active',
        'sort_order',
        'last_checked_at',
    ];

    protected $casts = [
        'mrp' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'last_checked_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Keep the NOT NULL uniqueness helper in step with the nullable variant id (see the migration).
        static::saving(function (self $price) {
            $price->variant_scope = (int) ($price->product_variant_id ?? 0);
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /** The variant this listing is comparable to; null means it applies at product level. */
    public function variant()
    {
        // withTrashed: a soft-deleted variant must still be nameable in the admin card (its listing just stops applying).
        return $this->belongsTo(ProductVariant::class, 'product_variant_id')->withTrashed();
    }

    public function clicks()
    {
        return $this->hasMany(MarketplaceClick::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Active AND priced: the only rows a customer may ever see. */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_active', true)->whereNotNull('selling_price')->where('selling_price', '>', 0);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function label(): string
    {
        return Marketplaces::label($this->marketplace);
    }

    public function isDisplayable(): bool
    {
        return $this->is_active
            && $this->selling_price !== null
            && (float) $this->selling_price > 0
            && Marketplaces::isEnabled($this->marketplace);
    }

    /**
     * Calculated, never stored: ((MRP − selling) / MRP) × 100, rounded to a whole percent.
     * Null unless there is a real MRP that the selling price is strictly below.
     */
    public function discountPercent(): ?int
    {
        if ($this->mrp === null || $this->selling_price === null) {
            return null;
        }

        $mrp = (float) $this->mrp;
        $selling = (float) $this->selling_price;

        if ($mrp <= 0 || $selling >= $mrp) {
            return null;
        }

        $percent = (int) round((($mrp - $selling) / $mrp) * 100);

        return $percent > 0 ? $percent : null;
    }

    /** True when an admin entered a selling price above the MRP (a probable typo; no discount is shown). */
    public function sellingPriceExceedsMrp(): bool
    {
        return $this->mrp !== null && $this->selling_price !== null && (float) $this->selling_price > (float) $this->mrp;
    }

    /** Where the redirect sends customers: the affiliate URL when present, otherwise the product URL. */
    public function displayUrl(): ?string
    {
        return self::safeUrl($this->affiliate_url) ?? self::safeUrl($this->product_url);
    }

    /** The URL only when it is a well-formed http(s) URL — never javascript:, data:, file:, etc. */
    public static function safeUrl(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '' || ! preg_match('~^https?://~i', $url) || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        return $url;
    }

    /** The customer-facing link: our own redirect route, which logs the click, never the raw destination. */
    public function outboundUrl(string $source = 'product_card'): string
    {
        return route('marketplace.out', ['marketplacePrice' => $this->getKey(), 'src' => $source]);
    }

    public function isStale(): bool
    {
        return $this->last_checked_at === null
            || $this->last_checked_at->lt(now()->subDays(Marketplaces::staleAfterDays()));
    }
}
