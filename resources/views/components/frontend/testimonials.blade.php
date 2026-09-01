@php
    use Illuminate\Support\Facades\Storage;

    // Routes and controllers are intentionally left untouched, so this component
    // sources its own data: every active testimonial, in the order the admin set.
    $testimonials = \App\Models\Testimonial::query()
        ->where('status', 'active')
        ->orderBy('sort_order')
        ->orderBy('id')
        ->get();

    // Same fallback the admin testimonial list already uses.
    $placeholderImage = 'https://placehold.co/60x60';
@endphp

<div class="swiper-container testimonial-slider--one">
    <div class="swiper-wrapper">
        @foreach ($testimonials as $testimonial)
            @php
                // "Designation · City", collapsing to whichever single value is
                // present so no stray separator is rendered.
                $meta = collect([$testimonial->designation, $testimonial->city])
                    ->filter(fn ($value) => filled($value))
                    ->implode(' · ');

                $hasImage = filled($testimonial->image)
                    && Storage::disk('public')->exists($testimonial->image);

                $rating = max(0, min(5, (int) $testimonial->rating));
            @endphp
            <div class="swiper-slide">
                <div class="cards-tm">
                    <p class="cards-tm__text font-body--md-400">
                        {{ $testimonial->message }}
                    </p>
                    <div class="cards-tm__info d-flex align-items-center justify-content-between">
                        <div class="cards-tm__info-left d-flex align-items-center">
                            <div class="cards-tm__info--user-img">
                                <img src="{{ $hasImage ? Storage::disk('public')->url($testimonial->image) : $placeholderImage }}" alt="{{ $testimonial->name }}">
                            </div>
                            <div class="cards-tm__info-left--designation">
                                <h2 class="font-body--lg-500">{{ $testimonial->name }}</h2>
                                @if (filled($meta))
                                    <span class="font-body--md-400">{{ $meta }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="cards-tm__info-right">
                            <ul class="d-flex align-items-center">
                                @for ($star = 1; $star <= 5; $star++)
                                    <li>
                                        <span>
                                            <svg width="12" height="13" viewBox="0 0 12 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M6.20663 9.44078L8.57101 10.9385C8.87326 11.1298 9.24826 10.8452 9.15863 10.4923L8.47576 7.80541C8.45647 7.73057 8.45869 7.6518 8.48217 7.57816C8.50566 7.50453 8.54945 7.43902 8.60851 7.38916L10.7288 5.62478C11.007 5.39303 10.8638 4.93066 10.5056 4.90741L7.73701 4.72741C7.66246 4.72212 7.59096 4.69577 7.53081 4.65142C7.47066 4.60707 7.42435 4.54656 7.39726 4.47691L6.36451 1.87666C6.33638 1.80276 6.28647 1.73916 6.22137 1.69428C6.15627 1.6494 6.07907 1.62537 6.00001 1.62537C5.92094 1.62537 5.84374 1.6494 5.77864 1.69428C5.71354 1.73916 5.66363 1.80276 5.63551 1.87666L4.60276 4.47691C4.57572 4.54663 4.52943 4.60722 4.46928 4.65164C4.40913 4.69606 4.33759 4.72246 4.26301 4.72778L1.49438 4.90778C1.13663 4.93066 0.992631 5.39303 1.27126 5.62478L3.39151 7.38953C3.4505 7.43936 3.49424 7.50481 3.51772 7.57837C3.54121 7.65193 3.54347 7.73062 3.52426 7.80541L2.89126 10.2973C2.78363 10.7207 3.23401 11.0623 3.59626 10.8324L5.79376 9.44078C5.85552 9.40152 5.92719 9.38066 6.00038 9.38066C6.07357 9.38066 6.14524 9.40152 6.20701 9.44078H6.20663Z"
                                                    fill="{{ $star <= $rating ? '#FF8A00' : '#E5E5E5' }}"
                                                ></path>
                                            </svg>
                                        </span>
                                    </li>
                                @endfor
                            </ul>
                        </div>
                    </div>
                    <span class="quotes">
                        <svg width="32" height="26" viewBox="0 0 32 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                opacity="0.3"
                                fill-rule="evenodd"
                                clip-rule="evenodd"
                                d="M23.8222 0C20.4357 0 17.6851 2.65696 17.6851 5.9336C17.6851 9.20821 20.4357 11.8672 23.8222 11.8672C29.6404 11.8672 26.2689 22.171 18.931 23.2443C18.5848 23.2936 18.2688 23.4578 18.0403 23.7071C17.8117 23.9563 17.6857 24.2742 17.6851 24.6032C17.6851 25.4456 18.487 26.1119 19.3751 25.9843C32.7122 24.0847 37.4546 0.00202497 23.8222 0.00202497V0ZM6.13933 0C2.74847 0 0 2.65493 0 5.9336C0 9.20619 2.74847 11.8631 6.13933 11.8631C11.9553 11.8631 8.58385 22.171 1.24597 23.2443C0.900119 23.2936 0.58443 23.4575 0.355931 23.7063C0.127431 23.9551 0.00118682 24.2725 0 24.6011C0 25.4436 0.801907 26.1098 1.68788 25.9823C15.0293 24.0827 19.7717 0 6.13933 0Z"
                                fill="currentColor"
                            ></path>
                        </svg>
                    </span>
                </div>
            </div>
        @endforeach
    </div>
    <div class="swiper-pagination"></div>
</div>
