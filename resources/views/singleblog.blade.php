<x-layouts.app>
    <div class="section breedcrumb">
      <div class="breedcrumb__img-wrapper">
        <img src="{{ asset('images/banner/breedcrumb.jpg') }}" alt="breedcrumb" />
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
            <li>
              <a href="{{ route('bloglist') }}">Blog<span> > </span></a>
            </li>
            <li class="active"><a href="{{ route('singleblog', $blog->slug) }}">{{ Str::limit($blog->title, 40) }}</a></li>
          </ul>
        </div>
      </div>
    </div>
    <!-- breedcrumb section end   -->

    <!-- Single blog section start  -->
    <section class="single-blog section">
      <div class="container">
        <div class="row single-blog__content">
          <div class="col-lg-8">
            <div class="single-blog__product-content">
              <div class="single-blog__img-wrapper one">
                <img
                    src="{{ storage_image_url($blog->featured_image, asset('images/blogs/img-01.png')) }}"
                    alt="{{ $blog->title }}"
                />
              </div>
              <div class="single-blog--tag-info">
                <div class="single-blog--tag-item">
                  <span class="icon">
                    <svg
                      width="20"
                      height="21"
                      viewBox="0 0 20 21"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        d="M17.1584 11.6753L11.1834 17.6503C11.0286 17.8053 10.8448 17.9282 10.6425 18.0121C10.4402 18.096 10.2233 18.1391 10.0042 18.1391C9.78522 18.1391 9.56834 18.096 9.36601 18.0121C9.16368 17.9282 8.97987 17.8053 8.82508 17.6503L1.66675 10.5003V2.16699H10.0001L17.1584 9.32533C17.4688 9.6376 17.6431 10.06 17.6431 10.5003C17.6431 10.9406 17.4688 11.3631 17.1584 11.6753V11.6753Z"
                        stroke="#00B307"
                        stroke-width="1.2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                      <path
                        d="M5.8335 6.33398H5.84183"
                        stroke="#00B307"
                        stroke-width="1.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                    </svg>
                  </span>
                  <p>{{ $blog->category?->name ?? 'Uncategorized' }}</p>
                </div>
                <div class="single-blog--tag-item">
                  <span class="icon">
                    <svg
                      width="20"
                      height="21"
                      viewBox="0 0 20 21"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        d="M9.99984 9.66667C11.8408 9.66667 13.3332 8.17428 13.3332 6.33333C13.3332 4.49238 11.8408 3 9.99984 3C8.15889 3 6.6665 4.49238 6.6665 6.33333C6.6665 8.17428 8.15889 9.66667 9.99984 9.66667Z"
                        stroke="#00B307"
                        stroke-width="1.2"
                      />
                      <path
                        d="M12.4999 12.167H7.49995C5.19828 12.167 3.13745 14.292 4.65161 16.0245C5.68161 17.2028 7.38495 18.0003 9.99995 18.0003C12.6149 18.0003 14.3174 17.2028 15.3474 16.0245C16.8624 14.2912 14.8008 12.167 12.4999 12.167Z"
                        stroke="#00B307"
                        stroke-width="1.2"
                      />
                    </svg>
                  </span>
                  <p>by <span>{{ $blog->author?->name ?? 'Admin' }}</span></p>
                </div>
                <div class="single-blog--tag-item">
                  <span class="icon">
                    <svg
                      width="18"
                      height="19"
                      viewBox="0 0 18 19"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        d="M10.5238 14.2728L9.48206 16.0087C9.43209 16.092 9.36139 16.1609 9.27687 16.2088C9.19234 16.2566 9.09686 16.2818 8.99972 16.2818C8.90258 16.2818 8.8071 16.2566 8.72257 16.2088C8.63804 16.1609 8.56735 16.092 8.51738 16.0087L7.47675 14.2728C7.42671 14.1895 7.35596 14.1206 7.27138 14.0728C7.1868 14.025 7.09128 13.9999 6.99413 14H2.8125C2.66332 14 2.52024 13.9407 2.41475 13.8352C2.30926 13.7298 2.25 13.5867 2.25 13.4375V4.4375C2.25 4.28832 2.30926 4.14524 2.41475 4.03975C2.52024 3.93426 2.66332 3.875 2.8125 3.875H15.1875C15.3367 3.875 15.4798 3.93426 15.5852 4.03975C15.6907 4.14524 15.75 4.28832 15.75 4.4375V13.4375C15.75 13.5867 15.6907 13.7298 15.5852 13.8352C15.4798 13.9407 15.3367 14 15.1875 14H11.0059C10.9088 14 10.8134 14.0252 10.7289 14.073C10.6445 14.1208 10.5738 14.1896 10.5238 14.2728V14.2728Z"
                        stroke="#00B307"
                        stroke-width="1.2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                    </svg>
                  </span>
                  <p>{{ number_format($blog->view_count) }} {{ Str::plural('View', $blog->view_count) }}</p>
                </div>
              </div>
              <h2 class="font-title--sm blog-head-title">
                {{ $blog->title }}
              </h2>

              <div class="single-blog__author-details">
                <div class="author">
                  <div class="author-img">
                    <img src="{{ asset('images/user/img-01.png') }}" alt="{{ $blog->author?->name ?? 'Cleann Organics Team' }}" />
                  </div>
                  <div class="author-info">
                    <h5 class="font-body--lg">{{ $blog->author?->name ?? 'Cleann Organics Team' }}</h5>
                    <div class="author-blog-details">
                      <p>{{ ($blog->published_at ?? $blog->created_at)->format('d F, Y') }}</p>
                      <span class="dot"></span>
                      <p class="duration">{{ max(1, (int) ceil(str_word_count(strip_tags($blog->content)) / 200)) }} min read</p>
                    </div>
                  </div>
                </div>
                <ul class="social-icon">
                  <li class="social-icon-link"><a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" rel="noopener noreferrer" aria-label="Share on Facebook">
                      <svg
                        width="10"
                        height="18"
                        viewBox="0 0 10 18"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          d="M7.99764 2.98875H9.64089V0.12675C9.35739 0.08775 8.38239 0 7.24689 0C4.87764 0 3.25464 1.49025 3.25464 4.22925V6.75H0.640137V9.9495H3.25464V18H6.46014V9.95025H8.96889L9.36714 6.75075H6.45939V4.5465C6.46014 3.62175 6.70914 2.98875 7.99764 2.98875Z"
                          fill="currentColor"
                        ></path>
                      </svg>
                    </a></li>
                  <li class="social-icon-link"><a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&amp;text={{ urlencode($blog->title) }}" target="_blank" rel="noopener noreferrer" aria-label="Share on X / Twitter">
                      <svg
                        width="18"
                        height="16"
                        viewBox="0 0 18 16"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          d="M18 2.41888C17.3306 2.7125 16.6174 2.90713 15.8737 3.00163C16.6388 2.54488 17.2226 1.82713 17.4971 0.962C16.7839 1.38725 15.9964 1.68763 15.1571 1.85525C14.4799 1.13413 13.5146 0.6875 12.4616 0.6875C10.4186 0.6875 8.77387 2.34575 8.77387 4.37863C8.77387 4.67113 8.79862 4.95238 8.85938 5.22013C5.7915 5.0705 3.07687 3.60013 1.25325 1.36025C0.934875 1.91263 0.748125 2.54488 0.748125 3.2255C0.748125 4.5035 1.40625 5.63638 2.38725 6.29225C1.79437 6.281 1.21275 6.10888 0.72 5.83775C0.72 5.849 0.72 5.86363 0.72 5.87825C0.72 7.6715 1.99912 9.161 3.6765 9.50413C3.37612 9.58625 3.04875 9.62563 2.709 9.62563C2.47275 9.62563 2.23425 9.61213 2.01038 9.56263C2.4885 11.024 3.84525 12.0984 5.4585 12.1333C4.203 13.1154 2.60888 13.7071 0.883125 13.7071C0.5805 13.7071 0.29025 13.6936 0 13.6565C1.63462 14.7106 3.57188 15.3125 5.661 15.3125C12.4515 15.3125 16.164 9.6875 16.164 4.81175C16.164 4.64863 16.1584 4.49113 16.1505 4.33475C16.8829 3.815 17.4982 3.16588 18 2.41888Z"
                          fill="currentColor"
                        ></path>
                      </svg>
                    </a></li>
                  <li class="social-icon-link"><a href="https://pinterest.com/pin/create/button/?url={{ urlencode(url()->current()) }}&amp;description={{ urlencode($blog->title) }}" target="_blank" rel="noopener noreferrer" aria-label="Share on Pinterest">
                      <svg
                        width="16"
                        height="18"
                        viewBox="0 0 16 18"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          d="M8.24471 0C3.31136 0 0.687744 3.16139 0.687744 6.60855C0.687744 8.20724 1.58103 10.2008 3.01097 10.8331C3.22811 10.931 3.34624 10.8894 3.39462 10.688C3.43737 10.535 3.62525 9.79807 3.71638 9.45042C3.74451 9.33904 3.72988 9.24229 3.63988 9.13766C3.16511 8.58864 2.78821 7.58847 2.78821 6.65017C2.78821 4.24594 4.69967 1.91146 7.9522 1.91146C10.7648 1.91146 12.7325 3.73854 12.7325 6.35204C12.7325 9.30529 11.1698 11.3484 9.13912 11.3484C8.0152 11.3484 7.17816 10.4663 7.44367 9.37505C7.76431 8.07561 8.39321 6.6783 8.39321 5.74113C8.39321 4.90072 7.91844 4.20544 6.94865 4.20544C5.80447 4.20544 4.87631 5.33837 4.87631 6.85943C4.87631 7.82585 5.21832 8.47838 5.21832 8.47838C5.21832 8.47838 4.08652 13.0506 3.87614 13.9045C3.52062 15.3502 3.92451 17.6914 3.95939 17.8928C3.98077 18.0042 4.10565 18.0391 4.1754 17.9479C4.28678 17.8017 5.65484 15.8497 6.03848 14.4389C6.17799 13.9248 6.75064 11.84 6.75064 11.84C7.12753 12.5207 8.21546 13.0911 9.37426 13.0911C12.8214 13.0911 15.3123 10.0613 15.3123 6.30141C15.2999 2.69675 12.215 0 8.24471 0Z"
                          fill="currentColor"
                        ></path>
                      </svg>
                    </a></li>
                  
                </ul>
              </div>
            </div>

            <!-- Text Contents of Blogs  -->
            <div class="single-blog__inner-content">
                {!! $blog->content !!}
            </div>

            @if ($blog->tags->isNotEmpty())
              <div class="single-blog--tags" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-top:24px;">
                <h5 class="font-body--lg-500" style="margin:0;">Tags:</h5>
                @foreach ($blog->tags as $tag)
                  <a href="{{ route('bloglist', ['tag' => $tag->slug]) }}" class="blog__popular-tags-item">{{ $tag->name }}</a>
                @endforeach
              </div>
            @endif

            @if ($relatedBlogs->isNotEmpty())
              <div class="single-blog--related" style="margin-top:40px;">
                <h5 class="font-body--xxxl-500" style="margin-bottom:20px;">Related Posts</h5>
                <div class="row">
                  @foreach ($relatedBlogs as $related)
                    <div class="col-md-6 col-lg-4" style="margin-bottom:24px;">
                      <x-frontend.blog-card :blog="$related" />
                    </div>
                  @endforeach
                </div>
              </div>
            @endif
          </div>
          <div class="col-lg-4">
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
                    <input type="text" id="blog-sidebar-search" name="search" placeholder="Search..." />
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
                          <span class="blog__popular-tags-item">{{ $tag->name }}</span>
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
                        <a href="{{ route('singleblog', $recent->slug) }}" class="blog__recent-product__item">
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
        </div>
      </div>
    </section>
    <!-- Single blog section  end  -->


    <script src="{{ asset('lib/js/jquery.min.js') }}"></script>
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

