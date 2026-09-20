{{--
    The price-comparison block: our own store first (visually primary), then compact marketplace offers.
    One markup, used by the product page (mode="pdp") and the shared "Compare prices" dialog (mode="dialog").

    $payload comes from App\Services\Storefront\MarketplaceComparison::payload() and is never null here.
    External links go through our own redirect route (which logs the click and only ever redirects to the URL stored
    on the active marketplace record) — the raw destination is never printed into the page.
--}}
@props(['payload', 'mode' => 'pdp'])
@php
    $own = $payload['ownPrice'];
    $variant = $payload['variant'];
    $money = fn ($value) => '₹'.number_format((float) $value, 2);
@endphp
<div class="compare__stack" data-compare-grid>
    <article class="compare__card compare__card--own">
        <div class="compare__head">
            <span class="compare__mark compare__mark--own"><span class="compare__mark-text">Clean Organics</span></span>
            @if ($payload['isBest'])
                <span class="compare__badge compare__badge--best">Best Price</span>
            @endif
        </div>
        <div class="compare__price">{{ $own !== null ? $money($own) : 'Price unavailable' }}</div>
        <div class="compare__meta">
            <span class="compare__official">Official Store</span>
            <span class="compare__note-tax">Inclusive of all taxes</span>
        </div>
        <div class="compare__cta">
            @if ($mode === 'pdp')
                {{-- Submits the page's existing add-to-cart form (same variant + quantity the shopper picked). --}}
                <button type="submit" form="add-to-cart-form" class="compare__btn compare__btn--primary" @disabled(! $payload['purchasable'])>
                    {{ $payload['purchasable'] ? 'Add to Cart' : 'Unavailable' }}
                </button>
            @elseif ($payload['purchasable'])
                <form action="{{ route('cart.items.store') }}" method="POST" data-cart-form="add" data-compare-cart>
                    @csrf
                    <input type="hidden" name="product_variant_id" value="{{ $variant->id }}" />
                    <input type="hidden" name="quantity" value="1" />
                    <button type="submit" class="compare__btn compare__btn--primary">Add to Cart</button>
                </form>
            @else
                <a href="{{ $payload['productUrl'] }}" class="compare__btn compare__btn--ghost">View product</a>
            @endif
        </div>
    </article>

    <div class="compare__offers">
    @foreach ($payload['offers'] as $offer)
        <article class="compare__card compare__card--offer" data-marketplace="{{ $offer['key'] }}">
            <div class="compare__head">
                <x-frontend.marketplace-mark :label="$offer['label']" :logo="$offer['logo']" />
            </div>
            <div class="compare__price">{{ $money($offer['price']) }}</div>
            <div class="compare__meta">
                @if ($offer['discount'])
                    <del class="compare__mrp">{{ $money($offer['mrp']) }}</del>
                    <span class="compare__off">{{ $offer['discount'] }}% OFF</span>
                @endif
            </div>
            <div class="compare__cta">
                <a
                    href="{{ $offer['url'] }}"
                    class="compare__btn compare__btn--ghost"
                    target="_blank"
                    rel="noopener noreferrer nofollow sponsored"
                    aria-label="View deal on {{ $offer['label'] }} (opens in a new tab)"
                >
                    View Deal
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true"><path d="M3.5 8.5l5-5M4.5 3.5h4v4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            </div>
        </article>
    @endforeach
    </div>
</div>
<p class="compare__disclaimer">Marketplace prices and availability may change. Please verify the final price on the respective marketplace.</p>
