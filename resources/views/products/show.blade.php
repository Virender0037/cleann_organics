<x-layouts.app
    :meta-title="$metaTitle"
    :meta-description="$metaDescription"
    :canonical-url="$canonicalUrl"
    :og-image="$ogImage"
>
    @php
        $galleryMedia = collect();
        if ($defaultVariant) {
            $sorted = $defaultVariant->images->sortBy('sort_order')->values();
            $primary = $sorted->firstWhere('is_primary', true);
            $galleryMedia = $primary
                ? collect([$primary])->concat($sorted->reject(fn ($i) => $i->id === $primary->id))
                : $sorted;
        }
        $headlineTier = $defaultVariant?->headlineTier();
    @endphp

    <!-- breedcrumb section start  -->
    <div class="section breedcrumb">
        <div class="breedcrumb__img-wrapper">
            <img src="{{ asset('images/banner/breedcrumb.jpg') }}" alt="breedcrumb">
            <div class="container">
                <ul class="breedcrumb__content">
                    <li>
                        <a href="{{ route('home') }}">
                            <svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M1 8L9 1L17 8V18H12V14C12 13.2044 11.6839 12.4413 11.1213 11.8787C10.5587 11.3161 9.79565 11 9 11C8.20435 11 7.44129 11.3161 6.87868 11.8787C6.31607 12.4413 6 13.2044 6 14V18H1V8Z"
                                    stroke="#808080" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span> > </span>
                        </a>
                    </li>
                    <li><a href="{{ route('shop') }}">Shop<span> > </span></a></li>
                    @if ($product->category)
                        <li><a href="{{ route('category.show', $product->category->slug) }}">{{ $product->category->name }}<span> > </span></a></li>
                    @endif
                    <li class="active"><a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a></li>
                </ul>
            </div>
        </div>
    </div>
    <!-- breedcrumb section end   -->

    <!-- Products View Section Start  -->
    <section class="products section">
        <div class="container">
            <div class="row" style="margin-top: 32px;">
                <div class="col-lg-6">
                    <!-- Product View Slider -->
                    <div class="gallery-view">
                        <div class="gallery-items">
                            <div class="swiper-container gallery-items-slider">
                                <div class="swiper-wrapper">
                                    @forelse ($galleryMedia as $index => $media)
                                        <div class="gallery-item swiper-slide {{ $index === 0 ? 'active' : '' }}" data-type="{{ $media->media_type }}" data-url="{{ \Illuminate\Support\Facades\Storage::url($media->image) }}">
                                            @if ($media->media_type === 'video')
                                                <video src="{{ \Illuminate\Support\Facades\Storage::url($media->image) }}" muted playsinline></video>
                                            @else
                                                <img src="{{ \Illuminate\Support\Facades\Storage::url($media->image) }}" alt="{{ $product->name }}" />
                                            @endif
                                        </div>
                                    @empty
                                        <div class="gallery-item swiper-slide active" data-type="image" data-url="{{ asset('images/products/img-01.png') }}">
                                            <img src="{{ asset('images/products/img-01.png') }}" alt="{{ $product->name }}" />
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                            <div class="gallery-prev-item">
                                <div class="gallery-icon">
                                    <svg width="16" height="10" viewBox="0 0 16 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15 8.5L8 1.5L1 8.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                            </div>
                            <div class="gallery-next-item">
                                <div class="gallery-icon">
                                    <svg width="16" height="10" viewBox="0 0 16 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15 1.5L8 8.5L1 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="gallery-main-image products__gallery-img--lg" id="gallery-main-viewer">
                            <img class="product-main-image" id="main-viewer-image" src="{{ $galleryMedia->first() && $galleryMedia->first()->media_type !== 'video' ? \Illuminate\Support\Facades\Storage::url($galleryMedia->first()->image) : asset('images/products/img-01.png') }}" alt="{{ $product->name }}" @if ($galleryMedia->first()?->media_type === 'video') hidden @endif />
                            <video class="product-main-image" id="main-viewer-video" controls @unless ($galleryMedia->first()?->media_type === 'video') hidden @endunless src="{{ $galleryMedia->first()?->media_type === 'video' ? \Illuminate\Support\Facades\Storage::url($galleryMedia->first()->image) : '' }}"></video>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <!-- Products information -->
                    <div class="products__content">
                        <div class="products__content-eyebrow">
                            <span class="products__content-eyebrow__text">
                                Pure &bull; Natural &bull; Sustainable
                                <svg class="products__content-eyebrow__icon" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12.5 1.5C12.5 1.5 12.8 6.3 9.8 9.3C7.4 11.7 3.5 12 1.5 12.5C2 10.5 2.3 6.6 4.7 4.2C7.7 1.2 12.5 1.5 12.5 1.5Z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M2 12L6 8" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
                                </svg>
                            </span>
                            <span class="products__content-stock {{ $defaultVariant?->isPurchasable() ? 'stock-in' : 'stock-out' }}" id="stock-badge">
                                <svg width="12" height="12" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M13.3333 4L6 11.3333L2.66667 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span id="stock-badge-label">{{ $defaultVariant?->stockLabel() ?? 'Unavailable' }}</span>
                            </span>
                        </div>

                        <div class="products__content-title">
                            <h2 class="font-title--md">{{ $product->name }}</h2>
                        </div>
                        <div class="products__content-info">
                            <x-frontend.rating-stars size="lg" :rating="$averageRating" />
                            <a href="#pills-customer-tab" class="font-body--md-400 review-count">{{ $reviewCount }} Review{{ $reviewCount === 1 ? '' : 's' }}</a>
                            <span class="dot">.</span>
                            <h5 class="font-body--md-500">Sku: <span class="counting font-body--md-400" id="variant-sku">{{ $defaultVariant?->sku ?? '—' }}</span></h5>
                        </div>

                        @if ($product->short_description)
                            <p class="products__content-short-desc font-body--md-400">{{ $product->short_description }}</p>
                        @endif

                        @php
                            // At the default quantity of 1, the active tier is always
                            // tiers[0] (the base tier) — headlineTier() IS that tier —
                            // so no discount is active yet on first paint. The tier
                            // panel below still lists every tier (needed regardless of
                            // starting quantity); only the "is-active" row and the
                            // price panel's discount styling depend on quantity, so
                            // they stay in their qty=1 state here and the tier-pricing
                            // JS (already called once on load, further down) takes
                            // over immediately after.
                            $tiersForPanel = $defaultVariant?->hasMultipleTiers() ? $defaultVariant->pricingTiers() : [];
                        @endphp
                        <div class="products__content-price products__content-price--panel {{ $defaultVariant?->hasMultipleTiers() ? 'has-tiers' : '' }}" id="price-block">
                            @if ($headlineTier)
                                <div class="products__content-price__main">
                                    <h2 class="font-body--xxxl-500">
                                        <span id="price-current">₹{{ number_format($headlineTier['price'], 2) }}</span>
                                        @if ($defaultVariant?->hasMultipleTiers())
                                            <span class="font-body--md-400 price-each" id="price-each">each</span>
                                        @endif
                                    </h2>
                                    @if ($headlineTier['compare_price'])
                                        <div class="price-discount-row" id="price-discount-row">
                                            <del class="font-body--xxl-400 price-original" id="price-compare">₹{{ number_format($headlineTier['compare_price'], 2) }}</del>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="products__content-price__main">
                                    <h2 class="font-body--xxxl-500" id="price-current">Price unavailable</h2>
                                </div>
                            @endif
                        </div>

                        {{--
                            Always present in the DOM (even for a non-tiered default
                            variant) and just hidden via the same native `hidden`
                            attribute this file already uses for the gallery's
                            img/video toggle — switching TO a tiered variant later
                            needs this element to already exist so the JS can reveal
                            and populate it, rather than having to create it from
                            scratch.
                        --}}
                        <div class="products__content-tier-panel" id="price-tiers-panel" @unless ($defaultVariant?->hasMultipleTiers()) hidden @endunless>
                            <div class="products__content-tier-panel__header">
                                <span class="products__content-tier-panel__header-icon">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="1.5" y="9" width="3" height="5.5" rx="0.8" fill="currentColor" />
                                        <rect x="6.5" y="5.5" width="3" height="9" rx="0.8" fill="currentColor" />
                                        <rect x="11.5" y="2" width="3" height="12.5" rx="0.8" fill="currentColor" />
                                    </svg>
                                </span>
                                <h3 class="products__content-tier-panel__heading">Quantity-based Pricing</h3>
                            </div>
                            <ul class="products__content-tier-panel__list" id="price-tiers">
                                @foreach ($tiersForPanel as $index => $tier)
                                    <li class="products__content-tier-panel__row {{ $index === 0 ? 'is-active' : '' }}">
                                        <span class="products__content-tier-panel__qty">{{ $tier['quantity'] }}+ qty</span>
                                        <span class="products__content-tier-panel__price">₹{{ number_format($tier['price'], 2) }} <span class="products__content-tier-panel__unit">each</span></span>
                                        <span class="products__content-tier-panel__badges">
                                            @if ($index === 0)
                                                <span class="products__content-tier-panel__badge products__content-tier-panel__badge--active">Your current price</span>
                                            @endif
                                            @if ($index === count($tiersForPanel) - 1)
                                                <span class="products__content-tier-panel__badge products__content-tier-panel__badge--best">Best price</span>
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        {{--
                            Populated entirely by the tier-pricing JS below (initial
                            render, +/-, manual quantity input, variant switch all go
                            through the same helper) — quantity-aware line total and
                            the next-tier/best-price incentive banner.
                        --}}
                        <div class="products__content-tier-feedback" id="tier-pricing-feedback"></div>

                        @if ($variants->count() > 1)
                            <div class="products__content-category font-body--md-500" style="margin-bottom: 8px;">Options:</div>
                            <div class="popular-tags" role="group" aria-label="Select an option" id="variant-selector" style="margin-bottom: 16px;">
                                @foreach ($variants as $variant)
                                    <button
                                        type="button"
                                        class="tag-btn variant-option {{ $variant->id === $defaultVariant?->id ? 'active' : '' }}"
                                        data-variant-id="{{ $variant->id }}"
                                        aria-pressed="{{ $variant->id === $defaultVariant?->id ? 'true' : 'false' }}"
                                        @unless ($variant->isPurchasable()) disabled aria-disabled="true" @endunless
                                    >{{ $variant->displayLabel() }}</button>
                                @endforeach
                            </div>
                        @endif

                        <div class="products__content-action">
                            <form action="{{ route('cart.items.store') }}" method="POST" id="add-to-cart-form" data-cart-form="add" style="display:contents;">
                                @csrf
                                <input type="hidden" name="product_variant_id" id="add-to-cart-variant-id" value="{{ $defaultVariant?->id }}" />
                                <div class="counter-btn-wrapper products__content-action-item">
                                    <button type="button" class="counter-btn-dec counter-btn" onclick="decrement()" aria-label="Decrease quantity">-</button>
                                    <input type="number" name="quantity" id="counter-btn-counter" class="counter-btn-counter" min="1" max="{{ $defaultVariant?->stock_quantity ?? 1 }}" value="1" aria-label="Quantity" />
                                    <button type="button" class="counter-btn-inc counter-btn" onclick="increment()" aria-label="Increase quantity">+</button>
                                </div>
                                <button type="submit" class="button button--md products__content-action-item" id="add-to-cart-btn" @unless ($defaultVariant?->isPurchasable()) disabled aria-disabled="true" @endunless>
                                    Add to Cart
                                    <span>
                                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M5.66667 7.33333H3.16667L1.5 16.5H16.5L14.8333 7.33333H12.3333M5.66667 7.33333V4.83333C5.66667 2.99239 7.15905 1.5 9 1.5V1.5C10.8409 1.5 12.3333 2.99238 12.3333 4.83333V7.33333M5.66667 7.33333H12.3333M5.66667 7.33333V9.83333M12.3333 7.33333V9.83333"
                                                stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                </button>
                            </form>

                            <form
                                action="{{ $isWishlisted ? route('wishlist.destroy', $product) : route('wishlist.store') }}"
                                method="POST"
                                class="products__content-action-item"
                                data-wishlist-form
                                style="display:contents;"
                            >
                                @csrf
                                @if ($isWishlisted)
                                    @method('DELETE')
                                @else
                                    <input type="hidden" name="product_id" value="{{ $product->id }}" />
                                @endif
                                <button
                                    type="submit"
                                    class="button-fav"
                                    aria-label="{{ $isWishlisted ? 'Remove from wishlist' : 'Add to wishlist' }}"
                                    aria-pressed="{{ $isWishlisted ? 'true' : 'false' }}"
                                >
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M9.9996 17.5451C-6.66672 8.33336 4.99993 -1.66664 9.9996 4.65674C14.9999 -1.66664 26.6666 8.33336 9.9996 17.5451Z" stroke="currentColor" fill="{{ $isWishlisted ? 'currentColor' : 'none' }}" stroke-width="1.5" />
                                    </svg>
                                </button>
                            </form>
                        </div>

                        {{--
                            Trust row — only claims already genuinely made elsewhere
                            on the site are reused here (verified before adding):
                            "Free Shipping" and "100% Secure Payment" copy is reused
                            verbatim from the homepage's cards-ship component
                            (resources/views/home.blade.php), and its truck/shield
                            icons are reused verbatim too. No numeric free-shipping
                            threshold (e.g. "above ₹999") is shown — a database check
                            (ShippingRate::free_shipping_above) found no shipping
                            rate rows configured at all, so no such threshold is
                            actually enforced anywhere in this app yet; showing one
                            would be fabricating a number, not reflecting real
                            configuration. "Eco-Friendly" reuses this product's own
                            category name ("Eco-Friendly Bottles") and the site's
                            existing organic/eco-friendly positioning (see
                            aboutus.blade.php), not a new claim.
                        --}}
                        <div class="products__content-trust">
                            <div class="products__content-trust__item">
                                <span class="products__content-trust__icon">
                                    <svg width="18" height="13" viewBox="0 0 40 28" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M32.7021 20.3042C31.7247 20.3042 30.7962 20.687 30.0957 21.3793C29.3952 22.0798 29.0043 22.992 29.0043 23.9694C29.0043 24.9468 29.3871 25.8591 30.0957 26.5595C30.8043 27.2519 31.7247 27.6347 32.7021 27.6347C34.7058 27.6347 36.3348 25.9894 36.3348 23.9694C36.3348 21.9495 34.7058 20.3042 32.7021 20.3042ZM32.7021 26.0057C31.5781 26.0057 30.6333 25.0772 30.6333 23.9694C30.6333 22.8617 31.5781 21.9332 32.7021 21.9332C33.8098 21.9332 34.7058 22.8454 34.7058 23.9694C34.7058 25.0935 33.8098 26.0057 32.7021 26.0057ZM33.6469 8.09488C33.5003 7.95641 33.3048 7.88311 33.1012 7.88311H28.9228C28.4749 7.88311 28.1083 8.24964 28.1083 8.69761V15.3765C28.1083 15.8245 28.4749 16.191 28.9228 16.191H35.5528C36.0008 16.191 36.3673 15.8245 36.3673 15.3765V10.9049C36.3673 10.6768 36.2696 10.4569 36.0986 10.3022L33.6469 8.09488ZM34.7383 14.562H29.7373V9.50396H32.7835L34.7383 11.2633V14.562ZM12.8121 20.3042C11.8347 20.3042 10.9061 20.687 10.2057 21.3793C9.50519 22.0798 9.11423 22.992 9.11423 23.9694C9.11423 24.9468 9.49705 25.8591 10.2057 26.5595C10.9143 27.2519 11.8347 27.6347 12.8121 27.6347C14.8157 27.6347 16.4447 25.9894 16.4447 23.9694C16.4447 21.9495 14.8157 20.3042 12.8121 20.3042ZM12.8121 26.0057C11.688 26.0057 10.7432 25.0772 10.7432 23.9694C10.7432 22.8617 11.688 21.9332 12.8121 21.9332C13.9198 21.9332 14.8157 22.8454 14.8157 23.9694C14.8157 25.0935 13.9198 26.0057 12.8121 26.0057ZM7.37935 21.306H5.74221V19.1395C5.74221 18.6915 5.37569 18.325 4.92771 18.325C4.47974 18.325 4.11322 18.6915 4.11322 19.1395V22.1205C4.11322 22.5685 4.47974 22.935 4.92771 22.935H7.37935C7.82733 22.935 8.19385 22.5685 8.19385 22.1205C8.19385 21.6726 7.82733 21.306 7.37935 21.306ZM11.5089 16.867C11.5089 16.419 11.1423 16.0525 10.6944 16.0525H0.814498C0.366524 16.0525 0 16.419 0 16.867C0 17.315 0.366524 17.6815 0.814498 17.6815H10.6944C11.1423 17.6815 11.5089 17.3231 11.5089 16.867ZM2.46793 13.9267L12.3478 13.9837C12.7958 13.9837 13.1623 13.6253 13.1704 13.1773C13.1786 12.7212 12.8121 12.3547 12.3641 12.3547L2.48422 12.2977C2.47607 12.2977 2.47607 12.2977 2.47607 12.2977C2.0281 12.2977 1.66158 12.6561 1.66158 13.104C1.65343 13.5602 2.01996 13.9267 2.46793 13.9267ZM4.12951 10.2289H14.0094C14.4573 10.2289 14.8239 9.86234 14.8239 9.41437C14.8239 8.96639 14.4573 8.59987 14.0094 8.59987H4.12951C3.68153 8.59987 3.31501 8.96639 3.31501 9.41437C3.31501 9.86234 3.68153 10.2289 4.12951 10.2289ZM39.6986 9.12929L33.8668 4.29932C33.7202 4.17715 33.541 4.11199 33.3456 4.11199H26.4875V1.17979C26.4875 0.73182 26.121 0.365295 25.673 0.365295H4.92771C4.47974 0.365295 4.11322 0.73182 4.11322 1.17979V7.14192C4.11322 7.58989 4.47974 7.95642 4.92771 7.95642C5.37569 7.95642 5.74221 7.58989 5.74221 7.14192V1.99429H24.8666V21.306H18.1877C17.7398 21.306 17.3732 21.6726 17.3732 22.1205C17.3732 22.5685 17.7398 22.935 18.1877 22.935H28.1328C28.5807 22.935 28.9473 22.5685 28.9473 22.1205C28.9473 21.6726 28.5807 21.306 28.1328 21.306H26.4956V5.74098H33.0605L38.371 10.1393L38.314 21.2897H37.4669C37.0189 21.2897 36.6524 21.6563 36.6524 22.1042C36.6524 22.5522 37.0189 22.9187 37.4669 22.9187H39.1203C39.5683 22.9187 39.9348 22.5604 39.9348 22.1124L40 9.7646C39.9919 9.52025 39.886 9.28405 39.6986 9.12929Z" fill="currentColor"/></svg>
                                </span>
                                <span class="products__content-trust__text">
                                    <strong>Free Shipping</strong>
                                    <small>Free shipping on all your order</small>
                                </span>
                            </div>
                            <div class="products__content-trust__item">
                                <span class="products__content-trust__icon">
                                    <svg width="15" height="18" viewBox="0 0 33 40" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M32.6468 34.6678L30.345 8.72697C30.3036 8.21362 29.873 7.82447 29.3514 7.82447H24.4994C24.4911 3.51066 20.9805 0 16.6667 0C12.3528 0 8.84219 3.51066 8.83391 7.82447H3.98191C3.46856 7.82447 3.038 8.21362 2.98832 8.72697L0.686523 34.6678C0.686523 34.7009 0.686523 34.7257 0.686523 34.7589C0.686523 37.6485 3.34436 40 6.60662 40H26.7267C29.9889 40 32.6468 37.6485 32.6468 34.7589C32.6468 34.7257 32.6468 34.7009 32.6468 34.6678ZM16.6667 1.98717C19.8875 1.98717 22.504 4.6036 22.5122 7.82447H10.8211C10.8294 4.6036 13.4458 1.98717 16.6667 1.98717ZM26.7267 38.0046H6.60662C4.45386 38.0046 2.69853 36.5721 2.67369 34.792L4.89269 9.81163H8.83391V13.2975C8.83391 13.8439 9.28102 14.291 9.82749 14.291C10.374 14.291 10.8211 13.8439 10.8211 13.2975V9.81163H22.5122V13.2975C22.5122 13.8439 22.9593 14.291 23.5058 14.291C24.0523 14.291 24.4994 13.8439 24.4994 13.2975V9.81163H28.4406L30.6596 34.8002C30.6348 36.5721 28.8794 38.0046 26.7267 38.0046Z" fill="currentColor"/></svg>
                                </span>
                                <span class="products__content-trust__text">
                                    <strong>Secure Payment</strong>
                                    <small>100% secure checkout</small>
                                </span>
                            </div>
                            <div class="products__content-trust__item">
                                <span class="products__content-trust__icon">
                                    <svg width="16" height="16" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12.5 1.5C12.5 1.5 12.8 6.3 9.8 9.3C7.4 11.7 3.5 12 1.5 12.5C2 10.5 2.3 6.6 4.7 4.2C7.7 1.2 12.5 1.5 12.5 1.5Z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M2 12L6 8" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
                                    </svg>
                                </span>
                                <span class="products__content-trust__text">
                                    <strong>Eco-Friendly</strong>
                                    <small>Good for you, better for Earth</small>
                                </span>
                            </div>
                        </div>

                        <div class="products__content-meta-row">
                            <h2 class="font-body--md-400 products__content-meta-row__item">Brand: <span class="font-body--md-500">{{ $product->brand ?: 'Unbranded' }}</span></h2>
                            @if ($product->category)
                                <h5 class="font-body--md-500 products__content-meta-row__item">Category: <a href="{{ route('category.show', $product->category->slug) }}">{{ $product->category->name }}</a></h5>
                            @endif
                        </div>

                        @if ($product->tags->isNotEmpty())
                            <div class="products__content-tags">
                                <h5 class="font-body--md-500">Tag :</h5>
                                <div class="products__content-tags-item">
                                    @foreach ($product->tags as $tag)
                                        <a href="{{ route('shop', ['tag' => $tag->slug]) }}" class="font-body--md-400">{{ $tag->name }}</a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Products View Section end  -->

    <!-- Products Tabs Start  -->
    <section class="products-tab section section--xl">
        <div class="products-tab__btn">
            <div class="container">
                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-description-tab" data-bs-toggle="pill" data-bs-target="#pills-description" type="button" role="tab" aria-controls="pills-description" aria-selected="true">
                            Description
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-information-tab" data-bs-toggle="pill" data-bs-target="#pills-information" type="button" role="tab" aria-controls="pills-information" aria-selected="false">
                            Additional information
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-customer-tab" data-bs-toggle="pill" data-bs-target="#pills-customer" type="button" role="tab" aria-controls="pills-customer" aria-selected="false">
                            Customer feedback
                        </button>
                    </li>
                </ul>
            </div>
        </div>
        <div class="products-tab__content">
            <div class="container">
                <div class="tab-content" id="pills-tabContent">
                    <!-- Products Description  -->
                    <div class="tab-pane fade show active" id="pills-description" role="tabpanel" aria-labelledby="pills-description-tab">
                        <div class="row products-tab__description">
                            <div class="col-lg-12">
                                @if ($product->description)
                                    <div class="products-tab__description--text">{!! nl2br(e($product->description)) !!}</div>
                                @else
                                    <p class="products-tab__description--text">No description provided yet.</p>
                                @endif
                                <p class="products-tab__description--text">
                                    @if ($product->is_returnable)
                                        Returnable within {{ $product->return_days }} day{{ $product->return_days === 1 ? '' : 's' }} of delivery.
                                    @else
                                        This item is not eligible for returns.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    <!-- Additional Information -->
                    <div class="tab-pane fade" id="pills-information" role="tabpanel" aria-labelledby="pills-information-tab">
                        <div class="row products-tab__information">
                            <div class="col-lg-12">
                                <ul class="products-tab__information-list">
                                    @foreach ($product->specifications as $spec)
                                        <li>
                                            <h5 class="title">{{ $spec->title }}:</h5>
                                            <p class="title-description">{{ $spec->value }}</p>
                                        </li>
                                    @endforeach
                                    @if ($product->category)
                                        <li>
                                            <h5 class="title">Category:</h5>
                                            <p class="title-description">{{ $product->category->name }}</p>
                                        </li>
                                    @endif
                                    <li>
                                        <h5 class="title">Stock Status:</h5>
                                        <p class="title-description" id="tab-stock-status">{{ $defaultVariant?->stockLabel() ?? 'Unavailable' }}</p>
                                    </li>
                                    @if ($product->tags->isNotEmpty())
                                        <li>
                                            <h5 class="title">Tags:</h5>
                                            <div class="title-description title-description__tags">
                                                @foreach ($product->tags as $tag)
                                                    <a href="{{ route('shop', ['tag' => $tag->slug]) }}" class="title-description__tags-item">{{ $tag->name }}@if (! $loop->last),@endif</a>
                                                @endforeach
                                            </div>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!--  Customer Feedback  -->
                    <div class="tab-pane fade" id="pills-customer" role="tabpanel" aria-labelledby="pills-customer-tab">
                        <div class="row products-tab__feedback">
                            <div class="col-lg-12">
                                <div class="feedback">
                                    @forelse ($reviews as $review)
                                        <div class="products-tab__feedback-content">
                                            <div class="products-tab__feedback-content-top">
                                                <div class="user-details">
                                                    <div class="user-details__info">
                                                        <h2 class="user-name">{{ $review->user->name ?? 'Verified Customer' }}</h2>
                                                        <x-frontend.rating-stars size="md" :rating="$review->rating" />
                                                    </div>
                                                </div>
                                                <div class="user-published__info">
                                                    <p>{{ $review->created_at->diffForHumans() }}</p>
                                                </div>
                                            </div>
                                            @if ($review->title)
                                                <p class="products-tab__feedback-comments"><strong>{{ $review->title }}</strong></p>
                                            @endif
                                            <p class="products-tab__feedback-comments">{{ $review->review }}</p>
                                        </div>
                                    @empty
                                        <p class="font-body--md-400">No reviews yet.</p>
                                    @endforelse

                                    <div class="products-tab__feedback-content" style="border-top:1px solid #e5e5e5;padding-top:24px;margin-top:8px;">
                                        <h5 class="font-body--lg-500" style="margin-bottom:16px;">Write a Review</h5>

                                        @auth
                                            @if ($userReview)
                                                @if ($userReview->status === 'pending')
                                                    <p class="font-body--md-400">Your review has been submitted and is awaiting moderation. It isn't public yet.</p>
                                                @elseif ($userReview->status === 'rejected')
                                                    <p class="font-body--md-400">Your review was not approved for publication.</p>
                                                @else
                                                    <p class="font-body--md-400">You've already reviewed this product. Thanks for your feedback!</p>
                                                @endif
                                            @else
                                                @if (session('success'))
                                                    <p class="font-body--md-400" style="color:#00B307;margin-bottom:16px;">{{ session('success') }}</p>
                                                @endif
                                                <form action="{{ route('reviews.store') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $product->id }}" />

                                                    <div class="contact-form__content">
                                                        <div class="contact-form-input">
                                                            <label for="review-rating">Rating</label>
                                                            <select name="rating" id="review-rating" required>
                                                                <option value="">Select a rating</option>
                                                                @foreach (['Poor', 'Fair', 'Good', 'Very Good', 'Excellent'] as $index => $label)
                                                                    <option value="{{ $index + 1 }}" @selected((int) old('rating') === $index + 1)>{{ $index + 1 }} - {{ $label }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error('rating')
                                                                <span style="color:#EA4B48;font-size:12px;">{{ $message }}</span>
                                                            @enderror
                                                        </div>

                                                        <div class="contact-form-input">
                                                            <label for="review-title">Title <span>(optional)</span></label>
                                                            <input type="text" id="review-title" name="title" value="{{ old('title') }}" placeholder="Sum up your review" maxlength="255" />
                                                            @error('title')
                                                                <span style="color:#EA4B48;font-size:12px;">{{ $message }}</span>
                                                            @enderror
                                                        </div>

                                                        <div class="contact-form-textarea" style="margin-bottom:16px;">
                                                            <label for="review-content" style="display:block;font-size:14px;line-height:21px;color:#1a1a1a;margin-bottom:6px;">Your review</label>
                                                            <textarea id="review-content" name="review" placeholder="What did you like or dislike?" required>{{ old('review') }}</textarea>
                                                            @error('review')
                                                                <span style="color:#EA4B48;font-size:12px;">{{ $message }}</span>
                                                            @enderror
                                                        </div>

                                                        <div class="contact-form-btn">
                                                            <button class="button button--md" type="submit">Submit Review</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            @endif
                                        @else
                                            <p class="font-body--md-400">
                                                <a href="{{ route('sign-in') }}">Sign in</a> to write a review.
                                            </p>
                                        @endauth
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Products Tabs End  -->

    <!-- Related Product Section   Start  -->
    @if ($relatedProducts->isNotEmpty())
        <section class="section section--xl related pt-0">
            <div class="container">
                <div class="section__head justify-content-center">
                    <h2 class="section--title-four font-title--sm">Related Products</h2>
                </div>
                <div class="swiper-container related-slider--one">
                    <div class="swiper-wrapper">
                        @foreach ($relatedProducts as $related)
                            <x-frontend.product-card :product="$related" wrapper-class="swiper-slide" />
                        @endforeach
                    </div>
                    <div class="swiper-pagination featured-pagination"></div>
                </div>
            </div>
        </section>
    @endif
    <!-- Related Product Section   end  -->

    <script src="{{ asset('lib/js/jquery.min.js') }}"></script>
    <script src="{{ asset('lib/js/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('lib/js/venobox.min.js') }}"></script>
    <script src="{{ asset('lib/js/bvselect.js') }}"></script>
    <script src="{{ asset('lib/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('js/cart.js') }}"></script>
    <script>
        // Variant switching: price, stock, SKU, gallery and Add-to-Cart
        // state all update in place from data already on the page — no
        // page reload, no new endpoint. See app/Http/Controllers/Storefront/
        // ProductController::variantsPayload() for where this JSON comes from.
        (function () {
            var variants = @json($variantsPayload);
            // Tracks whichever variant is currently selected so the
            // tier-pricing helper (triggered by +/-, manual input, or a
            // variant switch) always knows which tier list to read from.
            var currentVariantId = {{ $defaultVariant?->id ?? 'null' }};

            function formatMoney(value) {
                return '₹' + Number(value).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            // Mirrors ProductVariant::unitPriceForQuantity()'s exact selection
            // rule (see app/Models/ProductVariant.php): the highest-threshold
            // tier whose quantity is <= the given quantity, or the first
            // (lowest) tier if none qualify. variant.tiers is already sorted
            // ascending by quantity (ProductController::variantsPayload()).
            // This is the ONE place tier selection happens client-side —
            // every quantity change (+, -, manual input, variant switch)
            // goes through it, both here and initially on page load.
            function findActiveTier(tiers, quantity) {
                if (! tiers.length) {
                    return null;
                }
                var applicable = null;
                for (var i = 0; i < tiers.length; i++) {
                    if (tiers[i].quantity <= quantity) {
                        applicable = tiers[i];
                    }
                }
                return applicable || tiers[0];
            }

            function currentQuantity() {
                var counter = document.getElementById('counter-btn-counter');
                var qty = counter ? parseInt(counter.value, 10) : 1;
                return (qty && qty > 0) ? qty : 1;
            }

            // Hand-drawn in the same stroke style already used by every
            // other icon on this page (stroke="currentColor", round
            // caps/joins) — not a new icon library. No gift/tag icon
            // already existed anywhere in the codebase to reuse (checked
            // before drawing these), unlike the trust-row icons below,
            // which do reuse existing site SVGs.
            var TIER_GIFT_ICON = '<svg width="15" height="15" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">'
                + '<path d="M2.5 7.5H15.5V15C15.5 15.8284 14.8284 16.5 14 16.5H4C3.17157 16.5 2.5 15.8284 2.5 15V7.5Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round" />'
                + '<path d="M1.5 4.5H16.5V7.5H1.5V4.5Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round" />'
                + '<path d="M9 4.5V16.5" stroke="currentColor" stroke-width="1.4" />'
                + '<path d="M9 4.5C9 4.5 6.5 4.5 5.5 3.2C4.7 2.15 5.5 1 6.5 1C8 1 9 2.5 9 4.5Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round" />'
                + '<path d="M9 4.5C9 4.5 11.5 4.5 12.5 3.2C13.3 2.15 12.5 1 11.5 1C10 1 9 2.5 9 4.5Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round" />'
                + '</svg>';
            var TIER_CHECK_ICON = '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">'
                + '<path d="M13.3333 4L6 11.3333L2.66667 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />'
                + '</svg>';
            // Section 2's "Volume Price Applied!" status box icon — a larger
            // tag shape, matching the reference's more prominent icon there.
            var TIER_TAG_ICON = '<svg width="18" height="18" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">'
                + '<path d="M20 12.6L11.4 21.2C11.0343 21.5656 10.5375 21.7709 10.02 21.7709C9.50247 21.7709 9.00571 21.5656 8.64 21.2L1 13.56V4C1 2.34315 2.34315 1 4 1H13.56L20 7.44C20.7714 8.21143 20.7714 9.82857 20 10.6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />'
                + '<circle cx="6.5" cy="6.5" r="1.5" fill="currentColor" />'
                + '</svg>';

            // Renders the prominent unit price, the quantity-based pricing
            // panel, and the total/incentive banner — all from the same
            // computed state (findActiveTier() + currentQuantity() above);
            // this function only decides how to DISPLAY that state, it
            // never recomputes it differently. This is client-side UX only
            // — the server always recomputes the real price from the same
            // unitPriceForQuantity() rule when the cart is actually written
            // to (see CartService), so nothing here is ever trusted as the
            // real price.
            function applyTierPricing(variant) {
                var priceBlock = document.getElementById('price-block');
                var tiersPanel = document.getElementById('price-tiers-panel');
                var tiersList = document.getElementById('price-tiers');
                var feedback = document.getElementById('tier-pricing-feedback');
                if (! priceBlock || ! tiersPanel || ! tiersList || ! feedback) {
                    return;
                }

                if (! variant || ! variant.tiers.length) {
                    priceBlock.classList.remove('has-tiers');
                    priceBlock.innerHTML = '<div class="products__content-price__main"><h2 class="font-body--xxxl-500" id="price-current">Price unavailable</h2></div>';
                    tiersPanel.hidden = true;
                    tiersList.innerHTML = '';
                    feedback.innerHTML = '';
                    return;
                }

                var tiers = variant.tiers;
                var quantity = currentQuantity();
                var activeTier = findActiveTier(tiers, quantity);
                var activeIndex = tiers.indexOf(activeTier);
                var baseTier = tiers[0];
                // A real, currently-active volume discount — false at the
                // base (1st) tier even for a tiered variant, so qty 1-9 in
                // the example never shows a fabricated savings badge.
                var isDiscounted = variant.has_multiple_tiers && activeIndex > 0;

                priceBlock.classList.toggle('has-tiers', variant.has_multiple_tiers);

                // --- Section 1: prominent unit price + discount badges ---
                var priceHtml = '<div class="products__content-price__main">';
                priceHtml += '<h2 class="font-body--xxxl-500">';
                priceHtml += '<span id="price-current">' + formatMoney(activeTier.price) + '</span>';
                if (variant.has_multiple_tiers) {
                    priceHtml += '<span class="font-body--md-400 price-each" id="price-each">each</span>';
                }
                priceHtml += '</h2>';

                if (activeTier.compare_price) {
                    // Existing same-quantity compare_price mechanic (two
                    // tiers sharing one quantity, see pricingTiers()) — a
                    // different case from the base-vs-active comparison
                    // below, and unconditionally preserved as-is.
                    priceHtml += '<div class="price-discount-row" id="price-discount-row">'
                        + '<del class="font-body--xxl-400 price-original" id="price-compare">' + formatMoney(activeTier.compare_price) + '</del>'
                        + '</div>';
                } else if (isDiscounted) {
                    var savingsPerItem = baseTier.price - activeTier.price;
                    priceHtml += '<div class="price-discount-row" id="price-discount-row">'
                        + '<del class="font-body--xxl-400 price-original" id="price-compare">' + formatMoney(baseTier.price) + '</del>'
                        + '<span class="price-savings-pill">You save ' + formatMoney(savingsPerItem) + '</span>'
                        + '</div>';
                }
                priceHtml += '</div>';

                // Right-side "Volume Price Applied!" status box — only
                // rendered while a real discount is active, exactly like
                // the savings pill above; nothing fake shown otherwise.
                if (isDiscounted) {
                    priceHtml += '<div class="products__content-price__status">'
                        + '<span class="products__content-price__status-icon">' + TIER_TAG_ICON + '</span>'
                        + '<span class="products__content-price__status-text">Volume Price<br>Applied!</span>'
                        + '</div>';
                }

                priceBlock.innerHTML = priceHtml;

                // --- Section 2: quantity-based pricing tier panel ---------
                if (! variant.has_multiple_tiers) {
                    tiersPanel.hidden = true;
                    tiersList.innerHTML = '';
                    feedback.innerHTML = '';
                    return;
                }

                tiersPanel.hidden = false;
                tiersList.innerHTML = tiers.map(function (tier, index) {
                    var isActive = index === activeIndex;
                    var isBest = index === tiers.length - 1;
                    var badges = '';
                    if (isActive) {
                        badges += '<span class="products__content-tier-panel__badge products__content-tier-panel__badge--active">Your current price</span>';
                    }
                    if (isBest) {
                        badges += '<span class="products__content-tier-panel__badge products__content-tier-panel__badge--best">Best price</span>';
                    }
                    return '<li class="products__content-tier-panel__row' + (isActive ? ' is-active' : '') + '">'
                        + '<span class="products__content-tier-panel__qty">' + tier.quantity + '+ qty</span>'
                        + '<span class="products__content-tier-panel__price">' + formatMoney(tier.price) + ' <span class="products__content-tier-panel__unit">each</span></span>'
                        + '<span class="products__content-tier-panel__badges">' + badges + '</span>'
                        + '</li>';
                }).join('');

                // --- Section 4: next-tier/best-price banner -----------
                var nextTier = activeIndex < tiers.length - 1 ? tiers[activeIndex + 1] : null;

                var bannerHtml;
                if (nextTier) {
                    var qtyToUnlock = nextTier.quantity - quantity;
                    bannerHtml = '<div class="products__content-tier-banner">'
                        + '<span class="products__content-tier-banner__icon">' + TIER_GIFT_ICON + '</span>'
                        + '<div class="products__content-tier-banner__text">'
                        + '<p class="products__content-tier-banner__primary">Buy ' + qtyToUnlock + ' more to unlock ' + formatMoney(nextTier.price) + ' each</p>'
                        + '<p class="products__content-tier-banner__secondary">Unlock the best volume price</p>'
                        + '</div></div>';
                } else {
                    bannerHtml = '<div class="products__content-tier-banner products__content-tier-banner--success">'
                        + '<span class="products__content-tier-banner__icon">' + TIER_CHECK_ICON + '</span>'
                        + '<div class="products__content-tier-banner__text">'
                        + '<p class="products__content-tier-banner__primary">Best volume price unlocked</p>'
                        + '<p class="products__content-tier-banner__secondary">You are getting our lowest unit price.</p>'
                        + '</div></div>';
                }

                feedback.innerHTML = bannerHtml;
            }

            function renderStock(variant) {
                var badge = document.getElementById('stock-badge');
                // Only the label text updates — the checkmark icon markup
                // beside it stays put, so a variant switch never wipes it
                // out the way overwriting the whole badge's textContent
                // would.
                var badgeLabel = document.getElementById('stock-badge-label');
                if (badgeLabel) {
                    badgeLabel.textContent = variant.stock_label;
                }
                badge.classList.toggle('stock-in', variant.purchasable);
                badge.classList.toggle('stock-out', ! variant.purchasable);

                var tabStock = document.getElementById('tab-stock-status');
                if (tabStock) {
                    tabStock.textContent = variant.stock_label;
                }

                var sku = document.getElementById('variant-sku');
                if (sku) {
                    sku.textContent = variant.sku || '—';
                }

                var counter = document.getElementById('counter-btn-counter');
                if (counter) {
                    counter.max = variant.purchasable ? variant.stock_quantity : 0;
                    // Re-clamp whatever quantity was already entered to the
                    // new variant's stock, rather than leaving a value the
                    // previous variant allowed but this one doesn't.
                    if (variant.purchasable && parseInt(counter.value || '1', 10) > variant.stock_quantity) {
                        counter.value = variant.stock_quantity;
                    }
                }

                // The quantity submitted with the form is always for
                // whichever variant is currently selected — this is what
                // makes "submitted variant ID changes on switch" true.
                var variantIdField = document.getElementById('add-to-cart-variant-id');
                if (variantIdField) {
                    variantIdField.value = variant.id;
                }

                var addToCart = document.getElementById('add-to-cart-btn');
                if (addToCart) {
                    addToCart.disabled = ! variant.purchasable;
                    addToCart.setAttribute('aria-disabled', (! variant.purchasable).toString());
                }
            }

            // --- Gallery: main viewer + thumbnail rail ---------------------

            function showMainMedia(item) {
                var img = document.getElementById('main-viewer-image');
                var video = document.getElementById('main-viewer-video');
                if (! img || ! video) {
                    return;
                }

                video.pause();

                if (item.type === 'video') {
                    video.src = item.url;
                    video.hidden = false;
                    img.hidden = true;
                    img.removeAttribute('src');
                } else {
                    img.src = item.url;
                    img.hidden = false;
                    video.hidden = true;
                    video.removeAttribute('src');
                }
            }

            // Delegated (not bound per-element) so it keeps working after
            // rebuildGallery() below replaces the thumbnail nodes — a
            // direct per-element binding would silently stop firing on the
            // new elements.
            var galleryItems = document.querySelector('.gallery-items');
            if (galleryItems) {
                galleryItems.addEventListener('click', function (event) {
                    var item = event.target.closest('.gallery-item');
                    if (! item) {
                        return;
                    }

                    galleryItems.querySelectorAll('.gallery-item.active').forEach(function (el) {
                        el.classList.remove('active');
                    });
                    item.classList.add('active');
                    showMainMedia({ type: item.dataset.type, url: item.dataset.url });
                });
            }

            function slideMarkup(media, index) {
                var activeClass = index === 0 ? ' active' : '';
                if (media.type === 'video') {
                    return '<div class="gallery-item swiper-slide' + activeClass + '" data-type="video" data-url="' + media.url + '">'
                        + '<video src="' + media.url + '" muted playsinline></video></div>';
                }
                return '<div class="gallery-item swiper-slide' + activeClass + '" data-type="image" data-url="' + media.url + '">'
                    + '<img src="' + media.url + '" alt="" /></div>';
            }

            function orderMediaPrimaryFirst(media) {
                var primaryIndex = media.findIndex(function (m) { return m.is_primary; });
                if (primaryIndex <= 0) {
                    return media;
                }
                var copy = media.slice();
                var primary = copy.splice(primaryIndex, 1)[0];
                copy.unshift(primary);
                return copy;
            }

            // Captured once, before any variant switch, so the instance can
            // be rebuilt later with the exact same options main.js gave it
            // (including its responsive breakpoints) without duplicating
            // that config here and risking the two drifting apart.
            var galleryThumbsParams = (typeof productViewThumbs !== 'undefined' && productViewThumbs)
                ? Object.assign({}, productViewThumbs.params)
                : null;

            function rebuildGallery(media) {
                // Swiper 6's own slide-manipulation API — removeAllSlides(),
                // appendSlide(), removeSlide() — turned out to be
                // unreliable for this exact instance (centeredSlides +
                // vertical + responsive breakpoints + loop): three
                // different call orders were tried here, and each one threw
                // inside Swiper's own minified code at a different internal
                // property access (removeAllSlides -> updateSize reading
                // '0'; appendSlide -> reading 'append', with or without
                // loopDestroy()/loopCreate() bracketing the calls).
                //
                // Rather than keep guessing at call orders against a
                // black-box internal failure, this rebuilds the slider the
                // way Swiper's own docs recommend for wholesale content
                // replacement: destroy the instance (cleanStyles removes
                // its injected classes and loop-duplicate clones), replace
                // the real slide markup, then construct a fresh Swiper with
                // the exact params captured from the original instance.
                // Slower than patching a live instance, but it's the
                // supported path and it doesn't corrupt on repeated
                // switches — verified by switching variants back and forth
                // multiple times with no console errors and no leftover
                // duplicate slides.
                if (! galleryThumbsParams) {
                    return;
                }

                var ordered = orderMediaPrimaryFirst(media);
                var toRender = ordered.length
                    ? ordered
                    : [{ type: 'image', url: '{{ asset('images/products/img-01.png') }}' }];

                var wrapper = document.querySelector('.gallery-items-slider .swiper-wrapper');
                if (! wrapper) {
                    return;
                }

                if (productViewThumbs && typeof productViewThumbs.destroy === 'function') {
                    try {
                        productViewThumbs.destroy(true, true);
                    } catch (e) {
                        // Confirmed live: this build's destroy() can throw
                        // while detaching its own breakpoint/navigation
                        // event listeners ("removeEventListener is not a
                        // function") — a teardown-only failure. It doesn't
                        // block what actually matters here: the wrapper's
                        // real slide markup gets replaced immediately
                        // below regardless, which is what removes the old
                        // DOM (and anything still listening on it) either
                        // way, and a fresh Swiper is constructed next.
                        console.warn('Swiper destroy() during gallery rebuild raised (non-fatal, continuing):', e);
                    }
                }

                // Confirmed live: when destroy() throws (above) it can
                // exit before reaching its own cleanStyles step, leaving
                // the wrapper's old slide-position transform
                // (translateY/translateX from wherever the carousel last
                // sat) in place. The freshly constructed Swiper below
                // doesn't unconditionally clear a pre-existing transform,
                // so without this the new single slide can render
                // correctly in the DOM yet sit visually scrolled out of
                // its own container. Cleared unconditionally, not only in
                // the catch branch, since it's a harmless no-op when
                // destroy() succeeded cleanly.
                wrapper.style.transform = '';

                wrapper.innerHTML = toRender.map(slideMarkup).join('');
                productViewThumbs = new Swiper('.gallery-items-slider', galleryThumbsParams);

                showMainMedia(toRender[0]);
            }

            function selectVariant(variantId) {
                var variant = variants[variantId];
                if (! variant) {
                    return;
                }
                currentVariantId = variantId;

                document.querySelectorAll('.variant-option').forEach(function (button) {
                    var isSelected = String(button.dataset.variantId) === String(variantId);
                    button.classList.toggle('active', isSelected);
                    button.setAttribute('aria-pressed', isSelected.toString());
                });

                // renderStock() first: it re-clamps the quantity input to
                // the new variant's stock, so applyTierPricing() below
                // reads the already-clamped quantity rather than one the
                // previous variant allowed but this one doesn't.
                renderStock(variant);
                applyTierPricing(variant);
                rebuildGallery(variant.media);
            }

            document.querySelectorAll('.variant-option').forEach(function (button) {
                button.addEventListener('click', function () {
                    if (button.disabled) {
                        return;
                    }
                    selectVariant(button.dataset.variantId);
                });
            });

            // +/- buttons: increment()/decrement() (inline onclick, defined
            // in main.js) already stepped the input's value by the time this
            // fires, since inline onclick attributes are registered during
            // HTML parsing — before this script runs and adds its own
            // listener on the same click.
            document.querySelectorAll('.counter-btn').forEach(function (button) {
                button.addEventListener('click', function () {
                    applyTierPricing(variants[currentVariantId]);
                });
            });

            // Manual quantity input: stepUp()/stepDown() (used by +/-) don't
            // fire native input/change events, but typing does — this is
            // the one path applyTierPricing() needs a dedicated listener
            // for. Stock is re-clamped here the same way renderStock()
            // already clamps it after a variant switch.
            var counterInput = document.getElementById('counter-btn-counter');
            if (counterInput) {
                counterInput.addEventListener('input', function () {
                    var variant = variants[currentVariantId];
                    if (variant && variant.purchasable) {
                        var typed = parseInt(counterInput.value, 10);
                        if (typed > variant.stock_quantity) {
                            counterInput.value = variant.stock_quantity;
                        }
                    }
                    applyTierPricing(variant);
                });
            }

            // Initial render enhancement — reflects the starting quantity
            // (1) through the exact same helper as every other code path,
            // rather than trusting the server-rendered headline-tier
            // markup to already match it (it does today, since the input
            // starts at 1, but this keeps there being only one source of
            // truth for the calculation).
            applyTierPricing(variants[currentVariantId]);
        })();
    </script>
    </body>
</html>
</x-layouts.app>
