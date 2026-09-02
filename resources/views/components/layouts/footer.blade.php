<footer class="footer footer--one">
    <div class="container">
        <div class="footer__top">
            <div class="row">
                <!-- Brand information-->
                <div class="col-lg-4">
                    <div class="footer__brand-info">
                        <div class="footer__brand-info-logo">
                            <img src="{{ asset('images/vertical-logo.jpeg') }}"
                            alt="brand-logo"
                            style="height: 39px; width: auto;">
                        </div>
                        <p class="font-body--md-400">
                            Morbi cursus porttitor enim lobortis molestie. Duis gravida turpis dui, eget bibendum magna congue nec.
                        </p>
                        <div class="footer__brand-info-contact">
                            <a href="#"><span>(219)555-0114</span></a>
                            or
                            <a href="#"><span>Proxy@gmail.com</span></a>
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
                            <a href="{{ route('page.show', 'shipping-policy') }}"> Shipping Policy </a>
                        </li>
                        <li class="footer__navigation-link">
                            <a href="{{ route('page.show', 'refund-return-policy') }}"> Refund &amp; Return Policy </a>
                        </li>
                        <li class="footer__navigation-link">
                            <a href="{{ route('page.show', 'terms-and-conditions') }}"> Terms &amp; Conditions </a>
                        </li>
                        <li class="footer__navigation-link">
                            <a href="{{ route('page.show', 'privacy-policy') }}"> Privacy Policy </a>
                        </li>
                        <li class="footer__navigation-link">
                            <a href="{{ route('page.show', 'disclaimer') }}"> Disclaimer </a>
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
                            <a href="{{ route('page.show', 'our-mission') }}"> Our Mission </a>
                        </li>
                        <li class="footer__navigation-link">
                            <a href="{{ route('page.show', 'our-story') }}"> Our Story </a>
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
                        <li class="footer__navigation-link">
                            <a href="#"> Fruit &amp; Vegetables </a>
                        </li>
                        <li class="footer__navigation-link">
                            <a href="#"> Meat &amp; Fish </a>
                        </li>
                        <li class="footer__navigation-link">
                            <a href="#"> Bread &amp; Bakery </a>
                        </li>
                        <li class="footer__navigation-link">
                            <a href="#"> Beauty &amp; Health </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer__bottom">
            <p class="footer__copyright-text">
                Shopery eCommerce © 2021. All Rights Reserved
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