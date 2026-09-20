<x-layouts.app>
        <x-frontend.home-hero :banners="$banners" />

        <x-frontend.home-benefits :items="$benefits" :threshold="$freeShippingLabel" />

        <x-frontend.home-price-bands :bands="$priceBands" />

        <!-- popular categories Section Start  -->
        <section class="popular-categories section section--md">
            <div class="container">
                <div class="section__head">
                    <h2 class="section--title-one font-title--sm">Popular Categories</h2>
                    <a href="{{ route('shop') }}">
                        View All
                        <span>
                            <svg width="17" height="15" viewBox="0 0 17 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16 7.50049H1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M9.95001 1.47559L16 7.49959L9.95001 13.5246" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </a>
                </div>
                @if ($homeCategories->isNotEmpty())
                    {{-- Desktop Versions --}}
                    <div class="popular-categories__wrapper">
                        @foreach ($homeCategories as $homeCategory)
                            <a href="{{ route('category.show', $homeCategory->slug) }}" class="cards-sm popular-categories__wrapper-item">
                                <div class="cards-sm__img-wrapper">
                                    <img src="{{ storage_image_url($homeCategory->image, asset('images/products/img-01.png')) }}" alt="{{ $homeCategory->name }}">
                                </div>
                                <h5 class="font-body--xl-500">{{ $homeCategory->name }}</h5>
                            </a>
                        @endforeach
                    </div>

                    {{-- Mobile  Versions --}}
                    <div class="swiper-container popular-categories--slider">
                        <div class="swiper-wrapper">
                            @foreach ($homeCategories as $homeCategory)
                                <div class="swiper-slide">
                                    <a href="{{ route('category.show', $homeCategory->slug) }}" class="cards-sm popular-categories__wrapper-item">
                                        <div class="cards-sm__img-wrapper">
                                            <img src="{{ storage_image_url($homeCategory->image, asset('images/products/img-01.png')) }}" alt="{{ $homeCategory->name }}">
                                        </div>
                                        <h5 class="font-body--xl-500">{{ $homeCategory->name }}</h5>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                @endif
            </div>
        </section>
        <!-- popular categories Section end  -->

        <!-- popular products Section Start  -->
        <section class="popular-products section section--md">
            <div class="container">
                <div class="section__head">
                    <h2 class="section--title-one font-title--sm">Popular products</h2>
                    <a href="{{ route('shop') }}">
                        View All
                        <span>
                            <svg width="17" height="15" viewBox="0 0 17 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16 7.50049H1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M9.95001 1.47559L16 7.49959L9.95001 13.5246" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </a>
                </div>

                <!-- Desktop Versions -->
                <div class="popular-products__wrapper">
                    @foreach ($popularProducts as $popularProduct)
                        <x-frontend.product-card
                            :product="$popularProduct"
                            :wrap="false"
                            card-class="cards-md"
                            source="homepage"
                        />
                    @endforeach
                </div>

                <!-- Mobile Versions -->
                <div class="swiper-container popular-products--slider">
                    <div class="swiper-wrapper">
                        @forelse ($popularProducts->take(6) as $popularProduct)
                            <x-frontend.product-card
                                :product="$popularProduct"
                                wrapper-class="swiper-slide"
                                card-class="cards-md w-100"
                            source="homepage"
                            />
                        @empty
                            {{-- No qualifying public product yet — the swiper stays
                                 mounted with zero slides rather than showing fake demo
                                 products. --}}
                        @endforelse
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        </section>
        <!-- popular products Section end  -->

        <x-frontend.home-product-row title="100% Bio-Enzyme Products" :products="$bioEnzymeProducts" :view-all-url="route('shop', ['tag' => \App\Services\Storefront\ProductCatalogService::TAG_BIO_ENZYME])" />

        <x-frontend.home-product-row title="365 Days Lowest Price" section-class="home-collection--lowest" :products="$lowestPriceProducts" :view-all-url="route('shop', ['tag' => \App\Services\Storefront\ProductCatalogService::TAG_LOWEST_PRICE_365])" />

        {{-- Hidden entirely while no product is genuinely discounted, so an empty heading never shows. --}}
        @if ($dealProducts->isNotEmpty())
        <!-- Deals Section Start  -->
        <section class="deals section--gray section--lg">
            <div class="container">
                <div class="section__head">
                    <h2 class="section--title-one font-title--sm">Sale of the Month</h2>
                    <a href="{{ route('shop') }}">
                        View All
                        <span>
                            <svg width="17" height="15" viewBox="0 0 17 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16 7.50049H1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M9.95001 1.47559L16 7.49959L9.95001 13.5246" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </a>
                </div>

                <!-- Desktop Versions -->
                <div class="deals-products__wrapper">
                    @php
                        $heroDeal = $dealProducts->first();
                        $restDeals = $dealProducts->slice(1)->values();
                        $dealOrdinals = ['two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve'];
                    @endphp
                    @if ($heroDeal)
                        @php
                            $heroVariant = $heroDeal->variants->first();
                            $heroTiers = $heroVariant?->pricingTiers() ?? [];
                            $heroHeadline = $heroTiers[0] ?? null;
                            $heroThumbnail = $heroDeal->thumbnailImage();
                            $heroRating = round($heroDeal->approved_average_rating ?? 0, 1);
                            $heroReviewCount = $heroDeal->approved_review_count ?? 0;
                            $heroIsWishlisted = app(\App\Services\Storefront\WishlistService::class)->isWishlisted($heroDeal->id);
                            $heroUrl = route('products.show', $heroDeal->slug);
                            // Real percent-off derived from the same headline tier's
                            // own price/compare_price rather than the old hardcoded
                            // "Sale 50%" — never a fabricated/guessed figure.
                            $heroDiscountPercent = ($heroHeadline && $heroHeadline['compare_price'])
                                ? (int) round((1 - $heroHeadline['price'] / $heroHeadline['compare_price']) * 100)
                                : null;
                        @endphp
                        <div class="cards-lg deals-products__wrapper-item deals-products__wrapper-item--one">
                            <div class="cards-lg__img-wrapper">
                                <a href="{{ $heroUrl }}">
                                    <img
                                        src="{{ storage_image_url($heroThumbnail?->image, asset('images/products/img-01.png')) }}"
                                        alt="{{ $heroDeal->name }}"
                                        loading="lazy"
                                    />
                                </a>
                                <div class="tag-group">
                                    @if ($heroDiscountPercent)
                                        <span class="tag danger">Sale {{ $heroDiscountPercent }}%</span>
                                    @endif
                                    @if ($heroDeal->is_best_seller)
                                        <span class="tag blue">Best Sale</span>
                                    @endif
                                </div>
                                <div class="cards-lg__group-action">
                                    <form
                                        action="{{ $heroIsWishlisted ? route('wishlist.destroy', $heroDeal) : route('wishlist.store') }}"
                                        method="POST"
                                        data-wishlist-form
                                    >
                                        @csrf
                                        @if ($heroIsWishlisted)
                                            @method('DELETE')
                                        @else
                                            <input type="hidden" name="product_id" value="{{ $heroDeal->id }}" />
                                        @endif
                                        <button
                                            type="submit"
                                            class="action-btn"
                                            aria-label="{{ $heroIsWishlisted ? 'Remove '.$heroDeal->name.' from wishlist' : 'Add '.$heroDeal->name.' to wishlist' }}"
                                            aria-pressed="{{ $heroIsWishlisted ? 'true' : 'false' }}"
                                        >
                                            <svg width="20" height="18" viewBox="0 0 20 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M9.9996 16.5451C-6.66672 7.3333 4.99993 -2.6667 9.9996 3.65668C14.9999 -2.6667 26.6666 7.3333 9.9996 16.5451Z" stroke="currentColor" fill="{{ $heroIsWishlisted ? 'currentColor' : 'none' }}" stroke-width="1.5"></path>
                                            </svg>
                                        </button>
                                    </form>
                                    @if ($heroVariant && $heroVariant->isPurchasable())
                                        <form action="{{ route('cart.items.store') }}" method="POST" data-cart-form="add">
                                            @csrf
                                            <input type="hidden" name="product_variant_id" value="{{ $heroVariant->id }}" />
                                            <input type="hidden" name="quantity" value="1" />
                                            <button type="submit" class="button button--md w-75">
                                                Add to cart
                                                <span>
                                                    <svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M6.66667 8.83333H4.16667L2.5 18H17.5L15.8333 8.83333H13.3333M6.66667 8.83333V6.33333C6.66667 4.49239 8.15905 3 10 3V3C11.8409 3 13.3333 4.49238 13.3333 6.33333V8.83333M6.66667 8.83333H13.3333M6.66667 8.83333V11.3333M13.3333 8.83333V11.3333"
                                                            stroke="currentColor"
                                                            stroke-width="1.3"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                        ></path>
                                                    </svg>
                                                </span>
                                            </button>
                                        </form>
                                    @else
                                        <span class="button button--md w-75" aria-disabled="true" style="opacity:0.5; cursor:not-allowed;">
                                            Out of stock
                                        </span>
                                    @endif
                                    <a class="action-btn" href="{{ $heroUrl }}" aria-label="View {{ $heroDeal->name }}">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M10 3.54102C3.75 3.54102 1.25 10.0001 1.25 10.0001C1.25 10.0001 3.75 16.4577 10 16.4577C16.25 16.4577 18.75 10.0001 18.75 10.0001C18.75 10.0001 16.25 3.54102 10 3.54102V3.54102Z"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            ></path>
                                            <path
                                                d="M10 13.125C10.8288 13.125 11.6237 12.7958 12.2097 12.2097C12.7958 11.6237 13.125 10.8288 13.125 10C13.125 9.1712 12.7958 8.37634 12.2097 7.79029C11.6237 7.20424 10.8288 6.875 10 6.875C9.1712 6.875 8.37634 7.20424 7.79029 7.79029C7.20424 8.37634 6.875 9.1712 6.875 10C6.875 10.8288 7.20424 11.6237 7.79029 12.2097C8.37634 12.7958 9.1712 13.125 10 13.125V13.125Z"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            ></path>
                                        </svg>
                                    </a>
                                </div>
                                <div class="cards-lg__favs-list">
                                    <form
                                        action="{{ $heroIsWishlisted ? route('wishlist.destroy', $heroDeal) : route('wishlist.store') }}"
                                        method="POST"
                                        data-wishlist-form
                                    >
                                        @csrf
                                        @if ($heroIsWishlisted)
                                            @method('DELETE')
                                        @else
                                            <input type="hidden" name="product_id" value="{{ $heroDeal->id }}" />
                                        @endif
                                        <button
                                            type="submit"
                                            class="action-btn"
                                            aria-label="{{ $heroIsWishlisted ? 'Remove '.$heroDeal->name.' from wishlist' : 'Add '.$heroDeal->name.' to wishlist' }}"
                                            aria-pressed="{{ $heroIsWishlisted ? 'true' : 'false' }}"
                                        >
                                            <svg width="20" height="18" viewBox="0 0 20 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M9.9996 16.5451C-6.66672 7.3333 4.99993 -2.6667 9.9996 3.65668C14.9999 -2.6667 26.6666 7.3333 9.9996 16.5451Z" stroke="currentColor" fill="{{ $heroIsWishlisted ? 'currentColor' : 'none' }}" stroke-width="1.5"></path>
                                            </svg>
                                        </button>
                                    </form>
                                    <a class="action-btn" href="{{ $heroUrl }}" aria-label="View {{ $heroDeal->name }}">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M10 3.54102C3.75 3.54102 1.25 10.0001 1.25 10.0001C1.25 10.0001 3.75 16.4577 10 16.4577C16.25 16.4577 18.75 10.0001 18.75 10.0001C18.75 10.0001 16.25 3.54102 10 3.54102V3.54102Z"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            ></path>
                                            <path
                                                d="M10 13.125C10.8288 13.125 11.6237 12.7958 12.2097 12.2097C12.7958 11.6237 13.125 10.8288 13.125 10C13.125 9.1712 12.7958 8.37634 12.2097 7.79029C11.6237 7.20424 10.8288 6.875 10 6.875C9.1712 6.875 8.37634 7.20424 7.79029 7.79029C7.20424 8.37634 6.875 9.1712 6.875 10C6.875 10.8288 7.20424 11.6237 7.79029 12.2097C8.37634 12.7958 9.1712 13.125 10 13.125V13.125Z"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            ></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                            <div class="cards-lg__info text-center">
                                <h6 class="font-body--xl-400">{{ $heroDeal->name }}</h6>
                                <div class="cards-lg__info-price">
                                    @if ($heroHeadline)
                                        <span class="font-body--xxxl-500">₹{{ number_format($heroHeadline['price'], 2) }}</span>
                                        @if ($heroHeadline['compare_price'])
                                            <del class="font-body--xxxl-400">₹{{ number_format($heroHeadline['compare_price'], 2) }}</del>
                                        @endif
                                    @else
                                        <span class="font-body--xxxl-500">Price unavailable</span>
                                    @endif
                                </div>
                                <ul class="cards-lg__info-rating d-flex justify-content-center">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <li>
                                            <svg width="12" height="13" viewBox="0 0 12 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M6.20663 9.44078L8.57101 10.9385C8.87326 11.1298 9.24826 10.8452 9.15863 10.4923L8.47576 7.80541C8.45647 7.73057 8.45869 7.6518 8.48217 7.57816C8.50566 7.50453 8.54945 7.43902 8.60851 7.38916L10.7288 5.62478C11.007 5.39303 10.8638 4.93066 10.5056 4.90741L7.73701 4.72741C7.66246 4.72212 7.59096 4.69577 7.53081 4.65142C7.47066 4.60707 7.42435 4.54656 7.39726 4.47691L6.36451 1.87666C6.33638 1.80276 6.28647 1.73916 6.22137 1.69428C6.15627 1.6494 6.07907 1.62537 6.00001 1.62537C5.92094 1.62537 5.84374 1.6494 5.77864 1.69428C5.71354 1.73916 5.66363 1.80276 5.63551 1.87666L4.60276 4.47691C4.57572 4.54663 4.52943 4.60722 4.46928 4.65164C4.40913 4.69606 4.33759 4.72246 4.26301 4.72778L1.49438 4.90778C1.13663 4.93066 0.992631 5.39303 1.27126 5.62478L3.39151 7.38953C3.4505 7.43936 3.49424 7.50481 3.51772 7.57837C3.54121 7.65193 3.54347 7.73062 3.52426 7.80541L2.89126 10.2973C2.78363 10.7207 3.23401 11.0623 3.59626 10.8324L5.79376 9.44078C5.85552 9.40152 5.92719 9.38066 6.00038 9.38066C6.07357 9.38066 6.14524 9.40152 6.20701 9.44078H6.20663Z"
                                                    fill="{{ $i <= round($heroRating) ? '#FF8A00' : '#CCCCCC' }}"
                                                ></path>
                                            </svg>
                                        </li>
                                    @endfor
                                    <li>
                                        <span>({{ $heroReviewCount }} Review{{ $heroReviewCount === 1 ? '' : 's' }})</span>
                                    </li>
                                </ul>
                                {{-- Decorative legacy countdown: there is no genuine
                                     product/deal-expiry field in the schema, so this stays
                                     static rather than showing a fabricated "ends in" claim
                                     tied to real data. See the STOREFRONT REAL-DATA AUDIT,
                                     section E. --}}
                                <div class="cards-lg__info-countdown">
                                    <h6 class="font-body--md-400">Hurry up! Offer ends In:</h6>
                                    <div id="countdownOne" class="info-countdown__card"></div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @foreach ($restDeals as $index => $dealProduct)
                        <x-frontend.product-card
                            :product="$dealProduct"
                            :wrap="false"
                            :card-class="'cards-md deals-products__wrapper-item deals-products__wrapper-item--'.($dealOrdinals[$index] ?? 'twelve')"
                            source="homepage"
                        />
                    @endforeach

                <!-- Mobile Versions -->
                <div class="swiper-container deals-products--slider">
                    <div class="swiper-wrapper">
                        @forelse ($dealProducts->take(6) as $dealProduct)
                            <x-frontend.product-card
                                :product="$dealProduct"
                                wrapper-class="swiper-slide"
                                card-class="cards-md"
                            source="homepage"
                            />
                        @empty
                            {{-- No genuinely discounted public product yet — the swiper
                                 stays mounted with zero slides rather than showing fake
                                 demo products. --}}
                        @endforelse
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        </section>
        <!-- Deals Section end  -->
        @endif



        <!-- featured  Start  -->
        <section class="section section--lg featured pb-0">
            <div class="container">
                <div class="section__head">
                    <h2 class="section--title-one font-title--sm">Featured Products</h2>
                    <a href="{{ route('shop') }}">
                        View All
                        <span>
                            <svg width="17" height="15" viewBox="0 0 17 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16 7.50049H1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M9.95001 1.47559L16 7.49959L9.95001 13.5246" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </a>
                </div>
                <div class="swiper-container featured-slider--one">
                    <div class="swiper-wrapper">
                        @forelse ($featuredProducts as $featuredProduct)
                            <x-frontend.product-card
                                :product="$featuredProduct"
                                wrapper-class="swiper-slide"
                                card-class="cards-md w-100"
                            source="homepage"
                            />
                        @empty
                            {{-- No is_featured=true public products yet — the swiper stays
                                 mounted with zero slides rather than showing fake demo
                                 products; Swiper handles an empty wrapper without erroring. --}}
                        @endforelse
                    </div>
                    <div class="swiper-pagination featured-pagination"></div>
                </div>
            </div>
        </section>
        <!-- featured  end  -->

        <!-- Latest news start  -->
        @if ($environmentalNews->isNotEmpty() || $latestBlogs->isNotEmpty())
        <section class="news section section--lg">
            <div class="container">
                <div class="section__head justify-content-center">
                    <h2 class="section--title-one font-title--sm text-center">
                        Latest News
                    </h2>
                </div>
                <div class="news-slider-wrap">
                    <button type="button" class="arrows__btn news-slider-prev" aria-label="Previous news articles">
                        <svg width="17" height="14" viewBox="0 0 17 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1.25 7.22607H16.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M7.30005 1.20117L1.25005 7.22517L7.30005 13.2502" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                    <div class="news-slider--one swiper-container">
                    <div class="swiper-wrapper">
                        @if ($environmentalNews->isNotEmpty())
                            @foreach ($environmentalNews as $article)
                                <div class="swiper-slide">
                                    <div class="cards-blog">
                                        <div class="cards-blog__img-wrapper">
                                            <img
                                                src="{{ $article->image_url ?? asset('images/blogs/blog-01.png') }}"
                                                alt="{{ $article->title }}"
                                                onerror="this.onerror=null;this.src='{{ asset('images/blogs/blog-01.png') }}'"
                                            >
                                            @if ($article->published_at)
                                                <div class="date">
                                                    <h3 class="font-body--xxl-500">{{ $article->published_at->format('d') }}</h3>
                                                    <span class="font-body--sm-500">{{ $article->published_at->format('M') }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="cards-blog__info">
                                            <div class="cards-blog__info-tags d-flex">
                                                @if ($article->category)
                                                    <div class="cards-blog__info-tags-item">
                                                        <span>
                                                            <svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M17.1583 11.6748L11.1833 17.6498C11.0285 17.8048 10.8447 17.9277 10.6424 18.0116C10.4401 18.0955 10.2232 18.1386 10.0042 18.1386C9.78513 18.1386 9.56825 18.0955 9.36592 18.0116C9.16359 17.9277 8.97978 17.8048 8.82499 17.6498L1.66666 10.4998V2.1665H9.99999L17.1583 9.32484C17.4687 9.63711 17.643 10.0595 17.643 10.4998C17.643 10.9401 17.4687 11.3626 17.1583 11.6748V11.6748Z"
                                                                    stroke="currentColor"
                                                                    stroke-width="1.2"
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round"
                                                                />
                                                                <path d="M5.83331 6.33301H5.84165" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                            </svg>
                                                        </span>
                                                        {{ $article->category }}
                                                    </div>
                                                @endif
                                                @if ($article->source)
                                                    <div class="cards-blog__info-tags-item">
                                                        <span>
                                                            <svg width="14" height="17" viewBox="0 0 14 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M6.99993 7.66667C8.84088 7.66667 10.3333 6.17428 10.3333 4.33333C10.3333 2.49238 8.84088 1 6.99993 1C5.15898 1 3.6666 2.49238 3.6666 4.33333C3.6666 6.17428 5.15898 7.66667 6.99993 7.66667Z"
                                                                    stroke="currentColor"
                                                                    stroke-width="1.2"
                                                                />
                                                                <path
                                                                    d="M9.49995 10.1665H4.49995C2.19828 10.1665 0.137447 12.2915 1.65161 14.024C2.68161 15.2023 4.38495 15.9998 6.99995 15.9998C9.61495 15.9998 11.3174 15.2023 12.3474 14.024C13.8624 12.2907 11.8008 10.1665 9.49995 10.1665Z"
                                                                    stroke="currentColor"
                                                                    stroke-width="1.2"
                                                                />
                                                            </svg>
                                                        </span>
                                                        {{ $article->source }}
                                                    </div>
                                                @endif
                                                @if ($article->published_at)
                                                    <div class="cards-blog__info-tags-item">
                                                        <span>
                                                            {{-- Relative publish time — replaces the old fake "65 Comments"
                                                                 count, which has no real equivalent for an external article. --}}
                                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.2" />
                                                                <path d="M8 4.5V8L10.5 9.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                                            </svg>
                                                        </span>
                                                        {{ $article->published_at->diffForHumans() }}
                                                    </div>
                                                @endif
                                            </div>
                                            <a href="{{ $article->article_url }}" class="blog-title font-body--xl-500" target="_blank" rel="noopener noreferrer">{{ $article->title }}</a>
                                            <a href="{{ $article->article_url }}" target="_blank" rel="noopener noreferrer">
                                                Read More
                                                <span>
                                                    <svg width="17" height="15" viewBox="0 0 17 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M16 7.50049H1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M9.95001 1.47559L16 7.49959L9.95001 13.5246" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            {{-- External news feed unavailable: show the store's own latest published blog posts
                                 (real content from Admin → CMS → Blogs) instead of hardcoded template cards. --}}
                            @foreach ($latestBlogs as $latestBlog)
                                <div class="swiper-slide">
                                    <x-frontend.blog-card :blog="$latestBlog" />
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <div class="swiper-pagination"></div>
                    </div>
                    <button type="button" class="arrows__btn news-slider-next" aria-label="Next news articles">
                        <svg width="17" height="15" viewBox="0 0 17 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16 7.50049H1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M9.95001 1.47559L16 7.49959L9.95001 13.5246" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>
            </div>
        </section>
        @endif
        <!-- Latest news end -->

        <!--  Testimonial start  -->
        <section class="testimonial section section--gray-0 section--lg">
            <div class="container">
                <div class="section__head">
                    <h2 class="section--title-one font-title--sm">Client Testimonials</h2>
                    <div class="arrows">
                        <button class="arrows__btn swiper-button--prev">
                            <svg width="17" height="14" viewBox="0 0 17 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1.25 7.22607H16.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M7.30005 1.20117L1.25005 7.22517L7.30005 13.2502" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
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

        <x-frontend.home-reels :reels="$reels" :instagram-url="$instagramUrl" />





        <script src="{{ asset('lib/js/jquery.min.js') }}"></script>
        <script src="{{ asset('lib/js/venobox.min.js') }}"></script>
        <script src="{{ asset('lib/js/swiper-bundle.min.js') }}"></script>
        <script src="{{ asset('lib/js/bvselect.js') }}"></script>
        <script src="{{ asset('lib/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('lib/js/jquery.syotimer.min.js') }}"></script>
        <script src="{{ asset('js/main.js') }}"></script>
        <script src="{{ admin_asset('js/home1.js') }}"></script>
        <script src="{{ asset('js/wishlist.js') }}"></script>
        <script src="{{ admin_asset('js/cart.js') }}"></script>
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



