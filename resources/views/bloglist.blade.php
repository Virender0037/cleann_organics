<x-layouts.app>
    <!-- breedcrumb section start  -->
    <div class="section breedcrumb">
      <div class="breedcrumb__img-wrapper">
        <img src="{{ asset('images/banner/breedcrumb.jpg') }}" alt="breedcrumb">
        <div class="container">
          <ul class="breedcrumb__content">
            <li>
              <a href="{{ route('home') }}">
                <svg
                  width="18"
                  height="19"
                  viewBox="0 0 18 19"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
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
            <li class="active"><a href="{{ route('bloglist') }}">Blog</a></li>
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
                <svg
                  width="22"
                  height="19"
                  viewBox="0 0 22 19"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    d="M18 5.75C18.4142 5.75 18.75 5.41421 18.75 5C18.75 4.58579 18.4142 4.25 18 4.25V5.75ZM9 4.25C8.58579 4.25 8.25 4.58579 8.25 5C8.25 5.41421 8.58579 5.75 9 5.75V4.25ZM18 4.25H9V5.75H18V4.25Z"
                    fill="white"
                  />
                  <path
                    d="M13 14.75C13.4142 14.75 13.75 14.4142 13.75 14C13.75 13.5858 13.4142 13.25 13 13.25V14.75ZM4 13.25C3.58579 13.25 3.25 13.5858 3.25 14C3.25 14.4142 3.58579 14.75 4 14.75V13.25ZM13 13.25H4V14.75H13V13.25Z"
                    fill="white"
                  />
                  <circle
                    cx="5"
                    cy="5"
                    r="4"
                    stroke="white"
                    stroke-width="1.5"
                  />
                  <circle
                    cx="17"
                    cy="14"
                    r="4"
                    stroke="white"
                    stroke-width="1.5"
                  />
                </svg>
              </span>
            </button>
          </div>
          <div class="col-lg-9">
            <div class="filter--search-result justify-content-end">
              <div class="sort-list">
                <label for="sort">Sort by:</label>
                <select id="sort" class="sort-list__dropmenu">
                  <option value="01">Latest</option>
                  <option value="02">Newest</option>
                  <option value="03">Oldest</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Blog-list section start  -->
    <section class="blog-list section">
      <div class="container">
        <div class="row blog-list__wrapper shop-content">
          <div class="col-lg-3">
            <div class="sidebar">
              <!-- filter btn  -->
              <button class="filter">
                <svg
                  width="22"
                  height="19"
                  viewBox="0 0 22 19"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    d="M18 5.75C18.4142 5.75 18.75 5.41421 18.75 5C18.75 4.58579 18.4142 4.25 18 4.25V5.75ZM9 4.25C8.58579 4.25 8.25 4.58579 8.25 5C8.25 5.41421 8.58579 5.75 9 5.75V4.25ZM18 4.25H9V5.75H18V4.25Z"
                    fill="white"
                  />
                  <path
                    d="M13 14.75C13.4142 14.75 13.75 14.4142 13.75 14C13.75 13.5858 13.4142 13.25 13 13.25V14.75ZM4 13.25C3.58579 13.25 3.25 13.5858 3.25 14C3.25 14.4142 3.58579 14.75 4 14.75V13.25ZM13 13.25H4V14.75H13V13.25Z"
                    fill="white"
                  />
                  <circle
                    cx="5"
                    cy="5"
                    r="4"
                    stroke="white"
                    stroke-width="1.5"
                  />
                  <circle
                    cx="17"
                    cy="14"
                    r="4"
                    stroke="white"
                    stroke-width="1.5"
                  />
                </svg>
              </button>
              <div class="blog__sidebar">
                <!-- Search Field  -->
                <div class="blog__sidebar--item">
                  <form action="{{ route('bloglist') }}" method="GET" class="blog__search-field">
                    <input type="text" id="blog-sidebar-search" name="search" value="{{ $search }}" placeholder="Search..." />
                    <button type="submit" class="icon" aria-label="Search">
                      <svg
                        width="20"
                        height="20"
                        viewBox="0 0 20 20"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          d="M9.80577 17.2971C13.9428 17.2971 17.2966 13.9433 17.2966 9.80626C17.2966 5.66919 13.9428 2.31543 9.80577 2.31543C5.6687 2.31543 2.31494 5.66919 2.31494 9.80626C2.31494 13.9433 5.6687 17.2971 9.80577 17.2971Z"
                          stroke="#1A1A1A"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M15.0149 15.4043L17.9516 18.3335"
                          stroke="#1A1A1A"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                      </svg>
                    </button>
                  </form>
                </div>
                <!-- Top Categories  -->
                @if ($categories->isNotEmpty())
                  <div class="blog__sidebar--item">
                    <h5 class="font-body--xxl-500">Top Categories</h5>
                    <div class="blog__top-categories">
                      @foreach ($categories as $category)
                        <a href="{{ route('bloglist', ['category' => $category->slug]) }}" class="blog__top-categories-item">
                          <p class="font-body--md-400">{{ $category->name }}</p>
                          <p class="font-body--md-400 number">({{ $category->blogs_count }})</p>
                        </a>
                      @endforeach
                    </div>
                  </div>
                @endif
                <!-- Popular Tags  -->
                @if ($tags->isNotEmpty())
                  <div class="blog__sidebar--item">
                    <h5 class="font-body--xxl-500">Popular Tag</h5>
                    <div class="blog__popular-tags">
                      @foreach ($tags as $tag)
                        <a href="{{ route('bloglist', ['tag' => $tag->slug]) }}">
                          <span class="blog__popular-tags-item {{ request('tag') === $tag->slug ? 'active' : '' }}">{{ $tag->name }}</span>
                        </a>
                      @endforeach
                    </div>
                  </div>
                @endif

                <!-- Recent Added Products  -->
                @if ($recentBlogs->isNotEmpty())
                  <div class="blog__sidebar--item">
                    <h5 class="font-body--xxl-500">Recently Added</h5>
                    <div class="blog__recent-product">
                      @foreach ($recentBlogs as $recent)
                        <a
                          href="{{ route('singleblog', $recent->slug) }}"
                          class="blog__recent-product__item"
                        >
                          <div class="blog__recent-product__img-wrapper">
                            <img
                              src="{{ storage_image_url($recent->featured_image, asset('images/blogs/blog-recent-1.png')) }}"
                              alt="{{ $recent->title }}"
                            />
                          </div>
                          <div class="blog__recent-product__item-info">
                            <h5 class="font-body--lg-500">
                              {{ Str::limit($recent->title, 55) }}
                            </h5>
                            <div class="date">
                              <span class="icon">
                                <svg
                                  width="18"
                                  height="19"
                                  viewBox="0 0 18 19"
                                  fill="none"
                                  xmlns="http://www.w3.org/2000/svg"
                                >
                                  <path
                                    d="M14.25 3.5H3.75C2.92157 3.5 2.25 4.17157 2.25 5V15.5C2.25 16.3284 2.92157 17 3.75 17H14.25C15.0784 17 15.75 16.3284 15.75 15.5V5C15.75 4.17157 15.0784 3.5 14.25 3.5Z"
                                    stroke="#00B307"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                  />
                                  <path
                                    d="M12 2V5"
                                    stroke="#00B307"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                  />
                                  <path
                                    d="M6 2V5"
                                    stroke="#00B307"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                  />
                                  <path
                                    d="M2.25 8H15.75"
                                    stroke="#00B307"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                  />
                                </svg>
                              </span>
                              <p>{{ $recent->published_at?->format('M d, Y') ?? $recent->created_at->format('M d, Y') }}</p>
                            </div>
                          </div>
                        </a>
                      @endforeach
                    </div>
                  </div>
                @endif
              </div>
            </div>
          </div>
          <div class="col-lg-9">
            <!-- Desktop Version  -->
            <div class="row blog-list__content--desktop">
              @forelse ($blogs as $blog)
                <div class="col-xl-6 custom-col">
                  <x-frontend.blog-card :blog="$blog" />
                </div>
              @empty
                <div class="col-12">
                  <div class="cart-table" style="padding:60px 24px;text-align:center;">
                    <p class="font-body--lg-400" style="margin-bottom:16px;">
                      @if ($search)
                        No blog posts match "{{ $search }}".
                      @else
                        No blog posts have been published yet. Check back soon!
                      @endif
                    </p>
                  </div>
                </div>
              @endforelse
            </div>

            <!-- Mobile Version  -->
            <div class="blog-list--slider swiper-container">
              <div class="swiper-wrapper">
                @foreach ($blogs as $blog)
                  <div class="swiper-slide">
                    <x-frontend.blog-card :blog="$blog" />
                  </div>
                @endforeach
              </div>
              <div class="swiper-pagination"></div>
            </div>

            <!-- Pagination   -->
            {{ $blogs->onEachSide(2)->links('vendor.pagination.shop') }}
          </div>
        </div>
      </div>
    </section>
    <!-- Blog-list section  end  -->



    <!-- Shopping Cart sidebar  start  -->

    <!-- Shopping Cart sidebar  end -->

    <!-- Scripts Content Goes Here -->
    <script src="{{ asset('lib/js/jquery.min.js') }}"></script>
    <script src="{{ asset('lib/js/venobox.min.js') }}"></script>
    <script src="{{ asset('lib/js/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('lib/js/bvselect.js') }}"></script>
    <script src="{{ asset('lib/js/bootstrap.bundle.min.js') }}"></script>
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
