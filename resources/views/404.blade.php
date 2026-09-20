<x-layouts.app>
        <!-- breedcrumb section start  -->
        <div class="section breedcrumb">
            <div class="breedcrumb__img-wrapper">
                <img src="{{ asset('images/banner/breedcrumb.jpg') }}" alt="breedcrumb">
                <div class="container">
                    <ul class="breedcrumb__content">
                        <li>
                            <a href="index.html">
                                <svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M1 8L9 1L17 8V18H12V14C12 13.2044 11.6839 12.4413 11.1213 11.8787C10.5587 11.3161 9.79565 11 9 11C8.20435 11 7.44129 11.3161 6.87868 11.8787C6.31607 12.4413 6 13.2044 6 14V18H1V8Z"
                                        stroke="#808080"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                                <span> > </span>
                            </a>
                        </li>
                        <li class="active"><a href="404.html">404 Error Page</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- breedcrumb section end   -->

        <section class="section section--xl error">
            <div class="container">
                <div class="error__content">
                    <div class="error__content-img">
                        <img src="{{ asset('images/404.png') }}" alt="404" class="img-fluid" />
                    </div>
                    <h2 class="font-title--lg">Oops! page not found</h2>
                    <p>Ut consequat ac tortor eu vehicula. Aenean accumsan purus eros. Maecenas sagittis tortor at metus mollis</p>
                    <a href="{{route('home')}}" class="button button--md">Back to Home</a>
                </div>
            </div>
        </section>

        <script src="{{ asset('lib/js/jquery.min.js') }}"></script>
        <script src="{{ asset('lib/js/swiper-bundle.min.js') }}"></script>
        <script src="{{ asset('lib/js/bvselect.js') }}"></script>
        <script src="{{ asset('js/main.js') }}"></script>
        <!-- Purchase Button -->
        <div class="templatecookie-btn">
            <a href="https://1.envato.market/kjkaBN" target="_blank" class="purchase-btn">
                Purchase Now
                <span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </span>
            </a>
        </div>
    </body>
</html>
</x-layouts.app>
