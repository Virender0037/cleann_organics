{{--
    "Explore Our Range" — shop by price. Each tile links to the Shop's
    existing max_price filter, and the count comes from the same price rule
    that filter uses (ProductCatalogService::priceBands), so a tile never
    promises products the page won't list. Original Clean Organics design;
    the third-party reference app is inspiration for the layout idea only.
--}}
@props(['bands'])

<section class="explore-range section section--md">
    <div class="container">
        <div class="section__head justify-content-center">
            <h2 class="section--title-one font-title--sm text-center">Explore Our Range</h2>
        </div>
        <div class="explore-range__grid">
            @foreach ($bands as $band)
                <a href="{{ route('shop', ['max_price' => $band['max']]) }}" class="explore-range__tile" aria-label="Shop products under ₹{{ $band['max'] }}">
                    <span class="explore-range__arch">
                        <img src="{{ storage_image_url($band['image'], asset('images/products/img-01.png')) }}" alt="" loading="lazy">
                    </span>
                    <span class="explore-range__label">
                        Under ₹{{ $band['max'] }}
                        <span class="explore-range__go" aria-hidden="true">&rsaquo;</span>
                    </span>
                    @if ($band['count'] > 0)
                        <span class="explore-range__count">{{ $band['count'] }} {{ \Illuminate\Support\Str::plural('product', $band['count']) }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</section>
