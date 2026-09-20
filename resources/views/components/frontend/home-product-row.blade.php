{{--
    A tag-driven homepage collection ("100% Bio-Enzyme Products", "365 Days
    Lowest Price"). Hidden entirely while it has no products, so an
    un-curated collection never shows an empty heading.
--}}
@props(['title', 'products', 'viewAllUrl', 'sectionClass' => ''])

@if ($products->isNotEmpty())
<section class="home-collection section section--md {{ $sectionClass }}">
    <div class="container">
        <div class="section__head">
            <h2 class="section--title-one font-title--sm">{{ $title }}</h2>
            <a href="{{ $viewAllUrl }}">
                View All
                <span>
                    <svg width="17" height="15" viewBox="0 0 17 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16 7.50049H1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M9.95001 1.47559L16 7.49959L9.95001 13.5246" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
            </a>
        </div>
        <div class="home-collection__grid">
            @foreach ($products as $product)
                <x-frontend.product-card :product="$product" :wrap="false" card-class="cards-md w-100" />
            @endforeach
        </div>
    </div>
</section>
@endif
