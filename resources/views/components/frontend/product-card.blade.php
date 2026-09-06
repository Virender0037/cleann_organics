{{--
    wrap/cardClass exist only so this exact card markup can be dropped into
    homepage sections that don't use the Shop page's Bootstrap column grid
    (a plain CSS grid with no wrapper element, or a swiper slide with a
    different inner card modifier) without changing what Shop/Product-Detail
    already render — both default to their original values.
--}}
@props(['product', 'wrapperClass' => 'col-xl-4 col-md-6', 'wrap' => true, 'cardClass' => 'cards-md cards-md--four w-100'])
@php
    // variants is expected pre-loaded, scoped to active and ordered
    // is_default desc, sort_order asc — first() is the variant a shopper
    // would see initially, same rule the product detail page uses.
    $variant = $product->variants->first();
    $tiers = $variant?->pricingTiers() ?? [];
    $headline = $tiers[0] ?? null;
    $thumbnail = $product->thumbnailImage();
    $rating = round($product->approved_average_rating ?? 0, 1);
    $reviewCount = $product->approved_review_count ?? 0;
    $badge = $product->is_best_seller
        ? 'Best Seller'
        : ($product->is_featured ? 'Featured' : ($product->is_latest ? 'New' : null));
    $productUrl = route('products.show', $product->slug);
    // WishlistService is bound as a singleton (see AppServiceProvider) and
    // memoizes the current user's wishlisted product ids for the whole
    // request, so this is safe to call once per card without N+1 — the
    // underlying query runs at most once no matter how many cards render.
    $isWishlisted = app(\App\Services\Storefront\WishlistService::class)->isWishlisted($product->id);
@endphp
@if ($wrap)
<div class="{{ $wrapperClass }}">
@endif
    <div class="{{ $cardClass }}">
        <div class="cards-md__img-wrapper">
            <a href="{{ $productUrl }}">
                <img
                    src="{{ $thumbnail ? \Illuminate\Support\Facades\Storage::url($thumbnail->image) : asset('images/products/img-01.png') }}"
                    alt="{{ $product->name }}"
                    loading="lazy"
                />
            </a>
            @if ($badge)
                <span class="tag danger font-body--md-400">{{ $badge }}</span>
            @endif
            <div class="cards-md__favs-list">
                <form
                    action="{{ $isWishlisted ? route('wishlist.destroy', $product) : route('wishlist.store') }}"
                    method="POST"
                    data-wishlist-form
                >
                    @csrf
                    @if ($isWishlisted)
                        @method('DELETE')
                    @else
                        <input type="hidden" name="product_id" value="{{ $product->id }}" />
                    @endif
                    <button
                        type="submit"
                        class="action-btn"
                        aria-label="{{ $isWishlisted ? 'Remove '.$product->name.' from wishlist' : 'Add '.$product->name.' to wishlist' }}"
                        aria-pressed="{{ $isWishlisted ? 'true' : 'false' }}"
                    >
                        <svg width="20" height="18" viewBox="0 0 20 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9.9996 16.5451C-6.66672 7.3333 4.99993 -2.6667 9.9996 3.65668C14.9999 -2.6667 26.6666 7.3333 9.9996 16.5451Z" stroke="currentColor" fill="{{ $isWishlisted ? 'currentColor' : 'none' }}" stroke-width="1.5"></path>
                        </svg>
                    </button>
                </form>
                {{-- Real link to the product page rather than a Bootstrap
                     modal — every card previously opened the exact same
                     hardcoded "Quick View" modal regardless of which
                     product was clicked; that fake-data widget is removed
                     for Phase F rather than reproduced. --}}
                <a class="action-btn" href="{{ $productUrl }}" aria-label="View {{ $product->name }}">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M10 3.54102C3.75 3.54102 1.25 10.0001 1.25 10.0001C1.25 10.0001 3.75 16.4577 10 16.4577C16.25 16.4577 18.75 10.0001 18.75 10.0001C18.75 10.0001 16.25 3.54102 10 3.54102V3.54102Z"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        ></path>
                        <path
                            d="M10 13.125C10.8288 13.125 11.6237 12.7958 12.2097 12.2097C12.7958 11.6237 13.125 10.8288 13.125 10C13.125 9.1712 12.7958 8.37634 12.2097 7.79029C11.6237 7.20424 10.8288 6.875 10 6.875C9.1712 6.875 8.37634 7.20424 7.79029 7.79029C7.20424 8.37634 6.875 9.1712 6.875 10C6.875 10.8288 7.20424 11.6237 7.79029 12.2097C8.37634 12.7958 9.1712 13.125 10 13.125V13.125Z"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        ></path>
                    </svg>
                </a>
            </div>
        </div>
        <div class="cards-md__info d-flex justify-content-between align-items-center">
            <a href="{{ $productUrl }}" class="cards-md__info-left">
                <h6 class="font-body--md-400">{{ $product->name }}</h6>
                <div class="cards-md__info-price">
                    @if ($headline)
                        @if ($variant->hasMultipleTiers())
                            <span class="font-body--md-400">From</span>
                        @endif
                        <span class="font-body--lg-500">₹{{ number_format($headline['price'], 2) }}</span>
                        @if ($headline['compare_price'])
                            <del class="font-body--lg-400">₹{{ number_format($headline['compare_price'], 2) }}</del>
                        @endif
                    @else
                        <span class="font-body--lg-500">Price unavailable</span>
                    @endif
                </div>
                <x-frontend.rating-stars :rating="$rating" />
            </a>
            <div class="cards-md__info-right">
                @if ($variant && $variant->isPurchasable())
                    <form action="{{ route('cart.items.store') }}" method="POST" data-cart-form="add">
                        @csrf
                        <input type="hidden" name="product_variant_id" value="{{ $variant->id }}" />
                        <input type="hidden" name="quantity" value="1" />
                        <button type="submit" class="action-btn" aria-label="Add {{ $product->name }} to cart">
                            <svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M6.66667 8.83333H4.16667L2.5 18H17.5L15.8333 8.83333H13.3333M6.66667 8.83333V6.33333C6.66667 4.49239 8.15905 3 10 3V3C11.8409 3 13.3333 4.49238 13.3333 6.33333V8.83333M6.66667 8.83333H13.3333M6.66667 8.83333V11.3333M13.3333 8.83333V11.3333"
                                    stroke="currentColor"
                                    stroke-width="1.3"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                ></path>
                            </svg>
                        </button>
                    </form>
                @else
                    <span class="action-btn" aria-label="{{ $product->name }} is out of stock" aria-disabled="true" style="opacity:0.4; cursor:not-allowed;">
                        <svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M6.66667 8.83333H4.16667L2.5 18H17.5L15.8333 8.83333H13.3333M6.66667 8.83333V6.33333C6.66667 4.49239 8.15905 3 10 3V3C11.8409 3 13.3333 4.49238 13.3333 6.33333V8.83333M6.66667 8.83333H13.3333M6.66667 8.83333V11.3333M13.3333 8.83333V11.3333"
                                stroke="currentColor"
                                stroke-width="1.3"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            ></path>
                        </svg>
                    </span>
                @endif
            </div>
        </div>
    </div>
@if ($wrap)
</div>
@endif
