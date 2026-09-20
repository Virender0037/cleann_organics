<x-layouts.app>
    <!-- breedcrumb section start  -->
    <section class="section breedcrumb">
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
            <li class="active"><a href="{{ route('faq') }}">faq</a></li>
          </ul>
        </div>
      </div>
    </section>
    <!-- breedcrumb section end   -->

    <section class="section section--xl faq">
      <div class="container">
        <div class="row faq__content">
          <div class="col-lg-6 order-lg-0 order-1">
            <div class="faq__accordion">
              <h5 class="font-title--xl">Welcome, Let’s Talk About Our faq</h5>

              @if ($faqs->isEmpty())
                <div class="faq__empty-state">No FAQs are available at the moment.</div>
              @else
                <div class="faq__toolbar">
                  <div class="faq__search-field">
                    <span class="icon">
                      <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="8" cy="8" r="6.25" stroke="#808080" stroke-width="1.5" />
                        <path d="M16 16L12.5 12.5" stroke="#808080" stroke-width="1.5" stroke-linecap="round" />
                      </svg>
                    </span>
                    <input type="text" id="faqSearchInput" placeholder="Search FAQs..." autocomplete="off">
                  </div>

                  <div class="faq__filters" id="faqFilters">
                    <button type="button" class="faq__filter-btn button active" data-topic="all">All</button>
                    @foreach ($topics as $topic)
                      <button type="button" class="faq__filter-btn button button--outline" data-topic="{{ $topic }}">{{ $topic }}</button>
                    @endforeach
                  </div>

                  <p class="faq__count" id="faqCount"></p>
                </div>

                <div class="accordion" id="faq-accordion">
                  @foreach ($faqs as $index => $faq)
                    <div class="accordion-item faq__item" data-topic="{{ $faq->topic ?? '' }}" data-search="{{ Str::lower($faq->question.' '.$faq->answer) }}">
                      <h2 class="accordion-header" id="faq-heading-{{ $faq->id }}">
                        <button
                          class="accordion-button @if ($index !== 0) collapsed @endif"
                          type="button"
                          data-bs-toggle="collapse"
                          data-bs-target="#faq-collapse-{{ $faq->id }}"
                          aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                          aria-controls="faq-collapse-{{ $faq->id }}"
                        >
                          {{ $faq->question }}
                          <span class="icon">
                            <svg
                              width="32"
                              height="32"
                              viewBox="0 0 32 32"
                              fill="none"
                              xmlns="http://www.w3.org/2000/svg"
                            >
                              <rect
                                width="32"
                                height="32"
                                rx="16"
                                fill="currentColor"
                              />
                              <path
                                d="M12.0001 16H20.0001M16.0001 12V20V12Z"
                                stroke="#1A1A1A"
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                              />
                            </svg>
                          </span>
                        </button>
                      </h2>
                      <div
                        id="faq-collapse-{{ $faq->id }}"
                        class="accordion-collapse collapse @if ($index === 0) show @endif"
                        aria-labelledby="faq-heading-{{ $faq->id }}"
                        data-bs-parent="#faq-accordion"
                      >
                        <div class="accordion-body">{{ $faq->answer }}</div>
                      </div>
                    </div>
                  @endforeach
                </div>

                <div class="faq__empty-state" id="faqNoTopicResults" hidden>No FAQs found for this topic.</div>
                <div class="faq__empty-state" id="faqNoSearchResults" hidden>No FAQs matched your search.</div>
              @endif
            </div>
          </div>
          <div class="col-lg-6 order-lg-0 order-2">
            <div class="faq__img-wrapper">
              <img
                src="{{ asset('images/banner/banner-lg-09.png') }}"
                alt="banner"
                class="img-fluid"
              />
            </div>
          </div>
        </div>
      </div>
    </section>

    <script src="{{ asset('lib/js/jquery.min.js') }}"></script>
    <script src="{{ asset('lib/js/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('lib/js/bvselect.js') }}"></script>
    <script src="{{ asset('lib/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('js/faq-filter.js') }}"></script>
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
