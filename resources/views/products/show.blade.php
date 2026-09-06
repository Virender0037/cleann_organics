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
                        <div class="products__content-title">
                            <h2 class="font-title--md">{{ $product->name }}</h2>
                            <span class="label {{ $defaultVariant?->isPurchasable() ? 'stock-in' : 'stock-out' }}" id="stock-badge">
                                {{ $defaultVariant?->stockLabel() ?? 'Unavailable' }}
                            </span>
                        </div>
                        <div class="products__content-info">
                            <x-frontend.rating-stars size="lg" :rating="$averageRating" />
                            <a href="#pills-customer-tab" class="font-body--md-400 review-count">{{ $reviewCount }} Review{{ $reviewCount === 1 ? '' : 's' }}</a>
                            <span class="dot">.</span>
                            <h5 class="font-body--md-500">Sku: <span class="counting font-body--md-400" id="variant-sku">{{ $defaultVariant?->sku ?? '—' }}</span></h5>
                        </div>

                        <div class="products__content-price" id="price-block">
                            @if ($headlineTier)
                                <h2 class="font-body--xxxl-500">
                                    @if ($headlineTier['compare_price'])
                                        <del class="font-body--xxl-400" id="price-compare">₹{{ number_format($headlineTier['compare_price'], 2) }}</del>
                                    @endif
                                    <span id="price-current">₹{{ number_format($headlineTier['price'], 2) }}</span>
                                    @if ($defaultVariant?->hasMultipleTiers())
                                        <span class="font-body--md-400" id="price-each"> each</span>
                                    @endif
                                </h2>
                            @else
                                <h2 class="font-body--xxxl-500" id="price-current">Price unavailable</h2>
                            @endif
                        </div>
                        <ul class="font-body--md-400" id="price-tiers" style="list-style:none; padding:0; margin: 4px 0 16px;">
                            @if ($defaultVariant?->hasMultipleTiers())
                                @foreach ($defaultVariant->pricingTiers() as $tier)
                                    <li>{{ $tier['quantity'] }}+ qty — ₹{{ number_format($tier['price'], 2) }}</li>
                                @endforeach
                            @endif
                        </ul>
                        {{--
                            Populated entirely by the tier-pricing JS below (initial
                            render, +/-, manual quantity input, variant switch all go
                            through the same helper) — quantity-aware line total and
                            volume-pricing messaging, kept separate from the static
                            tier list above so that list is never rewritten.
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
                    </div>
                    <!-- brand  -->
                    <div class="products__content">
                        <div class="products__content-brand">
                            <div class="brand-name">
                                <h2 class="font-body--md-400">Brand: <span class="font-body--md-500">{{ $product->brand ?: 'Unbranded' }}</span></h2>
                            </div>
                        </div>
                        @if ($product->short_description)
                            <p class="products__content-brand-info font-body--md-400">{{ $product->short_description }}</p>
                        @endif
                    </div>
                    <!-- Action button -->
                    <div class="products__content">
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
                    </div>
                    <!-- Tags  -->
                    <div class="products__content">
                        @if ($product->category)
                            <h5 class="products__content-category font-body--md-500">Category: <a href="{{ route('category.show', $product->category->slug) }}">{{ $product->category->name }}</a></h5>
                        @endif
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

            // Renders the prominent unit price, the full static tier list,
            // and the quantity-aware total/volume messaging — this is the
            // client-side UX layer only; the server always recomputes the
            // real price from the same unitPriceForQuantity() rule when the
            // cart is actually written to (see CartService), so nothing
            // here is ever trusted as the real price.
            function applyTierPricing(variant) {
                var priceBlock = document.getElementById('price-block');
                var tiersList = document.getElementById('price-tiers');
                var feedback = document.getElementById('tier-pricing-feedback');
                if (! priceBlock || ! tiersList || ! feedback) {
                    return;
                }

                if (! variant || ! variant.tiers.length) {
                    priceBlock.innerHTML = '<h2 class="font-body--xxxl-500" id="price-current">Price unavailable</h2>';
                    tiersList.innerHTML = '';
                    feedback.innerHTML = '';
                    return;
                }

                var tiers = variant.tiers;
                var quantity = currentQuantity();
                var activeTier = findActiveTier(tiers, quantity);
                var activeIndex = tiers.indexOf(activeTier);
                var baseTier = tiers[0];

                var priceHtml = '<h2 class="font-body--xxxl-500">';
                if (activeTier.compare_price) {
                    priceHtml += '<del class="font-body--xxl-400" id="price-compare">' + formatMoney(activeTier.compare_price) + '</del>';
                }
                priceHtml += '<span id="price-current">' + formatMoney(activeTier.price) + '</span>';
                if (variant.has_multiple_tiers) {
                    priceHtml += '<span class="font-body--md-400" id="price-each"> each</span>';
                }
                priceHtml += '</h2>';
                priceBlock.innerHTML = priceHtml;

                // Static full tier list — quantity-independent, always shows
                // every tier regardless of which one is currently active.
                tiersList.innerHTML = variant.has_multiple_tiers
                    ? tiers.map(function (tier) {
                        return '<li>' + tier.quantity + '+ qty — ' + formatMoney(tier.price) + '</li>';
                    }).join('')
                    : '';

                if (! variant.has_multiple_tiers) {
                    feedback.innerHTML = '';
                    return;
                }

                var lineTotal = activeTier.price * quantity;
                var nextTier = activeIndex < tiers.length - 1 ? tiers[activeIndex + 1] : null;
                var messages = [];

                if (activeIndex > 0) {
                    var savingsPerItem = baseTier.price - activeTier.price;
                    if (nextTier) {
                        messages.push('<p class="products__content-tier-feedback__savings">Volume price applied — you save ' + formatMoney(savingsPerItem) + ' per item</p>');
                    } else {
                        messages.push('<p class="products__content-tier-feedback__savings">Best volume price applied</p>');
                        messages.push('<p class="products__content-tier-feedback__savings">You save ' + formatMoney(savingsPerItem) + ' per item compared with the ' + baseTier.quantity + '+ price</p>');
                    }
                }

                if (nextTier) {
                    var qtyToUnlock = nextTier.quantity - quantity;
                    messages.push('<p class="products__content-tier-feedback__next">Buy ' + qtyToUnlock + ' more to unlock ' + formatMoney(nextTier.price) + ' each</p>');
                }

                feedback.innerHTML = '<p class="products__content-tier-feedback__total">Total: ' + formatMoney(lineTotal) + '</p>' + messages.join('');
            }

            function renderStock(variant) {
                var badge = document.getElementById('stock-badge');
                badge.textContent = variant.stock_label;
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
