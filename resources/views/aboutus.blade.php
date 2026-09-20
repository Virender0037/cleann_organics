<x-layouts.app
    :meta-title="$page->meta_title ?: $page->title.' | Cleann Organics'"
    :meta-description="$page->meta_description"
    :canonical-url="$page->canonical_url">
        <h1 class="visually-hidden">{{ $page->title }}</h1>
        <!-- breedcrumb section start  -->
        <div class="section breedcrumb">
            <div class="breedcrumb__img-wrapper">
                <img src="{{ asset('images/banner/breedcrumb.jpg') }}" alt="breedcrumb" />
                <div class="container">
                    <ul class="breedcrumb__content">
                        <li>
                            <a href="{{ route('home') }}">
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
                        <li class="active"><a href="{{ route('aboutus') }}">About</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- breedcrumb section end   -->

        <!-- Hero Section start  -->
        <section class="hero section--xl section">
            <div class="container">
                <div class="row hero__content">
                    <div class="col-lg-9 col-xl-8 mx-auto about-story">
                        <div class="hero__text-content">
                            <h5>
                               Where Nature Meets Your Home
                            </h5>
                            <p class="info reading-text">
                                At CleannOrganics, we believe that the products you use every day should be as pure and honest as the values you live by. Founded in 2022 by Himanshu Garg, CleannOrganics was born from a simple but powerful realisation — most household and personal care products on the market are filled with harsh chemicals, synthetic fragrances, and toxic ingredients that silently harm our families, our homes, and our planet.
                            </p>
                            <p class="info reading-text">
                                We set out to change that.
                            </p>
                            <p class="info reading-text">
                                Inspired by the wisdom of Ayurveda, the power of bio-enzymes, and an unwavering commitment to sustainability, we created a range of products that are 100% natural, plant-based, and completely safe for the people you love and the world you live in.
                            </p>
                            <p class="info reading-text">
                                Today, CleannOrganics proudly offers a thoughtfully curated range of organic home cleaning and personal care products — from bio-enzyme floor cleaners and Ayurvedic dental care to plastic-free alternatives for everyday living.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Hero Section End  -->

        <x-frontend.home-benefits :items="$benefits" :threshold="$freeShippingLabel" />

        <!-- Hero Three  Section start  -->
        <section class="hero section--xl section">
            <div class="container">
                <div class="row hero__content">
                    <div class="col-lg-9 col-xl-8 mx-auto about-story">
                        <div class="hero__text-content">
                            <h6>
                                OUR PROMISE TO YOU:
                            </h6>
                            <ul class="hero__list-info">
                                <li>
                                    <span class="icon">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <rect opacity="0.1" width="20" height="20" rx="10" fill="#00B307" />
                                            <path d="M14.4168 7.125L8.68766 12.8542L6.0835 10.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                    <p>No harsh chemicals. No synthetic dyes. No toxic additives.</p>
                                </li>
                                <li>
                                    <span class="icon">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <rect opacity="0.1" width="20" height="20" rx="10" fill="#00B307" />
                                            <path d="M14.4168 7.125L8.68766 12.8542L6.0835 10.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                    <p>100% natural, plant-based &amp; Ayurvedic ingredients.</p>
                                </li>
                                <li>
                                    <span class="icon">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <rect opacity="0.1" width="20" height="20" rx="10" fill="#00B307" />
                                            <path d="M14.4168 7.125L8.68766 12.8542L6.0835 10.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                    <p>Eco-friendly, biodegradable &amp; sustainably sourced.</p>
                                </li>
                                <li>
                                    <span class="icon">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <rect opacity="0.1" width="20" height="20" rx="10" fill="#00B307" />
                                            <path d="M14.4168 7.125L8.68766 12.8542L6.0835 10.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                    <p>Safe for your family, children, pets &amp; the environment.</p>
                                </li>
                                <li>
                                    <span class="icon">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <rect opacity="0.1" width="20" height="20" rx="10" fill="#00B307" />
                                            <path d="M14.4168 7.125L8.68766 12.8542L6.0835 10.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                    <p>Made in India — with love and purpose.</p>
                                </li>
                            </ul>
                            <p class="info--two reading-text">
                                At CleannOrganics, we are not just selling products. We are building a movement — one household at a time — towards a cleaner, greener, and healthier India. Join us on this journey.
                            </p>
                            <a href="{{ route('shop') }}" class="button button--md">
                                Shop now
                                <span>
                                    <svg width="17" height="15" viewBox="0 0 17 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M16 7.50049H1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                        <path d="M9.95001 1.47559L16 7.49959L9.95001 13.5246" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Hero Three  Section End  -->

        <!-- Members Section Start  -->
        @if ($teamMembers->isNotEmpty())
        <section class="members members--two section section--lg">
            <div class="container">
                <div class="section__head section__head--one justify-content-center">
                    <h2 class="section--title-one font-title--xl">Meet Our Team</h2>
                </div>
                <div class="row justify-content-center about-team">
                    @foreach ($teamMembers as $member)
                        <div class="col-xl-3 col-lg-4 col-sm-6 mb-4">
                            <div class="cards-mb">
                                <div class="cards-mb__img-wrapper">
                                    <img src="{{ storage_image_url($member->image, asset('images/user/img-01.png')) }}" alt="{{ $member->name }}" loading="lazy" style="max-width:100%;" />
                                    <div class="cards-mb__img-wrapper--social">
                                        @if ($member->facebook_url)
                                            <span><a href="{{ $member->facebook_url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $member->name }} on Facebook"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a></span>
                                        @endif
                                        @if ($member->twitter_url)
                                            <span><a href="{{ $member->twitter_url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $member->name }} on X / Twitter"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4l16 16M20 4L4 20"/></svg></a></span>
                                        @endif
                                        @if ($member->instagram_url)
                                            <span><a href="{{ $member->instagram_url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $member->name }} on Instagram"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg></a></span>
                                        @endif
                                        @if ($member->linkedin_url)
                                            <span><a href="{{ $member->linkedin_url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $member->name }} on LinkedIn"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9v11M6 4v1M11 20v-7a3 3 0 0 1 6 0v7M11 11v9"/></svg></a></span>
                                        @endif
                                    </div>
                                </div>
                                <div class="cards-mb__designation">
                                    <h5 class="font-body--xl-500">{{ $member->name }}</h5>
                                    @if ($member->designation)<span class="font-body--md-400">{{ $member->designation }}</span>@endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif
        <!-- Members Section End   -->

        <!--  Testimonial start  -->
        <section class="testimonial testimonial--five section section--gray-0 section--xxl">
            <div class="container">
                <div class="section__head">
                    <h2 class="section--title-one font-title--xl">Client Testimonials</h2>
                    <div class="arrows">
                        <button class="arrows__btn swiper-button--prev">
                            <svg width="17" height="14" viewBox="0 0 17 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1.25 7.22607H16.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M7.30005 1.20117L1.25005 7.22517L7.30005 13.2502" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                            </svg>
                        </button>
                        <button class="arrows__btn swiper-button--next">
                            <svg width="17" height="15" viewBox="0 0 17 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16 7.50049H1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M9.95001 1.47559L16 7.49959L9.95001 13.5246" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                </div>
                <x-frontend.testimonials />
            </div>
        </section>
        <!--  Testimonial end  -->

        <script src="{{ asset('lib/js/jquery.min.js') }}"></script>
        <script src="{{ asset('lib/js/swiper-bundle.min.js') }}"></script>
        <script src="{{ asset('lib/js/bvselect.js') }}"></script>
        <script src="{{ asset('js/main.js') }}"></script>
        <!-- Purchase Button -->
        <!-- <div class="templatecookie-btn">
            <a href="https://1.envato.market/kjkaBN" target="_blank" class="purchase-btn">
                Purchase Now
                <span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </span>
            </a>
        </div> -->
    </body>
</html>
</x-layouts.app>

