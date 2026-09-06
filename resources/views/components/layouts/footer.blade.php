<footer class="footer footer--one">
    <div class="container">
        <div class="footer__top">
            <div class="row">
                <!-- Brand information-->
                <div class="col-lg-4">
                    <div class="footer__brand-info">
                        <div class="footer__brand-info-logo">
                            <img src="{{ ! empty($generalSettings['logo']) ? \Illuminate\Support\Facades\Storage::url($generalSettings['logo']) : asset('images/vertical-logo.jpeg') }}"
                            alt="{{ $generalSettings['site_name'] ?? 'brand-logo' }}"
                            style="height: 56px; width: auto;">
                        </div>
                        <p class="font-body--md-400">
                            Morbi cursus porttitor enim lobortis molestie. Duis gravida turpis dui, eget bibendum magna congue nec.
                        </p>
                        <div class="footer__brand-info-contact">
                            <a href="tel:{{ $generalSettings['company_phone'] ?? '+91-9999667014' }}" class="footer__brand-info-contact-item">
                                <span class="footer__brand-info-contact-icon">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                                <span>{{ $generalSettings['company_phone'] ?? '+91-9999667014' }}</span>
                            </a>
                            @if (! empty($generalSettings['company_email']))
                                <a href="mailto:{{ $generalSettings['company_email'] }}" class="footer__brand-info-contact-item">
                                    <span class="footer__brand-info-contact-icon">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M22 6l-10 7L2 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                    <span>{{ $generalSettings['company_email'] }}</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- My Account  -->
                <div class="col-lg-2 col-md-3 col-6">
                    <ul class="footer__navigation">
                        <li class="footer__navigation-title">
                            <h2 class="font-body--lg-500">My Account</h2>
                        </li>
                        <li class="footer__navigation-link">
                            <a href="#"> My Account </a>
                        </li>
                        <li class="footer__navigation-link">
                            <a href="#"> order History </a>
                        </li>
                        <li class="footer__navigation-link">
                            <a href="#"> Shoping Cart </a>
                        </li>
                        <li class="footer__navigation-link">
                            <a href="#"> Wishlist </a>
                        </li>
                    </ul>
                </div>
                <!-- Customer Help  -->
                <div class="col-lg-2 col-md-3 col-6">
                    <ul class="footer__navigation">
                        <li class="footer__navigation-title">
                            <h2 class="font-body--lg-500">Customer Help</h2>
                        </li>
                        <li class="footer__navigation-link">
                            <a href="{{ route('faq') }}"> FAQ </a>
                        </li>
                        <li class="footer__navigation-link">
                            <a href="{{ route('shipping-policy') }}"> Shipping Policy </a>
                        </li>
                        <li class="footer__navigation-link">
                            <a href="{{ route('refund-return-policy') }}"> Refund &amp; Return Policy </a>
                        </li>
                        <li class="footer__navigation-link">
                            <a href="{{ route('terms-and-conditions') }}"> Terms &amp; Conditions </a>
                        </li>
                        <li class="footer__navigation-link">
                            <a href="{{ route('privacy-policy') }}"> Privacy Policy </a>
                        </li>
                        <li class="footer__navigation-link">
                            <a href="{{ route('disclaimer') }}"> Disclaimer </a>
                        </li>
                    </ul>
                </div>
                <!-- Company -->
                <div class="col-lg-2 col-md-3 col-6">
                    <ul class="footer__navigation">
                        <li class="footer__navigation-title">
                            <h2 class="font-body--lg-500">Company</h2>
                        </li>
                        <li class="footer__navigation-link">
                            <a href="{{ route('aboutus') }}"> About Us </a>
                        </li>
                        <li class="footer__navigation-link">
                            <a href="{{ route('our-mission') }}"> Our Mission </a>
                        </li>
                        <li class="footer__navigation-link">
                            <a href="{{ route('our-story') }}"> Our Story </a>
                        </li>
                        <li class="footer__navigation-link">
                            <a href="{{ route('contact') }}"> Contact Us </a>
                        </li>
                    </ul>
                </div>
                <!-- Categories -->
                <div class="col-lg-2 col-md-3 col-6">
                    <ul class="footer__navigation">
                        <li class="footer__navigation-title">
                            <h2 class="font-body--lg-500">Categories</h2>
                        </li>
                        @foreach ($footerCategories as $footerCategory)
                            <li class="footer__navigation-link">
                                <a href="{{ route('category.show', $footerCategory->slug) }}"> {{ $footerCategory->name }} </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer__bottom">
            <p class="footer__copyright-text">
                {{ $generalSettings['site_name'] ?? 'Cleann Organics' }} © {{ now()->year }}. All Rights Reserved
            </p>
            <div class="footer__partner d-flex">
            <a href="#" class="footer__partner-item">
                <img src="{{ asset('images/brand-icon/img-01.png') }}" alt="img" />
            </a>

            <a href="#" class="footer__partner-item">
                <img src="{{ asset('images/brand-icon/img-02.png') }}" alt="img" />
            </a>

            <a href="#" class="footer__partner-item">
                <img src="{{ asset('images/brand-icon/img-03.png') }}" alt="img" />
            </a>

            <a href="#" class="footer__partner-item">
                <img src="{{ asset('images/brand-icon/img-04.png') }}" alt="img" />
            </a>

            <a href="#" class="footer__partner-item">
                <img src="{{ asset('images/brand-icon/img-05.png') }}" alt="img" />
            </a>
        </div>
        </div>
    </div>
</footer>