{{--
    Benefits / trust strip under the hero — admin-managed `home_banners`
    rows (section 'benefit'; Admin → CMS → Benefits Strip), active only, in
    sort order. Desktop shows the cards in one row, tablet 2, phones a
    swipeable auto-sliding row (js/home1.js). A "{free_shipping_threshold}"
    token in a description is replaced with the live threshold, so the Free
    Shipping card can never disagree with the real shipping rule.
    Renders nothing while there are no active items.
--}}
@props(['items', 'threshold'])

@if ($items->isNotEmpty())
<section class="home-benefits" aria-label="Why shop with us">
    <div class="container">
        <div class="swiper-container home-benefits__slider">
            <div class="swiper-wrapper">
                @foreach ($items as $item)
                    @php
                        $image = ($item->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($item->image))
                            ? \Illuminate\Support\Facades\Storage::url($item->image)
                            : null;
                        $linked = $item->hasLink();
                    @endphp
                    <div class="swiper-slide">
                        @if ($linked)
                            <a href="{{ $item->destinationUrl() }}" class="home-benefits__card" @if ($item->opens_new_tab) target="_blank" rel="noopener noreferrer" @endif>
                        @else
                            <div class="home-benefits__card">
                        @endif
                            <span class="home-benefits__icon">
                                @if ($image)
                                    <img src="{{ $image }}" alt="{{ $item->alt_text }}" loading="lazy" style="max-width:48px;max-height:48px;">
                                @else
                                    <x-frontend.benefit-icon :name="$item->icon ?: 'leaf'" :size="26" />
                                @endif
                            </span>
                            <span class="home-benefits__text">
                                <span class="home-benefits__title">{{ $item->title }}</span>
                                @if ($item->subtitle)
                                    <span class="home-benefits__subtitle">{{ $item->renderedSubtitle($threshold) }}</span>
                                @endif
                            </span>
                        @if ($linked)
                            </a>
                        @else
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="home-benefits__dots swiper-pagination"></div>
        </div>
    </div>
</section>
@endif
