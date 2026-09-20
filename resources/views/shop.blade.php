<x-layouts.app
    :meta-title="$metaTitle ?? null"
    :meta-description="$metaDescription ?? null"
    :canonical-url="$canonicalUrl ?? null"
>
        <!-- breedcrumb section start  -->
        <div class="section breedcrumb">
            <div class="breedcrumb__img-wrapper">
                <img src="{{ asset('images/banner/breedcrumb.jpg') }}" alt="breedcrumb">
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
                        @if ($activeCategory)
                            <li><a href="{{ route('shop') }}">Shop<span> > </span></a></li>
                            <li class="active"><a href="{{ route('category.show', $activeCategory->slug) }}">{{ $activeCategory->name }}</a></li>
                        @else
                            <li class="active"><a href="{{ route('shop') }}">Shop</a></li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
        <!-- breedcrumb section end   -->

        <!-- Filter  -->
        <div class="filter--search">
            <div class="container">
                <div class="filter--search__content row">
                    <div class="col-lg-3 d-none d-lg-block">
                        <button class="button button--md" id="filter">
                            Filter
                            <span>
                                <svg width="22" height="19" viewBox="0 0 22 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M18 5.75C18.4142 5.75 18.75 5.41421 18.75 5C18.75 4.58579 18.4142 4.25 18 4.25V5.75ZM9 4.25C8.58579 4.25 8.25 4.58579 8.25 5C8.25 5.41421 8.58579 5.75 9 5.75V4.25ZM18 4.25H9V5.75H18V4.25Z"
                                        fill="white"
                                    />
                                    <path
                                        d="M13 14.75C13.4142 14.75 13.75 14.4142 13.75 14C13.75 13.5858 13.4142 13.25 13 13.25V14.75ZM4 13.25C3.58579 13.25 3.25 13.5858 3.25 14C3.25 14.4142 3.58579 14.75 4 14.75V13.25ZM13 13.25H4V14.75H13V13.25Z"
                                        fill="white"
                                    />
                                    <circle cx="5" cy="5" r="4" stroke="white" stroke-width="1.5" />
                                    <circle cx="17" cy="14" r="4" stroke="white" stroke-width="1.5" />
                                </svg>
                            </span>
                        </button>
                    </div>
                    <div class="col-lg-9">
                        <div class="filter--search-result">
                            <form id="sort-form" method="GET" action="{{ $activeCategory ? route('category.show', $activeCategory->slug) : route('shop') }}" class="sort-list">
                                @foreach (['search', 'category', 'tag', 'rating', 'min_price', 'max_price'] as $preserved)
                                    @if (request()->filled($preserved))
                                        <input type="hidden" name="{{ $preserved }}" value="{{ request($preserved) }}" />
                                    @endif
                                @endforeach
                                <label for="sort">Sort by:</label>
                                <select id="sort" name="sort" class="sort-list__dropmenu" onchange="this.form.submit()">
                                    <option value="newest" @selected(request('sort', 'newest') === 'newest')>Newest</option>
                                    <option value="price-asc" @selected(request('sort') === 'price-asc')>Price: Low to High</option>
                                    <option value="price-desc" @selected(request('sort') === 'price-desc')>Price: High to Low</option>
                                    <option value="name" @selected(request('sort') === 'name')>Name</option>
                                </select>
                            </form>
                            <div class="result-found">
                                <p><span class="number">{{ $products->total() }}</span> Results Found</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shop list Section Start  -->
        <section class="shop shop--one">
            <div class="container">
                <div class="row shop-content">
                    <div class="col-lg-3">
                        <div class="shop__sidebar">
                            <!-- filter button -->
                            <button class="filter">
                                <svg width="22" height="19" viewBox="0 0 22 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M18 5.75C18.4142 5.75 18.75 5.41421 18.75 5C18.75 4.58579 18.4142 4.25 18 4.25V5.75ZM9 4.25C8.58579 4.25 8.25 4.58579 8.25 5C8.25 5.41421 8.58579 5.75 9 5.75V4.25ZM18 4.25H9V5.75H18V4.25Z"
                                        fill="white"
                                    ></path>
                                    <path
                                        d="M13 14.75C13.4142 14.75 13.75 14.4142 13.75 14C13.75 13.5858 13.4142 13.25 13 13.25V14.75ZM4 13.25C3.58579 13.25 3.25 13.5858 3.25 14C3.25 14.4142 3.58579 14.75 4 14.75V13.25ZM13 13.25H4V14.75H13V13.25Z"
                                        fill="white"
                                    ></path>
                                    <circle cx="5" cy="5" r="4" stroke="white" stroke-width="1.5"></circle>
                                    <circle cx="17" cy="14" r="4" stroke="white" stroke-width="1.5"></circle>
                                </svg>
                            </button>
                            <div class="shop__sidebar-content">
                                <div class="accordion shop" id="shop">
                                    <!-- All Categories  -->
                                    <div class="accordion-item shop-item">
                                        <h2 class="accordion-header" id="shop-item-accordion--one">
                                            <button class="accordion-button shop-button font-body--xxl-500" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                                All Categories
                                                <span class="icon">
                                                    <svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M13 7L7 1L1 7" stroke="#1A1A1A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </span>
                                            </button>
                                        </h2>
                                        <div id="collapseOne" class="accordion-collapse shop-collapse collapse show show" aria-labelledby="shop-item-accordion--one" data-bs-parent="#shop">
                                            <div class="accordion-body shop-body">
                                                <div class="categories">
                                                    {{-- Category links go straight to the canonical /category/{slug}
                                                         URL (never ?category= on /shop) so a category never has two
                                                         indexable URLs. Sort/search are preserved since they still
                                                         make sense once you land on the category. --}}
                                                    <div class="categories-item">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="category-nav" id="category-all" @checked(! $activeCategory) onclick="window.location.href='{{ route('shop', request()->only(['search', 'sort', 'tag', 'rating', 'min_price', 'max_price'])) }}'" />
                                                            <label class="form-check-label" for="category-all"> All Categories </label>
                                                        </div>
                                                    </div>
                                                    @foreach ($categories as $category)
                                                        <div class="categories-item">
                                                            <div class="form-check">
                                                                <input
                                                                    class="form-check-input"
                                                                    type="radio"
                                                                    name="category-nav"
                                                                    id="category-{{ $category->id }}"
                                                                    @checked($activeCategory?->id === $category->id)
                                                                    onclick="window.location.href='{{ route('category.show', array_merge(['slug' => $category->slug], request()->only(['search', 'sort', 'tag', 'rating', 'min_price', 'max_price']))) }}'"
                                                                />
                                                                <label class="form-check-label" for="category-{{ $category->id }}">
                                                                    {{ $category->name }} <span class="current">({{ $categoryCounts[$category->id] ?? 0 }})</span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Price Range -->
                                    <div class="accordion-item shop-item">
                                        <h2 class="accordion-header" id="shop-item-accordion--two">
                                            <button class="accordion-button shop-button font-body--xxl-500 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                                Price
                                                <span class="icon">
                                                    <svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M13 7L7 1L1 7" stroke="#1A1A1A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </span>
                                            </button>
                                        </h2>
                                        <div id="collapseTwo" class="accordion-collapse shop-collapse collapse show" aria-labelledby="shop-item-accordion--two" data-bs-parent="#shop">
                                            <form id="price-filter-form" method="GET" action="{{ $activeCategory ? route('category.show', $activeCategory->slug) : route('shop') }}">
                                                @foreach (['search', 'category', 'tag', 'sort', 'rating'] as $preserved)
                                                    @if (request()->filled($preserved))
                                                        <input type="hidden" name="{{ $preserved }}" value="{{ request($preserved) }}" />
                                                    @endif
                                                @endforeach
                                                <input type="hidden" name="min_price" id="min_price" value="{{ request('min_price', $priceBounds['min']) }}" />
                                                <input type="hidden" name="max_price" id="max_price" value="{{ request('max_price', $priceBounds['max']) }}" />
                                                <div class="price-range-slider">
                                                    <div
                                                        id="priceRangeSlider"
                                                        data-min="{{ $priceBounds['min'] }}"
                                                        data-max="{{ $priceBounds['max'] }}"
                                                        data-start-min="{{ request('min_price', $priceBounds['min']) }}"
                                                        data-start-max="{{ request('max_price', $priceBounds['max']) }}"
                                                        data-form="price-filter-form"
                                                    ></div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- Rating  -->
                                <div class="accordion-item shop-item">
                                    <h2 class="accordion-header" id="shop-item-accordion--three">
                                        <button class="accordion-button shop-button font-body--xxl-500 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                            Rating
                                            <span class="icon">
                                                <svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M13 7L7 1L1 7" stroke="#1A1A1A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </span>
                                        </button>
                                    </h2>
                                    <div id="collapseThree" class="accordion-collapse shop-collapse collapse show" aria-labelledby="shop-item-accordion--three" data-bs-parent="#shop">
                                        <div class="accordion-body shop-body">
                                            <form id="rating-filter-form" method="GET" action="{{ $activeCategory ? route('category.show', $activeCategory->slug) : route('shop') }}">
                                                @foreach (['search', 'category', 'tag', 'sort', 'min_price', 'max_price'] as $preserved)
                                                    @if (request()->filled($preserved))
                                                        <input type="hidden" name="{{ $preserved }}" value="{{ request($preserved) }}" />
                                                    @endif
                                                @endforeach
                                            <div class="ratings">
                                                <div class="ratings--list-item">
                                                    <div class="form-check d-flex align-items-center">
                                                        <input class="form-check-input" type="radio" name="rating" id="five" value="5" @checked(request('rating') == 5) onchange="this.form.submit()" />
                                                        <label class="form-check-label ratings-star d-flex align-items-center" for="five">
                                                            <span>
                                                                <svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M9.31008 13.9111L12.8566 16.1577C13.31 16.4446 13.8725 16.0177 13.7381 15.4884L12.7138 11.4581C12.6848 11.3458 12.6882 11.2276 12.7234 11.1172C12.7586 11.0067 12.8243 10.9085 12.9129 10.8337L16.0933 8.18711C16.5106 7.83949 16.2958 7.14593 15.7586 7.11105L11.6056 6.84105C11.4938 6.83312 11.3866 6.79359 11.2964 6.72707C11.2061 6.66055 11.1367 6.56977 11.096 6.4653L9.5469 2.56493C9.50471 2.45408 9.42984 2.35867 9.33219 2.29136C9.23455 2.22404 9.11875 2.18799 9.00015 2.18799C8.88155 2.18799 8.76574 2.22404 8.6681 2.29136C8.57046 2.35867 8.49558 2.45408 8.4534 2.56493L6.90427 6.4653C6.86372 6.56988 6.79429 6.66077 6.70406 6.7274C6.61383 6.79402 6.50652 6.83364 6.39465 6.84161L2.24171 7.11161C1.70508 7.14593 1.48908 7.83949 1.90702 8.18711L5.0874 10.8342C5.17588 10.909 5.2415 11.0072 5.27673 11.1175C5.31195 11.2278 5.31534 11.3459 5.28652 11.4581L4.33702 15.1959C4.17558 15.8309 4.85115 16.3434 5.39452 15.9986L8.69077 13.9111C8.78342 13.8522 8.89093 13.8209 9.00071 13.8209C9.11049 13.8209 9.218 13.8522 9.31065 13.9111H9.31008Z"
                                                                        fill="#FF8A00"
                                                                    />
                                                                </svg>
                                                            </span>
                                                            <span>
                                                                <svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M9.31008 13.9111L12.8566 16.1577C13.31 16.4446 13.8725 16.0177 13.7381 15.4884L12.7138 11.4581C12.6848 11.3458 12.6882 11.2276 12.7234 11.1172C12.7586 11.0067 12.8243 10.9085 12.9129 10.8337L16.0933 8.18711C16.5106 7.83949 16.2958 7.14593 15.7586 7.11105L11.6056 6.84105C11.4938 6.83312 11.3866 6.79359 11.2964 6.72707C11.2061 6.66055 11.1367 6.56977 11.096 6.4653L9.5469 2.56493C9.50471 2.45408 9.42984 2.35867 9.33219 2.29136C9.23455 2.22404 9.11875 2.18799 9.00015 2.18799C8.88155 2.18799 8.76574 2.22404 8.6681 2.29136C8.57046 2.35867 8.49558 2.45408 8.4534 2.56493L6.90427 6.4653C6.86372 6.56988 6.79429 6.66077 6.70406 6.7274C6.61383 6.79402 6.50652 6.83364 6.39465 6.84161L2.24171 7.11161C1.70508 7.14593 1.48908 7.83949 1.90702 8.18711L5.0874 10.8342C5.17588 10.909 5.2415 11.0072 5.27673 11.1175C5.31195 11.2278 5.31534 11.3459 5.28652 11.4581L4.33702 15.1959C4.17558 15.8309 4.85115 16.3434 5.39452 15.9986L8.69077 13.9111C8.78342 13.8522 8.89093 13.8209 9.00071 13.8209C9.11049 13.8209 9.218 13.8522 9.31065 13.9111H9.31008Z"
                                                                        fill="#FF8A00"
                                                                    />
                                                                </svg>
                                                            </span>
                                                            <span>
                                                                <svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M9.31008 13.9111L12.8566 16.1577C13.31 16.4446 13.8725 16.0177 13.7381 15.4884L12.7138 11.4581C12.6848 11.3458 12.6882 11.2276 12.7234 11.1172C12.7586 11.0067 12.8243 10.9085 12.9129 10.8337L16.0933 8.18711C16.5106 7.83949 16.2958 7.14593 15.7586 7.11105L11.6056 6.84105C11.4938 6.83312 11.3866 6.79359 11.2964 6.72707C11.2061 6.66055 11.1367 6.56977 11.096 6.4653L9.5469 2.56493C9.50471 2.45408 9.42984 2.35867 9.33219 2.29136C9.23455 2.22404 9.11875 2.18799 9.00015 2.18799C8.88155 2.18799 8.76574 2.22404 8.6681 2.29136C8.57046 2.35867 8.49558 2.45408 8.4534 2.56493L6.90427 6.4653C6.86372 6.56988 6.79429 6.66077 6.70406 6.7274C6.61383 6.79402 6.50652 6.83364 6.39465 6.84161L2.24171 7.11161C1.70508 7.14593 1.48908 7.83949 1.90702 8.18711L5.0874 10.8342C5.17588 10.909 5.2415 11.0072 5.27673 11.1175C5.31195 11.2278 5.31534 11.3459 5.28652 11.4581L4.33702 15.1959C4.17558 15.8309 4.85115 16.3434 5.39452 15.9986L8.69077 13.9111C8.78342 13.8522 8.89093 13.8209 9.00071 13.8209C9.11049 13.8209 9.218 13.8522 9.31065 13.9111H9.31008Z"
                                                                        fill="#FF8A00"
                                                                    />
                                                                </svg>
                                                            </span>
                                                            <span>
                                                                <svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M9.31008 13.9111L12.8566 16.1577C13.31 16.4446 13.8725 16.0177 13.7381 15.4884L12.7138 11.4581C12.6848 11.3458 12.6882 11.2276 12.7234 11.1172C12.7586 11.0067 12.8243 10.9085 12.9129 10.8337L16.0933 8.18711C16.5106 7.83949 16.2958 7.14593 15.7586 7.11105L11.6056 6.84105C11.4938 6.83312 11.3866 6.79359 11.2964 6.72707C11.2061 6.66055 11.1367 6.56977 11.096 6.4653L9.5469 2.56493C9.50471 2.45408 9.42984 2.35867 9.33219 2.29136C9.23455 2.22404 9.11875 2.18799 9.00015 2.18799C8.88155 2.18799 8.76574 2.22404 8.6681 2.29136C8.57046 2.35867 8.49558 2.45408 8.4534 2.56493L6.90427 6.4653C6.86372 6.56988 6.79429 6.66077 6.70406 6.7274C6.61383 6.79402 6.50652 6.83364 6.39465 6.84161L2.24171 7.11161C1.70508 7.14593 1.48908 7.83949 1.90702 8.18711L5.0874 10.8342C5.17588 10.909 5.2415 11.0072 5.27673 11.1175C5.31195 11.2278 5.31534 11.3459 5.28652 11.4581L4.33702 15.1959C4.17558 15.8309 4.85115 16.3434 5.39452 15.9986L8.69077 13.9111C8.78342 13.8522 8.89093 13.8209 9.00071 13.8209C9.11049 13.8209 9.218 13.8522 9.31065 13.9111H9.31008Z"
                                                                        fill="#FF8A00"
                                                                    />
                                                                </svg>
                                                            </span>
                                                            <span>
                                                                <svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M9.31008 13.9111L12.8566 16.1577C13.31 16.4446 13.8725 16.0177 13.7381 15.4884L12.7138 11.4581C12.6848 11.3458 12.6882 11.2276 12.7234 11.1172C12.7586 11.0067 12.8243 10.9085 12.9129 10.8337L16.0933 8.18711C16.5106 7.83949 16.2958 7.14593 15.7586 7.11105L11.6056 6.84105C11.4938 6.83312 11.3866 6.79359 11.2964 6.72707C11.2061 6.66055 11.1367 6.56977 11.096 6.4653L9.5469 2.56493C9.50471 2.45408 9.42984 2.35867 9.33219 2.29136C9.23455 2.22404 9.11875 2.18799 9.00015 2.18799C8.88155 2.18799 8.76574 2.22404 8.6681 2.29136C8.57046 2.35867 8.49558 2.45408 8.4534 2.56493L6.90427 6.4653C6.86372 6.56988 6.79429 6.66077 6.70406 6.7274C6.61383 6.79402 6.50652 6.83364 6.39465 6.84161L2.24171 7.11161C1.70508 7.14593 1.48908 7.83949 1.90702 8.18711L5.0874 10.8342C5.17588 10.909 5.2415 11.0072 5.27673 11.1175C5.31195 11.2278 5.31534 11.3459 5.28652 11.4581L4.33702 15.1959C4.17558 15.8309 4.85115 16.3434 5.39452 15.9986L8.69077 13.9111C8.78342 13.8522 8.89093 13.8209 9.00071 13.8209C9.11049 13.8209 9.218 13.8522 9.31065 13.9111H9.31008Z"
                                                                        fill="#FF8A00"
                                                                    />
                                                                </svg>
                                                            </span>
                                                        </label>
                                                        <span class="star-num">5.0</span>
                                                    </div>
                                                </div>
                                                <div class="ratings--list-item">
                                                    <div class="form-check d-flex align-items-center">
                                                        <input class="form-check-input" type="radio" name="rating" id="four" value="4" @checked(request('rating') == 4) onchange="this.form.submit()" />
                                                        <label class="form-check-label ratings-star d-flex align-items-center" for="four">
                                                            <span>
                                                                <svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M9.31008 13.9111L12.8566 16.1577C13.31 16.4446 13.8725 16.0177 13.7381 15.4884L12.7138 11.4581C12.6848 11.3458 12.6882 11.2276 12.7234 11.1172C12.7586 11.0067 12.8243 10.9085 12.9129 10.8337L16.0933 8.18711C16.5106 7.83949 16.2958 7.14593 15.7586 7.11105L11.6056 6.84105C11.4938 6.83312 11.3866 6.79359 11.2964 6.72707C11.2061 6.66055 11.1367 6.56977 11.096 6.4653L9.5469 2.56493C9.50471 2.45408 9.42984 2.35867 9.33219 2.29136C9.23455 2.22404 9.11875 2.18799 9.00015 2.18799C8.88155 2.18799 8.76574 2.22404 8.6681 2.29136C8.57046 2.35867 8.49558 2.45408 8.4534 2.56493L6.90427 6.4653C6.86372 6.56988 6.79429 6.66077 6.70406 6.7274C6.61383 6.79402 6.50652 6.83364 6.39465 6.84161L2.24171 7.11161C1.70508 7.14593 1.48908 7.83949 1.90702 8.18711L5.0874 10.8342C5.17588 10.909 5.2415 11.0072 5.27673 11.1175C5.31195 11.2278 5.31534 11.3459 5.28652 11.4581L4.33702 15.1959C4.17558 15.8309 4.85115 16.3434 5.39452 15.9986L8.69077 13.9111C8.78342 13.8522 8.89093 13.8209 9.00071 13.8209C9.11049 13.8209 9.218 13.8522 9.31065 13.9111H9.31008Z"
                                                                        fill="#FF8A00"
                                                                    />
                                                                </svg>
                                                            </span>
                                                            <span>
                                                                <svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M9.31008 13.9111L12.8566 16.1577C13.31 16.4446 13.8725 16.0177 13.7381 15.4884L12.7138 11.4581C12.6848 11.3458 12.6882 11.2276 12.7234 11.1172C12.7586 11.0067 12.8243 10.9085 12.9129 10.8337L16.0933 8.18711C16.5106 7.83949 16.2958 7.14593 15.7586 7.11105L11.6056 6.84105C11.4938 6.83312 11.3866 6.79359 11.2964 6.72707C11.2061 6.66055 11.1367 6.56977 11.096 6.4653L9.5469 2.56493C9.50471 2.45408 9.42984 2.35867 9.33219 2.29136C9.23455 2.22404 9.11875 2.18799 9.00015 2.18799C8.88155 2.18799 8.76574 2.22404 8.6681 2.29136C8.57046 2.35867 8.49558 2.45408 8.4534 2.56493L6.90427 6.4653C6.86372 6.56988 6.79429 6.66077 6.70406 6.7274C6.61383 6.79402 6.50652 6.83364 6.39465 6.84161L2.24171 7.11161C1.70508 7.14593 1.48908 7.83949 1.90702 8.18711L5.0874 10.8342C5.17588 10.909 5.2415 11.0072 5.27673 11.1175C5.31195 11.2278 5.31534 11.3459 5.28652 11.4581L4.33702 15.1959C4.17558 15.8309 4.85115 16.3434 5.39452 15.9986L8.69077 13.9111C8.78342 13.8522 8.89093 13.8209 9.00071 13.8209C9.11049 13.8209 9.218 13.8522 9.31065 13.9111H9.31008Z"
                                                                        fill="#FF8A00"
                                                                    />
                                                                </svg>
                                                            </span>
                                                            <span>
                                                                <svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M9.31008 13.9111L12.8566 16.1577C13.31 16.4446 13.8725 16.0177 13.7381 15.4884L12.7138 11.4581C12.6848 11.3458 12.6882 11.2276 12.7234 11.1172C12.7586 11.0067 12.8243 10.9085 12.9129 10.8337L16.0933 8.18711C16.5106 7.83949 16.2958 7.14593 15.7586 7.11105L11.6056 6.84105C11.4938 6.83312 11.3866 6.79359 11.2964 6.72707C11.2061 6.66055 11.1367 6.56977 11.096 6.4653L9.5469 2.56493C9.50471 2.45408 9.42984 2.35867 9.33219 2.29136C9.23455 2.22404 9.11875 2.18799 9.00015 2.18799C8.88155 2.18799 8.76574 2.22404 8.6681 2.29136C8.57046 2.35867 8.49558 2.45408 8.4534 2.56493L6.90427 6.4653C6.86372 6.56988 6.79429 6.66077 6.70406 6.7274C6.61383 6.79402 6.50652 6.83364 6.39465 6.84161L2.24171 7.11161C1.70508 7.14593 1.48908 7.83949 1.90702 8.18711L5.0874 10.8342C5.17588 10.909 5.2415 11.0072 5.27673 11.1175C5.31195 11.2278 5.31534 11.3459 5.28652 11.4581L4.33702 15.1959C4.17558 15.8309 4.85115 16.3434 5.39452 15.9986L8.69077 13.9111C8.78342 13.8522 8.89093 13.8209 9.00071 13.8209C9.11049 13.8209 9.218 13.8522 9.31065 13.9111H9.31008Z"
                                                                        fill="#FF8A00"
                                                                    />
                                                                </svg>
                                                            </span>
                                                            <span>
                                                                <svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M9.31008 13.9111L12.8566 16.1577C13.31 16.4446 13.8725 16.0177 13.7381 15.4884L12.7138 11.4581C12.6848 11.3458 12.6882 11.2276 12.7234 11.1172C12.7586 11.0067 12.8243 10.9085 12.9129 10.8337L16.0933 8.18711C16.5106 7.83949 16.2958 7.14593 15.7586 7.11105L11.6056 6.84105C11.4938 6.83312 11.3866 6.79359 11.2964 6.72707C11.2061 6.66055 11.1367 6.56977 11.096 6.4653L9.5469 2.56493C9.50471 2.45408 9.42984 2.35867 9.33219 2.29136C9.23455 2.22404 9.11875 2.18799 9.00015 2.18799C8.88155 2.18799 8.76574 2.22404 8.6681 2.29136C8.57046 2.35867 8.49558 2.45408 8.4534 2.56493L6.90427 6.4653C6.86372 6.56988 6.79429 6.66077 6.70406 6.7274C6.61383 6.79402 6.50652 6.83364 6.39465 6.84161L2.24171 7.11161C1.70508 7.14593 1.48908 7.83949 1.90702 8.18711L5.0874 10.8342C5.17588 10.909 5.2415 11.0072 5.27673 11.1175C5.31195 11.2278 5.31534 11.3459 5.28652 11.4581L4.33702 15.1959C4.17558 15.8309 4.85115 16.3434 5.39452 15.9986L8.69077 13.9111C8.78342 13.8522 8.89093 13.8209 9.00071 13.8209C9.11049 13.8209 9.218 13.8522 9.31065 13.9111H9.31008Z"
                                                                        fill="#FF8A00"
                                                                    />
                                                                </svg>
                                                            </span>
                                                            <span>
                                                                <svg width="16" height="15" viewBox="0 0 16 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M8.31008 11.9111L11.8566 14.1577C12.31 14.4446 12.8725 14.0177 12.7381 13.4884L11.7138 9.45805C11.6848 9.34579 11.6882 9.22764 11.7234 9.11718C11.7586 9.00673 11.8243 8.90846 11.9129 8.83368L15.0933 6.18711C15.5106 5.83949 15.2958 5.14593 14.7586 5.11105L10.6056 4.84105C10.4938 4.83312 10.3866 4.79359 10.2964 4.72707C10.2061 4.66055 10.1367 4.56977 10.096 4.4653L8.5469 0.564927C8.50471 0.454081 8.42984 0.358673 8.33219 0.291355C8.23455 0.224037 8.11875 0.187988 8.00015 0.187988C7.88155 0.187988 7.76574 0.224037 7.6681 0.291355C7.57046 0.358673 7.49558 0.454081 7.4534 0.564927L5.90427 4.4653C5.86372 4.56988 5.79429 4.66077 5.70406 4.7274C5.61383 4.79402 5.50652 4.83364 5.39465 4.84161L1.24171 5.11161C0.705084 5.14593 0.489084 5.83949 0.907022 6.18711L4.0874 8.83424C4.17588 8.90898 4.2415 9.00715 4.27673 9.11749C4.31195 9.22783 4.31534 9.34587 4.28652 9.45805L3.33702 13.1959C3.17558 13.8309 3.85115 14.3434 4.39452 13.9986L7.69077 11.9111C7.78342 11.8522 7.89093 11.8209 8.00071 11.8209C8.11049 11.8209 8.218 11.8522 8.31065 11.9111H8.31008Z"
                                                                        fill="#CCCCCC"
                                                                    />
                                                                </svg>
                                                            </span>
                                                        </label>
                                                        <span class="star-num">4.0 & up</span>
                                                    </div>
                                                </div>
                                                <div class="ratings--list-item">
                                                    <div class="form-check d-flex align-items-center">
                                                        <input class="form-check-input" type="radio" name="rating" id="three" value="3" @checked(request('rating') == 3) onchange="this.form.submit()" />
                                                        <label class="form-check-label ratings-star d-flex align-items-center" for="three">
                                                            <span>
                                                                <svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M9.31008 13.9111L12.8566 16.1577C13.31 16.4446 13.8725 16.0177 13.7381 15.4884L12.7138 11.4581C12.6848 11.3458 12.6882 11.2276 12.7234 11.1172C12.7586 11.0067 12.8243 10.9085 12.9129 10.8337L16.0933 8.18711C16.5106 7.83949 16.2958 7.14593 15.7586 7.11105L11.6056 6.84105C11.4938 6.83312 11.3866 6.79359 11.2964 6.72707C11.2061 6.66055 11.1367 6.56977 11.096 6.4653L9.5469 2.56493C9.50471 2.45408 9.42984 2.35867 9.33219 2.29136C9.23455 2.22404 9.11875 2.18799 9.00015 2.18799C8.88155 2.18799 8.76574 2.22404 8.6681 2.29136C8.57046 2.35867 8.49558 2.45408 8.4534 2.56493L6.90427 6.4653C6.86372 6.56988 6.79429 6.66077 6.70406 6.7274C6.61383 6.79402 6.50652 6.83364 6.39465 6.84161L2.24171 7.11161C1.70508 7.14593 1.48908 7.83949 1.90702 8.18711L5.0874 10.8342C5.17588 10.909 5.2415 11.0072 5.27673 11.1175C5.31195 11.2278 5.31534 11.3459 5.28652 11.4581L4.33702 15.1959C4.17558 15.8309 4.85115 16.3434 5.39452 15.9986L8.69077 13.9111C8.78342 13.8522 8.89093 13.8209 9.00071 13.8209C9.11049 13.8209 9.218 13.8522 9.31065 13.9111H9.31008Z"
                                                                        fill="#FF8A00"
                                                                    />
                                                                </svg>
                                                            </span>
                                                            <span>
                                                                <svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M9.31008 13.9111L12.8566 16.1577C13.31 16.4446 13.8725 16.0177 13.7381 15.4884L12.7138 11.4581C12.6848 11.3458 12.6882 11.2276 12.7234 11.1172C12.7586 11.0067 12.8243 10.9085 12.9129 10.8337L16.0933 8.18711C16.5106 7.83949 16.2958 7.14593 15.7586 7.11105L11.6056 6.84105C11.4938 6.83312 11.3866 6.79359 11.2964 6.72707C11.2061 6.66055 11.1367 6.56977 11.096 6.4653L9.5469 2.56493C9.50471 2.45408 9.42984 2.35867 9.33219 2.29136C9.23455 2.22404 9.11875 2.18799 9.00015 2.18799C8.88155 2.18799 8.76574 2.22404 8.6681 2.29136C8.57046 2.35867 8.49558 2.45408 8.4534 2.56493L6.90427 6.4653C6.86372 6.56988 6.79429 6.66077 6.70406 6.7274C6.61383 6.79402 6.50652 6.83364 6.39465 6.84161L2.24171 7.11161C1.70508 7.14593 1.48908 7.83949 1.90702 8.18711L5.0874 10.8342C5.17588 10.909 5.2415 11.0072 5.27673 11.1175C5.31195 11.2278 5.31534 11.3459 5.28652 11.4581L4.33702 15.1959C4.17558 15.8309 4.85115 16.3434 5.39452 15.9986L8.69077 13.9111C8.78342 13.8522 8.89093 13.8209 9.00071 13.8209C9.11049 13.8209 9.218 13.8522 9.31065 13.9111H9.31008Z"
                                                                        fill="#FF8A00"
                                                                    />
                                                                </svg>
                                                            </span>
                                                            <span>
                                                                <svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M9.31008 13.9111L12.8566 16.1577C13.31 16.4446 13.8725 16.0177 13.7381 15.4884L12.7138 11.4581C12.6848 11.3458 12.6882 11.2276 12.7234 11.1172C12.7586 11.0067 12.8243 10.9085 12.9129 10.8337L16.0933 8.18711C16.5106 7.83949 16.2958 7.14593 15.7586 7.11105L11.6056 6.84105C11.4938 6.83312 11.3866 6.79359 11.2964 6.72707C11.2061 6.66055 11.1367 6.56977 11.096 6.4653L9.5469 2.56493C9.50471 2.45408 9.42984 2.35867 9.33219 2.29136C9.23455 2.22404 9.11875 2.18799 9.00015 2.18799C8.88155 2.18799 8.76574 2.22404 8.6681 2.29136C8.57046 2.35867 8.49558 2.45408 8.4534 2.56493L6.90427 6.4653C6.86372 6.56988 6.79429 6.66077 6.70406 6.7274C6.61383 6.79402 6.50652 6.83364 6.39465 6.84161L2.24171 7.11161C1.70508 7.14593 1.48908 7.83949 1.90702 8.18711L5.0874 10.8342C5.17588 10.909 5.2415 11.0072 5.27673 11.1175C5.31195 11.2278 5.31534 11.3459 5.28652 11.4581L4.33702 15.1959C4.17558 15.8309 4.85115 16.3434 5.39452 15.9986L8.69077 13.9111C8.78342 13.8522 8.89093 13.8209 9.00071 13.8209C9.11049 13.8209 9.218 13.8522 9.31065 13.9111H9.31008Z"
                                                                        fill="#FF8A00"
                                                                    />
                                                                </svg>
                                                            </span>

                                                            <span>
                                                                <svg width="16" height="15" viewBox="0 0 16 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M8.31008 11.9111L11.8566 14.1577C12.31 14.4446 12.8725 14.0177 12.7381 13.4884L11.7138 9.45805C11.6848 9.34579 11.6882 9.22764 11.7234 9.11718C11.7586 9.00673 11.8243 8.90846 11.9129 8.83368L15.0933 6.18711C15.5106 5.83949 15.2958 5.14593 14.7586 5.11105L10.6056 4.84105C10.4938 4.83312 10.3866 4.79359 10.2964 4.72707C10.2061 4.66055 10.1367 4.56977 10.096 4.4653L8.5469 0.564927C8.50471 0.454081 8.42984 0.358673 8.33219 0.291355C8.23455 0.224037 8.11875 0.187988 8.00015 0.187988C7.88155 0.187988 7.76574 0.224037 7.6681 0.291355C7.57046 0.358673 7.49558 0.454081 7.4534 0.564927L5.90427 4.4653C5.86372 4.56988 5.79429 4.66077 5.70406 4.7274C5.61383 4.79402 5.50652 4.83364 5.39465 4.84161L1.24171 5.11161C0.705084 5.14593 0.489084 5.83949 0.907022 6.18711L4.0874 8.83424C4.17588 8.90898 4.2415 9.00715 4.27673 9.11749C4.31195 9.22783 4.31534 9.34587 4.28652 9.45805L3.33702 13.1959C3.17558 13.8309 3.85115 14.3434 4.39452 13.9986L7.69077 11.9111C7.78342 11.8522 7.89093 11.8209 8.00071 11.8209C8.11049 11.8209 8.218 11.8522 8.31065 11.9111H8.31008Z"
                                                                        fill="#CCCCCC"
                                                                    />
                                                                </svg>
                                                            </span>
                                                            <span>
                                                                <svg width="16" height="15" viewBox="0 0 16 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M8.31008 11.9111L11.8566 14.1577C12.31 14.4446 12.8725 14.0177 12.7381 13.4884L11.7138 9.45805C11.6848 9.34579 11.6882 9.22764 11.7234 9.11718C11.7586 9.00673 11.8243 8.90846 11.9129 8.83368L15.0933 6.18711C15.5106 5.83949 15.2958 5.14593 14.7586 5.11105L10.6056 4.84105C10.4938 4.83312 10.3866 4.79359 10.2964 4.72707C10.2061 4.66055 10.1367 4.56977 10.096 4.4653L8.5469 0.564927C8.50471 0.454081 8.42984 0.358673 8.33219 0.291355C8.23455 0.224037 8.11875 0.187988 8.00015 0.187988C7.88155 0.187988 7.76574 0.224037 7.6681 0.291355C7.57046 0.358673 7.49558 0.454081 7.4534 0.564927L5.90427 4.4653C5.86372 4.56988 5.79429 4.66077 5.70406 4.7274C5.61383 4.79402 5.50652 4.83364 5.39465 4.84161L1.24171 5.11161C0.705084 5.14593 0.489084 5.83949 0.907022 6.18711L4.0874 8.83424C4.17588 8.90898 4.2415 9.00715 4.27673 9.11749C4.31195 9.22783 4.31534 9.34587 4.28652 9.45805L3.33702 13.1959C3.17558 13.8309 3.85115 14.3434 4.39452 13.9986L7.69077 11.9111C7.78342 11.8522 7.89093 11.8209 8.00071 11.8209C8.11049 11.8209 8.218 11.8522 8.31065 11.9111H8.31008Z"
                                                                        fill="#CCCCCC"
                                                                    />
                                                                </svg>
                                                            </span>
                                                        </label>
                                                        <span class="star-num">3.0 & up</span>
                                                    </div>
                                                </div>
                                                <div class="ratings--list-item">
                                                    <div class="form-check d-flex align-items-center">
                                                        <input class="form-check-input" type="radio" name="rating" id="two" value="2" @checked(request('rating') == 2) onchange="this.form.submit()" />
                                                        <label class="form-check-label ratings-star d-flex align-items-center" for="two">
                                                            <span>
                                                                <svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M9.31008 13.9111L12.8566 16.1577C13.31 16.4446 13.8725 16.0177 13.7381 15.4884L12.7138 11.4581C12.6848 11.3458 12.6882 11.2276 12.7234 11.1172C12.7586 11.0067 12.8243 10.9085 12.9129 10.8337L16.0933 8.18711C16.5106 7.83949 16.2958 7.14593 15.7586 7.11105L11.6056 6.84105C11.4938 6.83312 11.3866 6.79359 11.2964 6.72707C11.2061 6.66055 11.1367 6.56977 11.096 6.4653L9.5469 2.56493C9.50471 2.45408 9.42984 2.35867 9.33219 2.29136C9.23455 2.22404 9.11875 2.18799 9.00015 2.18799C8.88155 2.18799 8.76574 2.22404 8.6681 2.29136C8.57046 2.35867 8.49558 2.45408 8.4534 2.56493L6.90427 6.4653C6.86372 6.56988 6.79429 6.66077 6.70406 6.7274C6.61383 6.79402 6.50652 6.83364 6.39465 6.84161L2.24171 7.11161C1.70508 7.14593 1.48908 7.83949 1.90702 8.18711L5.0874 10.8342C5.17588 10.909 5.2415 11.0072 5.27673 11.1175C5.31195 11.2278 5.31534 11.3459 5.28652 11.4581L4.33702 15.1959C4.17558 15.8309 4.85115 16.3434 5.39452 15.9986L8.69077 13.9111C8.78342 13.8522 8.89093 13.8209 9.00071 13.8209C9.11049 13.8209 9.218 13.8522 9.31065 13.9111H9.31008Z"
                                                                        fill="#FF8A00"
                                                                    />
                                                                </svg>
                                                            </span>
                                                            <span>
                                                                <svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M9.31008 13.9111L12.8566 16.1577C13.31 16.4446 13.8725 16.0177 13.7381 15.4884L12.7138 11.4581C12.6848 11.3458 12.6882 11.2276 12.7234 11.1172C12.7586 11.0067 12.8243 10.9085 12.9129 10.8337L16.0933 8.18711C16.5106 7.83949 16.2958 7.14593 15.7586 7.11105L11.6056 6.84105C11.4938 6.83312 11.3866 6.79359 11.2964 6.72707C11.2061 6.66055 11.1367 6.56977 11.096 6.4653L9.5469 2.56493C9.50471 2.45408 9.42984 2.35867 9.33219 2.29136C9.23455 2.22404 9.11875 2.18799 9.00015 2.18799C8.88155 2.18799 8.76574 2.22404 8.6681 2.29136C8.57046 2.35867 8.49558 2.45408 8.4534 2.56493L6.90427 6.4653C6.86372 6.56988 6.79429 6.66077 6.70406 6.7274C6.61383 6.79402 6.50652 6.83364 6.39465 6.84161L2.24171 7.11161C1.70508 7.14593 1.48908 7.83949 1.90702 8.18711L5.0874 10.8342C5.17588 10.909 5.2415 11.0072 5.27673 11.1175C5.31195 11.2278 5.31534 11.3459 5.28652 11.4581L4.33702 15.1959C4.17558 15.8309 4.85115 16.3434 5.39452 15.9986L8.69077 13.9111C8.78342 13.8522 8.89093 13.8209 9.00071 13.8209C9.11049 13.8209 9.218 13.8522 9.31065 13.9111H9.31008Z"
                                                                        fill="#FF8A00"
                                                                    />
                                                                </svg>
                                                            </span>
                                                            <span>
                                                                <svg width="16" height="15" viewBox="0 0 16 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M8.31008 11.9111L11.8566 14.1577C12.31 14.4446 12.8725 14.0177 12.7381 13.4884L11.7138 9.45805C11.6848 9.34579 11.6882 9.22764 11.7234 9.11718C11.7586 9.00673 11.8243 8.90846 11.9129 8.83368L15.0933 6.18711C15.5106 5.83949 15.2958 5.14593 14.7586 5.11105L10.6056 4.84105C10.4938 4.83312 10.3866 4.79359 10.2964 4.72707C10.2061 4.66055 10.1367 4.56977 10.096 4.4653L8.5469 0.564927C8.50471 0.454081 8.42984 0.358673 8.33219 0.291355C8.23455 0.224037 8.11875 0.187988 8.00015 0.187988C7.88155 0.187988 7.76574 0.224037 7.6681 0.291355C7.57046 0.358673 7.49558 0.454081 7.4534 0.564927L5.90427 4.4653C5.86372 4.56988 5.79429 4.66077 5.70406 4.7274C5.61383 4.79402 5.50652 4.83364 5.39465 4.84161L1.24171 5.11161C0.705084 5.14593 0.489084 5.83949 0.907022 6.18711L4.0874 8.83424C4.17588 8.90898 4.2415 9.00715 4.27673 9.11749C4.31195 9.22783 4.31534 9.34587 4.28652 9.45805L3.33702 13.1959C3.17558 13.8309 3.85115 14.3434 4.39452 13.9986L7.69077 11.9111C7.78342 11.8522 7.89093 11.8209 8.00071 11.8209C8.11049 11.8209 8.218 11.8522 8.31065 11.9111H8.31008Z"
                                                                        fill="#CCCCCC"
                                                                    />
                                                                </svg>
                                                            </span>
                                                            <span>
                                                                <svg width="16" height="15" viewBox="0 0 16 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M8.31008 11.9111L11.8566 14.1577C12.31 14.4446 12.8725 14.0177 12.7381 13.4884L11.7138 9.45805C11.6848 9.34579 11.6882 9.22764 11.7234 9.11718C11.7586 9.00673 11.8243 8.90846 11.9129 8.83368L15.0933 6.18711C15.5106 5.83949 15.2958 5.14593 14.7586 5.11105L10.6056 4.84105C10.4938 4.83312 10.3866 4.79359 10.2964 4.72707C10.2061 4.66055 10.1367 4.56977 10.096 4.4653L8.5469 0.564927C8.50471 0.454081 8.42984 0.358673 8.33219 0.291355C8.23455 0.224037 8.11875 0.187988 8.00015 0.187988C7.88155 0.187988 7.76574 0.224037 7.6681 0.291355C7.57046 0.358673 7.49558 0.454081 7.4534 0.564927L5.90427 4.4653C5.86372 4.56988 5.79429 4.66077 5.70406 4.7274C5.61383 4.79402 5.50652 4.83364 5.39465 4.84161L1.24171 5.11161C0.705084 5.14593 0.489084 5.83949 0.907022 6.18711L4.0874 8.83424C4.17588 8.90898 4.2415 9.00715 4.27673 9.11749C4.31195 9.22783 4.31534 9.34587 4.28652 9.45805L3.33702 13.1959C3.17558 13.8309 3.85115 14.3434 4.39452 13.9986L7.69077 11.9111C7.78342 11.8522 7.89093 11.8209 8.00071 11.8209C8.11049 11.8209 8.218 11.8522 8.31065 11.9111H8.31008Z"
                                                                        fill="#CCCCCC"
                                                                    />
                                                                </svg>
                                                            </span>
                                                            <span>
                                                                <svg width="16" height="15" viewBox="0 0 16 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M8.31008 11.9111L11.8566 14.1577C12.31 14.4446 12.8725 14.0177 12.7381 13.4884L11.7138 9.45805C11.6848 9.34579 11.6882 9.22764 11.7234 9.11718C11.7586 9.00673 11.8243 8.90846 11.9129 8.83368L15.0933 6.18711C15.5106 5.83949 15.2958 5.14593 14.7586 5.11105L10.6056 4.84105C10.4938 4.83312 10.3866 4.79359 10.2964 4.72707C10.2061 4.66055 10.1367 4.56977 10.096 4.4653L8.5469 0.564927C8.50471 0.454081 8.42984 0.358673 8.33219 0.291355C8.23455 0.224037 8.11875 0.187988 8.00015 0.187988C7.88155 0.187988 7.76574 0.224037 7.6681 0.291355C7.57046 0.358673 7.49558 0.454081 7.4534 0.564927L5.90427 4.4653C5.86372 4.56988 5.79429 4.66077 5.70406 4.7274C5.61383 4.79402 5.50652 4.83364 5.39465 4.84161L1.24171 5.11161C0.705084 5.14593 0.489084 5.83949 0.907022 6.18711L4.0874 8.83424C4.17588 8.90898 4.2415 9.00715 4.27673 9.11749C4.31195 9.22783 4.31534 9.34587 4.28652 9.45805L3.33702 13.1959C3.17558 13.8309 3.85115 14.3434 4.39452 13.9986L7.69077 11.9111C7.78342 11.8522 7.89093 11.8209 8.00071 11.8209C8.11049 11.8209 8.218 11.8522 8.31065 11.9111H8.31008Z"
                                                                        fill="#CCCCCC"
                                                                    />
                                                                </svg>
                                                            </span>
                                                        </label>
                                                        <span class="star-num">2.0 & up</span>
                                                    </div>
                                                </div>
                                                <div class="ratings--list-item">
                                                    <div class="form-check d-flex align-items-center">
                                                        <input class="form-check-input" type="radio" name="rating" id="one" value="1" @checked(request('rating') == 1) onchange="this.form.submit()" />
                                                        <label class="form-check-label ratings-star d-flex align-items-center" for="one">
                                                            <span>
                                                                <svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M9.31008 13.9111L12.8566 16.1577C13.31 16.4446 13.8725 16.0177 13.7381 15.4884L12.7138 11.4581C12.6848 11.3458 12.6882 11.2276 12.7234 11.1172C12.7586 11.0067 12.8243 10.9085 12.9129 10.8337L16.0933 8.18711C16.5106 7.83949 16.2958 7.14593 15.7586 7.11105L11.6056 6.84105C11.4938 6.83312 11.3866 6.79359 11.2964 6.72707C11.2061 6.66055 11.1367 6.56977 11.096 6.4653L9.5469 2.56493C9.50471 2.45408 9.42984 2.35867 9.33219 2.29136C9.23455 2.22404 9.11875 2.18799 9.00015 2.18799C8.88155 2.18799 8.76574 2.22404 8.6681 2.29136C8.57046 2.35867 8.49558 2.45408 8.4534 2.56493L6.90427 6.4653C6.86372 6.56988 6.79429 6.66077 6.70406 6.7274C6.61383 6.79402 6.50652 6.83364 6.39465 6.84161L2.24171 7.11161C1.70508 7.14593 1.48908 7.83949 1.90702 8.18711L5.0874 10.8342C5.17588 10.909 5.2415 11.0072 5.27673 11.1175C5.31195 11.2278 5.31534 11.3459 5.28652 11.4581L4.33702 15.1959C4.17558 15.8309 4.85115 16.3434 5.39452 15.9986L8.69077 13.9111C8.78342 13.8522 8.89093 13.8209 9.00071 13.8209C9.11049 13.8209 9.218 13.8522 9.31065 13.9111H9.31008Z"
                                                                        fill="#FF8A00"
                                                                    />
                                                                </svg>
                                                            </span>
                                                            <span>
                                                                <svg width="16" height="15" viewBox="0 0 16 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M8.31008 11.9111L11.8566 14.1577C12.31 14.4446 12.8725 14.0177 12.7381 13.4884L11.7138 9.45805C11.6848 9.34579 11.6882 9.22764 11.7234 9.11718C11.7586 9.00673 11.8243 8.90846 11.9129 8.83368L15.0933 6.18711C15.5106 5.83949 15.2958 5.14593 14.7586 5.11105L10.6056 4.84105C10.4938 4.83312 10.3866 4.79359 10.2964 4.72707C10.2061 4.66055 10.1367 4.56977 10.096 4.4653L8.5469 0.564927C8.50471 0.454081 8.42984 0.358673 8.33219 0.291355C8.23455 0.224037 8.11875 0.187988 8.00015 0.187988C7.88155 0.187988 7.76574 0.224037 7.6681 0.291355C7.57046 0.358673 7.49558 0.454081 7.4534 0.564927L5.90427 4.4653C5.86372 4.56988 5.79429 4.66077 5.70406 4.7274C5.61383 4.79402 5.50652 4.83364 5.39465 4.84161L1.24171 5.11161C0.705084 5.14593 0.489084 5.83949 0.907022 6.18711L4.0874 8.83424C4.17588 8.90898 4.2415 9.00715 4.27673 9.11749C4.31195 9.22783 4.31534 9.34587 4.28652 9.45805L3.33702 13.1959C3.17558 13.8309 3.85115 14.3434 4.39452 13.9986L7.69077 11.9111C7.78342 11.8522 7.89093 11.8209 8.00071 11.8209C8.11049 11.8209 8.218 11.8522 8.31065 11.9111H8.31008Z"
                                                                        fill="#CCCCCC"
                                                                    />
                                                                </svg>
                                                            </span>
                                                            <span>
                                                                <svg width="16" height="15" viewBox="0 0 16 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M8.31008 11.9111L11.8566 14.1577C12.31 14.4446 12.8725 14.0177 12.7381 13.4884L11.7138 9.45805C11.6848 9.34579 11.6882 9.22764 11.7234 9.11718C11.7586 9.00673 11.8243 8.90846 11.9129 8.83368L15.0933 6.18711C15.5106 5.83949 15.2958 5.14593 14.7586 5.11105L10.6056 4.84105C10.4938 4.83312 10.3866 4.79359 10.2964 4.72707C10.2061 4.66055 10.1367 4.56977 10.096 4.4653L8.5469 0.564927C8.50471 0.454081 8.42984 0.358673 8.33219 0.291355C8.23455 0.224037 8.11875 0.187988 8.00015 0.187988C7.88155 0.187988 7.76574 0.224037 7.6681 0.291355C7.57046 0.358673 7.49558 0.454081 7.4534 0.564927L5.90427 4.4653C5.86372 4.56988 5.79429 4.66077 5.70406 4.7274C5.61383 4.79402 5.50652 4.83364 5.39465 4.84161L1.24171 5.11161C0.705084 5.14593 0.489084 5.83949 0.907022 6.18711L4.0874 8.83424C4.17588 8.90898 4.2415 9.00715 4.27673 9.11749C4.31195 9.22783 4.31534 9.34587 4.28652 9.45805L3.33702 13.1959C3.17558 13.8309 3.85115 14.3434 4.39452 13.9986L7.69077 11.9111C7.78342 11.8522 7.89093 11.8209 8.00071 11.8209C8.11049 11.8209 8.218 11.8522 8.31065 11.9111H8.31008Z"
                                                                        fill="#CCCCCC"
                                                                    />
                                                                </svg>
                                                            </span>
                                                            <span>
                                                                <svg width="16" height="15" viewBox="0 0 16 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M8.31008 11.9111L11.8566 14.1577C12.31 14.4446 12.8725 14.0177 12.7381 13.4884L11.7138 9.45805C11.6848 9.34579 11.6882 9.22764 11.7234 9.11718C11.7586 9.00673 11.8243 8.90846 11.9129 8.83368L15.0933 6.18711C15.5106 5.83949 15.2958 5.14593 14.7586 5.11105L10.6056 4.84105C10.4938 4.83312 10.3866 4.79359 10.2964 4.72707C10.2061 4.66055 10.1367 4.56977 10.096 4.4653L8.5469 0.564927C8.50471 0.454081 8.42984 0.358673 8.33219 0.291355C8.23455 0.224037 8.11875 0.187988 8.00015 0.187988C7.88155 0.187988 7.76574 0.224037 7.6681 0.291355C7.57046 0.358673 7.49558 0.454081 7.4534 0.564927L5.90427 4.4653C5.86372 4.56988 5.79429 4.66077 5.70406 4.7274C5.61383 4.79402 5.50652 4.83364 5.39465 4.84161L1.24171 5.11161C0.705084 5.14593 0.489084 5.83949 0.907022 6.18711L4.0874 8.83424C4.17588 8.90898 4.2415 9.00715 4.27673 9.11749C4.31195 9.22783 4.31534 9.34587 4.28652 9.45805L3.33702 13.1959C3.17558 13.8309 3.85115 14.3434 4.39452 13.9986L7.69077 11.9111C7.78342 11.8522 7.89093 11.8209 8.00071 11.8209C8.11049 11.8209 8.218 11.8522 8.31065 11.9111H8.31008Z"
                                                                        fill="#CCCCCC"
                                                                    />
                                                                </svg>
                                                            </span>
                                                            <span>
                                                                <svg width="16" height="15" viewBox="0 0 16 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M8.31008 11.9111L11.8566 14.1577C12.31 14.4446 12.8725 14.0177 12.7381 13.4884L11.7138 9.45805C11.6848 9.34579 11.6882 9.22764 11.7234 9.11718C11.7586 9.00673 11.8243 8.90846 11.9129 8.83368L15.0933 6.18711C15.5106 5.83949 15.2958 5.14593 14.7586 5.11105L10.6056 4.84105C10.4938 4.83312 10.3866 4.79359 10.2964 4.72707C10.2061 4.66055 10.1367 4.56977 10.096 4.4653L8.5469 0.564927C8.50471 0.454081 8.42984 0.358673 8.33219 0.291355C8.23455 0.224037 8.11875 0.187988 8.00015 0.187988C7.88155 0.187988 7.76574 0.224037 7.6681 0.291355C7.57046 0.358673 7.49558 0.454081 7.4534 0.564927L5.90427 4.4653C5.86372 4.56988 5.79429 4.66077 5.70406 4.7274C5.61383 4.79402 5.50652 4.83364 5.39465 4.84161L1.24171 5.11161C0.705084 5.14593 0.489084 5.83949 0.907022 6.18711L4.0874 8.83424C4.17588 8.90898 4.2415 9.00715 4.27673 9.11749C4.31195 9.22783 4.31534 9.34587 4.28652 9.45805L3.33702 13.1959C3.17558 13.8309 3.85115 14.3434 4.39452 13.9986L7.69077 11.9111C7.78342 11.8522 7.89093 11.8209 8.00071 11.8209C8.11049 11.8209 8.218 11.8522 8.31065 11.9111H8.31008Z"
                                                                        fill="#CCCCCC"
                                                                    />
                                                                </svg>
                                                            </span>
                                                        </label>
                                                        <span class="star-num">1.0 & up</span>
                                                    </div>
                                                </div>
                                            </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- Popular Tag -->
                                <div class="accordion-item shop-item">
                                    <h2 class="accordion-header" id="shop-item-accordion--four">
                                        <button class="accordion-button shop-button font-body--xxl-500 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                            Popular Tag
                                            <span class="icon">
                                                <svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M13 7L7 1L1 7" stroke="#1A1A1A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </span>
                                        </button>
                                    </h2>
                                    <div id="collapseFour" class="accordion-collapse shop-collapse collapse show" aria-labelledby="shop-item-accordion--four" data-bs-parent="#shop">
                                        <div class="accordion-body shop-body">
                                            <div class="popular-tags">
                                                @forelse ($tags as $tag)
                                                    <a
                                                        href="{{ ($activeCategory ? route('category.show', $activeCategory->slug) : route('shop')).'?'.http_build_query(array_merge(request()->only(['search', 'sort', 'rating', 'min_price', 'max_price']), ['tag' => $tag->slug])) }}"
                                                        class="tag-btn {{ request('tag') === $tag->slug ? 'active' : '' }}"
                                                    >{{ $tag->name }}</a>
                                                @empty
                                                    <p class="font-body--md-400">No tags yet.</p>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- banner  -->
                                <div class="shop-item">
                                    <div class="shop-img-banner">
                                        <img src="{{ asset('images/banner/banner-sm-19.jpg') }}" alt="banner-sm" />
                                        <div class="text-content">
                                            <h5><span>79%</span> Discount</h5>
                                            <p>on Your Fast Order</p>
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
                                <!-- Sales Products  -->
                                <div class="shop-item">
                                    <h2 class="font-body--xxl-500" style="margin-bottom: 12px;">Sale Products</h2>
                                    {{-- Real best-seller/featured/newest products, replacing the old
                                         hardcoded fake "Red Capsicum" entries and product-details.html
                                         links. --}}
                                    @forelse ($saleProducts as $saleProduct)
                                        @php
                                            $saleVariant = $saleProduct->variants->first();
                                            $saleTiers = $saleVariant?->pricingTiers() ?? [];
                                            $saleHeadline = $saleTiers[0] ?? null;
                                            $saleThumbnail = $saleProduct->thumbnailImage();
                                        @endphp
                                        <a href="{{ route('products.show', $saleProduct->slug) }}" class="shop-sale-product-item">
                                            <div class="product-img">
                                                <img
                                                    src="{{ storage_image_url($saleThumbnail?->image, asset('images/products/img-01.png')) }}"
                                                    alt="{{ $saleProduct->name }}"
                                                    loading="lazy"
                                                />
                                            </div>
                                            <div class="product-info">
                                                <h5 class="font-body--md-400">{{ $saleProduct->name }}</h5>
                                                <div class="price">
                                                    @if ($saleHeadline)
                                                        <span class="current">₹{{ number_format($saleHeadline['price'], 2) }}</span>
                                                        @if ($saleHeadline['compare_price'])
                                                            <del class="prev">₹{{ number_format($saleHeadline['compare_price'], 2) }}</del>
                                                        @endif
                                                    @else
                                                        <span class="current">Price unavailable</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </a>
                                    @empty
                                        <p class="font-body--md-400">No products yet.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-9">
                        <!-- Desktop Version  -->
                        <div class="row shop__product-items">
                            @forelse ($products as $product)
                                <x-frontend.product-card :product="$product" source="shop" />
                            @empty
                                <div class="col-12">
                                    <p class="font-body--md-400" style="padding: 40px 0; text-align: center;">
                                        No products found. Try adjusting your filters.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                        
                        {{ $products->onEachSide(2)->links('vendor.pagination.shop') }}
                    </div>
                </div>
            </div>
        </section>
        <!-- Shop list Section End   -->


        <!-- Dead leftover template markup removed for Phase F: a duplicate "Shopping Cart" sidebar (unreachable — document.querySelector('.shopping-cart') in main.js always matches the real one in components/layouts/header.blade.php first), a stray Envato marketplace "Purchase Now" button, and premature closing </body></html> tags that predated the layout being extracted into components/layouts/{header,footer}.blade.php. The scripts below are unaffected by that removal — kept exactly as before. -->
        <script src="{{ asset('lib/js/nouislider.min.js') }}"></script>
        <script src="{{ asset('lib/js/jquery.min.js') }}"></script>
        <script src="{{ asset('lib/js/swiper-bundle.min.js') }}"></script>
        <script src="{{ asset('lib/js/bvselect.js') }}"></script>
        <script src="{{ asset('lib/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('js/main.js') }}"></script>
        <script src="{{ admin_asset('js/cart.js') }}"></script>
        <script src="{{ asset('js/wishlist.js') }}"></script>
        <script>
            // Wires the noUiSlider price-range control (initialized in
            // main.js from this element's data-min/data-max/data-start-*
            // attributes) to the hidden min_price/max_price inputs and
            // submits the filter form on release, rather than on every
            // drag tick.
            document.addEventListener('DOMContentLoaded', function () {
                var slider = document.getElementById('priceRangeSlider');
                if (! slider || ! slider.noUiSlider) {
                    return;
                }

                var form = document.getElementById(slider.dataset.form);
                var minInput = document.getElementById('min_price');
                var maxInput = document.getElementById('max_price');

                slider.noUiSlider.on('change', function (values) {
                    minInput.value = Math.round(parseFloat(values[0]));
                    maxInput.value = Math.round(parseFloat(values[1]));
                    form.submit();
                });
            });
        </script>
    </body>
</html>
</x-layouts.app>
