{{--
    Shared by the header's mini-cart sidebar (rendered on every page via the
    view composer in AppServiceProvider) and CartController::mini() (the
    fetch()-refreshed partial after an add/update/remove/clear elsewhere on
    the page) — one template, so the two can never drift apart.

    Every mutation here is a real <form> (method-spoofed DELETE), so remove
    works correctly even on pages that don't load cart.js — cart.js (where
    present) intercepts these via fetch() for a no-reload experience, but
    nothing here depends on it.

    Expects: $lines (Collection of CartService line arrays), $subtotal, $itemCount.
--}}
<div class="shopping-cart-top">
    <div class="shopping-cart-header">
        <h5 class="font-body--xxl-500">Shopping Cart (<span class="count">{{ $itemCount }}</span>)</h5>
        <button class="close" aria-label="Close cart">
            <svg width="45" height="45" viewBox="0 0 45 45" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="22.5" cy="22.5" r="22.5" fill="white" />
                <path d="M28.75 16.25L16.25 28.75" stroke="#1A1A1A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M16.25 16.25L28.75 28.75" stroke="#1A1A1A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
    </div>

    @forelse ($lines as $line)
        <div class="shopping-cart__product-content" data-cart-line data-item-key="{{ $line['key'] }}">
            <a href="{{ $line['product_url'] ?? '#' }}" class="shopping-cart__product-content-item">
                <div class="img-wrapper">
                    <img
                        src="{{ $line['thumbnail_url'] ?? asset('images/products/img-01.png') }}"
                        alt="{{ $line['product']->name ?? 'Product' }}"
                    />
                </div>
                <div class="text-content">
                    <h5 class="font-body--md-400">{{ $line['product']->name ?? 'Product' }}</h5>
                    @if (! $line['available'])
                        <p class="font-body--md-400" style="color:#c0392b;">No longer available</p>
                    @else
                        <p class="font-body--md-400">
                            {{ $line['variant_label'] }} — {{ $line['quantity'] }} x
                            <span class="font-body--md-500">₹{{ number_format($line['unit_price'], 2) }}</span>
                        </p>
                    @endif
                </div>
            </a>
            <form action="{{ route('cart.items.destroy', $line['key']) }}" method="POST" data-cart-form="remove">
                @csrf
                @method('DELETE')
                <button type="submit" class="delete-item" aria-label="Remove {{ $line['product']->name ?? 'item' }} from cart">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 23C18.0748 23 23 18.0748 23 12C23 5.92525 18.0748 1 12 1C5.92525 1 1 5.92525 1 12C1 18.0748 5.92525 23 12 23Z" stroke="#CCCCCC" stroke-miterlimit="10" />
                        <path d="M16 8L8 16" stroke="#666666" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M16 16L8 8" stroke="#666666" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </form>
        </div>
    @empty
        <div class="shopping-cart__product-content">
            <p class="font-body--md-400" style="padding: 16px 0;">Your cart is empty.</p>
        </div>
    @endforelse
</div>

<div class="shopping-cart-bottom">
    <div class="shopping-cart-product-info">
        <p class="product-count font-body--lg-400">{{ $itemCount }} {{ $itemCount === 1 ? 'Product' : 'Products' }}</p>
        <span class="product-price font-body--lg-500">₹{{ number_format($subtotal, 2) }}</span>
    </div>

    {{-- Checkout is a later phase — kept visible for the design but honestly
         inert rather than pretending to place an order. --}}
    <button class="button button--lg w-100" disabled aria-disabled="true" title="Checkout is coming soon">Checkout</button>
    <a href="{{ route('shopping-cart') }}" class="button button--lg button--disable w-100" style="display:block; text-align:center;">
        Go to Cart
    </a>
</div>
