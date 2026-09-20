{{--
    Reels / video shopping + Instagram follow. Each reel is tied to a real
    product: its price comes from the linked variant, and "Add to Cart" uses
    the exact same server-side cart endpoint (and toast) as every product
    card — nothing is priced or added client-side. The Instagram link only
    appears once a profile URL is configured (Admin → Settings → Storefront
    & Offers); no feed API/token is faked.
--}}
@props(['reels', 'instagramUrl' => null])

@if ($reels->isNotEmpty() || $instagramUrl)
<section class="home-reels section section--lg">
    <div class="container">
        <div class="section__head justify-content-center">
            <h2 class="section--title-one font-title--sm text-center">Shop Our Reels</h2>
        </div>

        @if ($reels->isNotEmpty())
            <div class="home-reels__grid">
                @foreach ($reels as $reel)
                    @php
                        $product = $reel->product;
                        $variant = $reel->purchasableVariant();
                        $price = $variant?->pricingTiers()[0]['price'] ?? null;
                        $source = $reel->videoSource();
                        $poster = $reel->thumbnail ? storage_image_url($reel->thumbnail, asset('images/products/img-01.png')) : null;
                    @endphp
                    @continue(! $product)
                    <article class="home-reels__card">
                        <div class="home-reels__media">
                            @if ($source)
                                <video controls playsinline preload="none" style="max-width:100%;" @if ($poster) poster="{{ $poster }}" @endif aria-label="{{ $reel->title ?: $product->name }}">
                                    <source src="{{ $source }}">
                                </video>
                            @elseif ($reel->instagram_url)
                                <a href="{{ $reel->instagram_url }}" target="_blank" rel="noopener noreferrer" class="home-reels__poster-link">
                                    <img src="{{ $poster ?? asset('images/products/img-01.png') }}" alt="{{ $reel->title ?: $product->name }}" loading="lazy" style="max-width:100%;">
                                    <span class="home-reels__play">Watch on Instagram</span>
                                </a>
                            @else
                                <img src="{{ $poster ?? asset('images/products/img-01.png') }}" alt="{{ $reel->title ?: $product->name }}" loading="lazy" style="max-width:100%;">
                            @endif
                        </div>
                        <div class="home-reels__body">
                            <h3 class="home-reels__title font-body--md-500">{{ $reel->title ?: $product->name }}</h3>
                            <p class="home-reels__product font-body--sm-400">{{ $product->name }}@if ($price !== null) · <strong>₹{{ number_format($price, 2) }}</strong>@endif</p>
                            <div class="home-reels__actions">
                                @if ($variant)
                                    <form action="{{ route('cart.items.store') }}" method="POST" data-cart-form="add">
                                        @csrf
                                        <input type="hidden" name="product_variant_id" value="{{ $variant->id }}" />
                                        <input type="hidden" name="quantity" value="1" />
                                        <button type="submit" class="button button--md">Add to Cart</button>
                                    </form>
                                @endif
                                <a href="{{ route('products.show', $product->slug) }}" class="home-reels__view">View Product</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        @if ($instagramUrl)
            <div class="home-reels__follow">
                <p class="font-body--lg-400">Follow Cleann Organics for daily tips, launches and offers.</p>
                <a href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer" class="button button--md">Follow us on Instagram</a>
            </div>
        @endif
    </div>
</section>
@endif
