{{--
    The homepage hero slideshow — every slide is an admin-managed
    `home_banners` row (section 'hero'; Admin → CMS → Hero Slides). One link
    per slide to its configured product / category / collection / URL.
    Initialised in js/home1.js (.home-hero__slider). Renders nothing while
    there are no active slides.
--}}
@props(['banners'])

@if ($banners->isNotEmpty())
@php
    $single = $banners->count() < 2;
    // The frame follows the FIRST slide's real proportions (clamped) instead of a
    // fixed banner ratio, so uploaded artwork is not cropped — a 16:9 photo with
    // embedded text used to lose a third of its height in an 8:3 frame.
    $first = $banners->first();
    $ratioDesktop = round(min(max($first->imageRatio() ?? (8 / 3), 1.4), 3.2), 4);
    $ratioMobile = round(min(max($first->imageRatio(true) ?? $ratioDesktop, 0.75), 1.8), 4);
@endphp
<section class="banner banner--01 home-hero" aria-label="Featured offers">
    <div class="container">
        <div class="swiper-container home-hero__slider {{ $single ? 'is-single' : '' }}" data-slides="{{ $banners->count() }}" style="--hero-ratio: {{ $ratioDesktop }}; --hero-ratio-m: {{ $ratioMobile }};">
            <div class="swiper-wrapper">
                @foreach ($banners as $banner)
                    @php
                        $fallback = asset('images/banner/banner-lg-03.jpg');
                        $desktop = storage_image_url($banner->image, $fallback);
                        $mobile = $banner->mobile_image ? storage_image_url($banner->mobile_image, $desktop) : $desktop;
                        $alt = $banner->alt_text ?: ($banner->title ?: 'Cleann Organics');
                    @endphp
                    <div class="swiper-slide">
                        <a href="{{ $banner->destinationUrl() }}"
                           class="home-hero__slide"
                           aria-label="{{ $banner->title ?: 'Shop now' }}"
                           @if ($banner->opens_new_tab) target="_blank" rel="noopener noreferrer" @endif>
                            <span class="home-hero__media">
                                <picture>
                                    <source media="(max-width: 767px)" srcset="{{ $mobile }}">
                                    <img src="{{ $desktop }}" alt="{{ $alt }}" style="max-width:100%;" @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                                </picture>
                            </span>
                            @if ($banner->title || $banner->subtitle || $banner->button_text)
                                <span class="home-hero__text home-hero__text--{{ $banner->text_position === 'right' ? 'right' : 'left' }} {{ ($banner->title || $banner->subtitle) ? '' : 'home-hero__text--cta-only' }}">
                                    @if ($banner->title)<strong class="home-hero__title">{{ $banner->title }}</strong>@endif
                                    @if ($banner->subtitle)<span class="home-hero__subtitle">{{ $banner->subtitle }}</span>@endif
                                    @if ($banner->button_text)<span class="button button--md home-hero__cta">{{ $banner->button_text }}</span>@endif
                                </span>
                            @endif
                        </a>
                    </div>
                @endforeach
            </div>
            @unless ($single)
                <button type="button" class="home-hero__nav home-hero__nav--prev" aria-label="Previous slide">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>
                </button>
                <button type="button" class="home-hero__nav home-hero__nav--next" aria-label="Next slide">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg>
                </button>
                <div class="home-hero__dots swiper-pagination"></div>
            @endunless
        </div>
    </div>
</section>
@endif
