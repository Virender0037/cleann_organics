<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'variant_name',
        'sku',
        'barcode',
        'unit',
        'size',
        'weight',
        'color',
        'pack_quantity',
        'enable_tiered_pricing',
        'single_quantity',
        'single_price',
        'standard_quantity',
        'standard_price',
        'discount_quantity',
        'discount_price',
        'stock_quantity',
        'low_stock_quantity',
        'stock_status',
        'is_default',
        'sort_order',
        'status',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * All media (images and videos) for this variant, in gallery order.
     * Kept named `images` for backward compatibility with existing callers;
     * it now returns every media type, not images exclusively.
     */
    public function images()
    {
        return $this->hasMany(ProductVariantImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductVariantImage::class)
            ->where('is_primary', true)
            ->where('media_type', 'image');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * The variant's price tiers, cheapest-quantity first.
     *
     * single/standard/discount are independent quantity tiers (e.g. 1+ at
     * ₹299, 10+ at ₹279, 30+ at ₹249) — NOT an old-price/new-price pair.
     * A tier at a higher quantity is a genuinely different purchase, so it
     * is never rendered as a strikethrough against a lower-quantity tier's
     * price; each tier is returned with its own quantity so the caller can
     * show "10+ qty ₹279" style rows instead.
     *
     * The one case a `compare_price` is populated is when two tiers share
     * the exact same quantity (e.g. an admin mistake or a deliberate
     * same-quantity price cut) — there, comparing is legitimate because
     * both prices apply to the identical purchase, so the higher one is
     * surfaced as a real strikethrough over the lower.
     *
     * Malformed input (a quantity <= 0, a missing price, tiers entered out
     * of numeric order) is handled safely: incomplete tiers are dropped and
     * the rest are always returned sorted by quantity ascending, so a
     * misordered discount_quantity/standard_quantity never produces a wrong
     * headline price or a crash.
     *
     * @return array<int, array{quantity: int, price: float, compare_price: ?float, source: string}>
     */
    public function pricingTiers(): array
    {
        if (! $this->enable_tiered_pricing) {
            if ($this->single_price === null || (float) $this->single_price < 0) {
                return [];
            }

            return [[
                'quantity' => ($this->single_quantity && (int) $this->single_quantity > 0) ? (int) $this->single_quantity : 1,
                'price' => (float) $this->single_price,
                'compare_price' => null,
                'source' => 'single',
            ]];
        }

        $candidates = [];

        foreach ([
            ['single_quantity', 'single_price', 'single'],
            ['standard_quantity', 'standard_price', 'standard'],
            ['discount_quantity', 'discount_price', 'discount'],
        ] as [$qtyField, $priceField, $source]) {
            $qty = $this->{$qtyField};
            $price = $this->{$priceField};

            if ($qty !== null && (int) $qty > 0 && $price !== null && (float) $price >= 0) {
                $candidates[] = ['quantity' => (int) $qty, 'price' => (float) $price, 'source' => $source];
            }
        }

        if (empty($candidates)) {
            return [];
        }

        // Collapse same-quantity tiers into one row (see doc comment above)
        // before the final sort, so two rows never render for one quantity.
        $byQuantity = [];
        foreach ($candidates as $tier) {
            $byQuantity[$tier['quantity']][] = $tier;
        }

        $tiers = [];
        foreach ($byQuantity as $quantity => $group) {
            if (count($group) === 1) {
                $tiers[] = [
                    'quantity' => $quantity,
                    'price' => $group[0]['price'],
                    'compare_price' => null,
                    'source' => $group[0]['source'],
                ];

                continue;
            }

            usort($group, fn ($a, $b) => $a['price'] <=> $b['price']);
            $lowest = $group[0];
            $highest = end($group);

            $tiers[] = [
                'quantity' => $quantity,
                'price' => $lowest['price'],
                'compare_price' => $highest['price'] > $lowest['price'] ? $highest['price'] : null,
                'source' => $lowest['source'],
            ];
        }

        usort($tiers, fn ($a, $b) => $a['quantity'] <=> $b['quantity']);

        return array_values($tiers);
    }

    /**
     * The tier to lead with: the lowest-quantity valid tier, i.e. what a
     * shopper buying the minimum amount actually pays. Never a bulk tier
     * dressed up as the base price.
     */
    public function headlineTier(): ?array
    {
        return $this->pricingTiers()[0] ?? null;
    }

    public function headlinePrice(): ?float
    {
        return $this->headlineTier()['price'] ?? null;
    }

    public function hasMultipleTiers(): bool
    {
        return count($this->pricingTiers()) > 1;
    }

    /**
     * A human label for the variant selector that never leaks the raw id.
     */
    public function displayLabel(): string
    {
        if ($this->variant_name) {
            return $this->variant_name;
        }

        $weight = null;
        if ($this->weight !== null) {
            $formatted = rtrim(rtrim(number_format((float) $this->weight, 2), '0'), '.');
            $weight = trim($formatted.' '.($this->unit ?? ''));
        }

        $parts = array_filter([
            $this->size,
            $weight,
            $this->color,
            $this->pack_quantity ? "Pack of {$this->pack_quantity}" : null,
        ], fn ($part) => filled($part));

        return $parts ? implode(' / ', $parts) : 'Standard';
    }

    public function stockLabel(): string
    {
        if ($this->stock_status === 'out_of_stock' || $this->stock_quantity <= 0) {
            return 'Out of Stock';
        }

        if ($this->stock_quantity <= $this->low_stock_quantity) {
            return "Only {$this->stock_quantity} left";
        }

        return 'In Stock';
    }

    public function isPurchasable(): bool
    {
        return $this->status === 'active'
            && $this->stock_status === 'in_stock'
            && $this->stock_quantity > 0;
    }
}
