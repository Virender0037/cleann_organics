<?php

namespace App\Services\Storefront;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Single source of truth for cart reads and mutations, for both guest
 * (session-only — see class docblock on why) and authenticated
 * (carts/cart_items tables) shoppers. Pricing is never computed here from
 * scratch: every unit price comes from ProductVariant::unitPriceForQuantity(),
 * which is itself built on the Phase F pricingTiers() accessor. Nothing in
 * this class trusts a price, name, or stock figure supplied by the client —
 * every line is rebuilt from the current database record on every read.
 *
 * Guest carts live entirely in the session (`carts`/`cart_items` cannot
 * represent one: carts.user_id is NOT NULL and UNIQUE, so there is no schema
 * blocker to work around here, just a deliberate storage choice) as a plain
 * [variant_id => quantity] map — the minimum needed to reconstruct a line
 * from the database, nothing else. SESSION_DRIVER=database in this app, so
 * this persists reliably server-side across requests, not just in a cookie.
 */
class CartService
{
    private const SESSION_KEY = 'cart';

    public function __construct(private readonly Request $request)
    {
    }

    public function isGuest(): bool
    {
        return ! Auth::check();
    }

    /**
     * Every line in the current cart, repaired in place: quantities beyond
     * current stock are clamped down (and persisted back), and lines whose
     * variant/product/category is no longer publicly valid are flagged
     * unavailable rather than silently dropped, so a shopper can see why
     * and remove it themselves instead of it just vanishing.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function lines(): Collection
    {
        if ($this->isGuest()) {
            return $this->guestLines();
        }

        return $this->authLines();
    }

    public function itemCount(): int
    {
        return (int) $this->lines()->where('available', true)->sum('quantity');
    }

    public function subtotal(): float
    {
        return round((float) $this->lines()->where('available', true)->sum('subtotal'), 2);
    }

    public function isEmpty(): bool
    {
        return $this->lines()->isEmpty();
    }

    /**
     * Add a variant to the cart. Adding one already present increases its
     * quantity (never a duplicate row/key) — clamped to available stock
     * either way. Every check here is server-side; a disabled frontend
     * button is not trusted.
     *
     * @return array{success: bool, message: string, line?: array}
     */
    public function addItem(int $variantId, int $quantity): array
    {
        if ($quantity < 1) {
            return ['success' => false, 'message' => 'Quantity must be at least 1.'];
        }

        $variant = $this->findPurchasableVariant($variantId);

        if (! $variant) {
            return ['success' => false, 'message' => 'This product is no longer available.'];
        }

        if ($this->isGuest()) {
            $raw = $this->guestCartRaw();
            $existingQty = $raw[$variantId] ?? 0;
            $desiredQty = $existingQty + $quantity;
            $clampedQty = max(1, min($desiredQty, $variant->stock_quantity));

            $raw[$variantId] = $clampedQty;
            $this->saveGuestCartRaw($raw);

            $message = $clampedQty < $desiredQty
                ? "Only {$variant->stock_quantity} in stock — quantity adjusted."
                : 'Added to cart.';

            return [
                'success' => true,
                'message' => $message,
                'line' => $this->buildLine($variantId, $variant, $clampedQty),
            ];
        }

        $cart = $this->getOrCreateAuthCart();

        $existing = CartItem::where('cart_id', $cart->id)
            ->where('product_variant_id', $variantId)
            ->first();

        $desiredQty = ($existing->quantity ?? 0) + $quantity;
        $clampedQty = max(1, min($desiredQty, $variant->stock_quantity));
        $unitPrice = $variant->unitPriceForQuantity($clampedQty);

        $item = CartItem::updateOrCreate(
            ['cart_id' => $cart->id, 'product_variant_id' => $variantId],
            [
                'quantity' => $clampedQty,
                'unit_price' => $unitPrice,
                'total_price' => round($unitPrice * $clampedQty, 2),
            ]
        );

        $this->recalculateAuthCartTotals($cart);

        $message = $clampedQty < $desiredQty
            ? "Only {$variant->stock_quantity} in stock — quantity adjusted."
            : 'Added to cart.';

        return [
            'success' => true,
            'message' => $message,
            'line' => $this->buildLine($item->id, $variant, $clampedQty),
        ];
    }

    /**
     * @param  string|int  $itemKey  variant id for a guest line, cart_item id for an authenticated one
     * @return array{success: bool, message: string, line?: array}
     */
    public function updateItem(string|int $itemKey, int $quantity): array
    {
        if ($quantity < 1) {
            return ['success' => false, 'message' => 'Quantity must be at least 1.'];
        }

        if ($this->isGuest()) {
            $raw = $this->guestCartRaw();
            $variantId = (int) $itemKey;

            if (! array_key_exists($variantId, $raw)) {
                return ['success' => false, 'message' => 'That item is not in your cart.'];
            }

            $variant = $this->findPurchasableVariant($variantId);

            if (! $variant) {
                unset($raw[$variantId]);
                $this->saveGuestCartRaw($raw);

                return ['success' => false, 'message' => 'This product is no longer available and was removed.'];
            }

            $clampedQty = min($quantity, $variant->stock_quantity);
            $raw[$variantId] = $clampedQty;
            $this->saveGuestCartRaw($raw);

            $message = $clampedQty < $quantity
                ? "Only {$variant->stock_quantity} in stock — quantity adjusted."
                : 'Quantity updated.';

            return ['success' => true, 'message' => $message, 'line' => $this->buildLine($variantId, $variant, $clampedQty)];
        }

        $item = $this->ownedCartItem($itemKey);

        if (! $item) {
            return ['success' => false, 'message' => 'That item is not in your cart.'];
        }

        $variant = $this->findPurchasableVariant($item->product_variant_id);

        if (! $variant) {
            $item->delete();
            $this->recalculateAuthCartTotals($item->cart);

            return ['success' => false, 'message' => 'This product is no longer available and was removed.'];
        }

        $clampedQty = min($quantity, $variant->stock_quantity);
        $unitPrice = $variant->unitPriceForQuantity($clampedQty);

        $item->update([
            'quantity' => $clampedQty,
            'unit_price' => $unitPrice,
            'total_price' => round($unitPrice * $clampedQty, 2),
        ]);

        $this->recalculateAuthCartTotals($item->cart);

        $message = $clampedQty < $quantity
            ? "Only {$variant->stock_quantity} in stock — quantity adjusted."
            : 'Quantity updated.';

        return ['success' => true, 'message' => $message, 'line' => $this->buildLine($item->id, $variant, $clampedQty)];
    }

    public function removeItem(string|int $itemKey): array
    {
        if ($this->isGuest()) {
            $raw = $this->guestCartRaw();
            $variantId = (int) $itemKey;

            if (! array_key_exists($variantId, $raw)) {
                return ['success' => false, 'message' => 'That item is not in your cart.'];
            }

            unset($raw[$variantId]);
            $this->saveGuestCartRaw($raw);

            return ['success' => true, 'message' => 'Item removed.'];
        }

        $item = $this->ownedCartItem($itemKey);

        if (! $item) {
            return ['success' => false, 'message' => 'That item is not in your cart.'];
        }

        $cart = $item->cart;
        $item->delete();
        $this->recalculateAuthCartTotals($cart);

        return ['success' => true, 'message' => 'Item removed.'];
    }

    public function clear(): void
    {
        if ($this->isGuest()) {
            $this->request->session()->forget(self::SESSION_KEY);

            return;
        }

        $cart = $this->authCart();

        if ($cart) {
            $cart->items()->delete();
            $this->recalculateAuthCartTotals($cart);
        }
    }

    /**
     * Called from the Login event listener for both login and registration
     * (both call Auth::login(), which fires it). Combines quantities for a
     * variant present in both carts, drops anything no longer valid, never
     * exceeds current stock, and clears the guest session cart only after
     * the merge succeeds.
     */
    public function mergeGuestCartInto(User $user): void
    {
        $raw = $this->guestCartRaw();

        if (empty($raw)) {
            return;
        }

        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        DB::transaction(function () use ($raw, $cart) {
            foreach ($raw as $variantId => $quantity) {
                $variant = $this->findPurchasableVariant((int) $variantId);

                if (! $variant) {
                    continue;
                }

                $existing = CartItem::where('cart_id', $cart->id)
                    ->where('product_variant_id', $variantId)
                    ->first();

                $desiredQty = ($existing->quantity ?? 0) + $quantity;
                $clampedQty = max(1, min($desiredQty, $variant->stock_quantity));
                $unitPrice = $variant->unitPriceForQuantity($clampedQty);

                CartItem::updateOrCreate(
                    ['cart_id' => $cart->id, 'product_variant_id' => $variantId],
                    [
                        'quantity' => $clampedQty,
                        'unit_price' => $unitPrice,
                        'total_price' => round($unitPrice * $clampedQty, 2),
                    ]
                );
            }
        });

        $this->recalculateAuthCartTotals($cart);
        $this->request->session()->forget(self::SESSION_KEY);
    }

    // ------------------------------------------------------------------
    // Internals
    // ------------------------------------------------------------------

    /**
     * A variant only counts as addable/purchasable when it, its product,
     * and the product's category are all still active, and it's in stock —
     * the same public-visibility rule Phase F applies to storefront pages,
     * checked again here independently of whatever the frontend displayed.
     */
    private function findPurchasableVariant(int $variantId): ?ProductVariant
    {
        $variant = ProductVariant::query()
            ->with(['product.category', 'images'])
            ->find($variantId);

        if (! $variant || ! $variant->isPurchasable()) {
            return null;
        }

        $product = $variant->product;

        if (! $product || $product->status !== 'active') {
            return null;
        }

        if (! $product->category || $product->category->status !== 'active') {
            return null;
        }

        return $variant;
    }

    private function guestCartRaw(): array
    {
        $raw = $this->request->session()->get(self::SESSION_KEY, []);

        // Defensive: only ever trust an array of int quantities keyed by
        // variant id, regardless of what a stale/tampered session holds.
        $clean = [];
        foreach ((array) $raw as $variantId => $quantity) {
            if (is_numeric($variantId) && is_numeric($quantity) && (int) $quantity > 0) {
                $clean[(int) $variantId] = (int) $quantity;
            }
        }

        return $clean;
    }

    private function saveGuestCartRaw(array $data): void
    {
        if (empty($data)) {
            $this->request->session()->forget(self::SESSION_KEY);

            return;
        }

        $this->request->session()->put(self::SESSION_KEY, $data);
    }

    private function guestLines(): Collection
    {
        $raw = $this->guestCartRaw();

        if (empty($raw)) {
            return collect();
        }

        // One query for every variant in the guest cart rather than one
        // per line — this runs on every page load via the header composer,
        // so it matters.
        $variants = ProductVariant::query()
            ->with(['product.category', 'images'])
            ->whereIn('id', array_keys($raw))
            ->get()
            ->keyBy('id');

        $lines = collect();
        $repaired = [];

        foreach ($raw as $variantId => $quantity) {
            $variant = $variants->get($variantId);

            if (! $variant) {
                // Gone entirely (deleted) — drop it rather than show a line
                // with nothing to render.
                continue;
            }

            $line = $this->buildLine($variantId, $variant, $quantity);
            $lines->push($line);

            if ($line['available']) {
                $repaired[$variantId] = $line['quantity'];
            } else {
                // Keep unavailable lines visible (so the shopper can see
                // and remove them) but don't let a dead variant linger
                // forever in the stored session payload.
                $repaired[$variantId] = $quantity;
            }
        }

        $this->saveGuestCartRaw($repaired);

        return $lines;
    }

    private function authLines(): Collection
    {
        $cart = $this->authCart();

        if (! $cart) {
            return collect();
        }

        $items = $cart->items()->with(['variant.product.category', 'variant.images'])->get();

        return $items->map(function (CartItem $item) {
            $variant = $item->variant;

            if (! $variant) {
                return null;
            }

            return $this->buildLine($item->id, $variant, $item->quantity);
        })->filter()->values();
    }

    private function authCart(): ?Cart
    {
        $user = Auth::user();

        return $user ? Cart::where('user_id', $user->id)->first() : null;
    }

    private function getOrCreateAuthCart(): Cart
    {
        return Cart::firstOrCreate(['user_id' => Auth::id()]);
    }

    /**
     * Finds a cart_items row by id, scoped strictly to the current user's
     * own cart — never trusts the id alone. Returns null (never another
     * user's row) if it doesn't belong to Auth::user().
     */
    private function ownedCartItem(string|int $itemKey): ?CartItem
    {
        $userId = Auth::id();

        if (! $userId) {
            return null;
        }

        return CartItem::where('id', $itemKey)
            ->whereHas('cart', fn ($q) => $q->where('user_id', $userId))
            ->with('cart')
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    private function buildLine(string|int $key, ProductVariant $variant, int $requestedQuantity): array
    {
        $product = $variant->product;
        $categoryActive = $product?->category?->status === 'active';
        $productActive = $product?->status === 'active';
        $variantActive = $variant->status === 'active';

        $available = $variantActive && $productActive && $categoryActive;
        $quantity = max(1, $requestedQuantity);
        $adjusted = false;

        if ($available) {
            if ($variant->stock_status !== 'in_stock' || $variant->stock_quantity <= 0) {
                $available = false;
            } elseif ($quantity > $variant->stock_quantity) {
                $quantity = $variant->stock_quantity;
                $adjusted = true;
            }
        }

        $unitPrice = $available ? $variant->unitPriceForQuantity($quantity) : null;
        $subtotal = $unitPrice !== null ? round($unitPrice * $quantity, 2) : 0.0;

        $image = $variant->images->firstWhere('is_primary', true)
            ?? $variant->images->where('media_type', 'image')->sortBy('sort_order')->first();

        return [
            'key' => $key,
            'variant' => $variant,
            'product' => $product,
            'quantity' => $quantity,
            'adjusted' => $adjusted,
            'available' => $available,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
            'stock_label' => $available ? $variant->stockLabel() : 'Unavailable',
            'variant_label' => $variant->displayLabel(),
            'thumbnail_url' => $image ? Storage::url($image->image) : null,
            'product_url' => $product ? route('products.show', $product->slug) : null,
        ];
    }

    private function recalculateAuthCartTotals(Cart $cart): void
    {
        $subtotal = round((float) $cart->items()->sum('total_price'), 2);

        // Shipping/discount/tax remain Phase H/coupon-phase territory — left
        // at 0 rather than invented here, so total is honestly just the
        // subtotal for now.
        $cart->update([
            'subtotal' => $subtotal,
            'total_amount' => $subtotal,
        ]);
    }
}
