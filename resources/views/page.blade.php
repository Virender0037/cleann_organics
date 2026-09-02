<x-layouts.app
    :meta-title="$page->meta_title ?: $page->title.' | Cleann Organics'"
    :meta-description="$page->meta_description"
    :canonical-url="$page->canonical_url">

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
            {{-- Pages that have graduated to a dedicated canonical route (name
                 matching the slug, e.g. privacy-policy, our-story) link to
                 themselves via that route rather than the generic page.show
                 URL, which now just 301-redirects here — otherwise this
                 breadcrumb would be an internal link that depends on a
                 redirect to reach its own page. Any page without one yet
                 falls back to page.show unchanged. --}}
            <li class="active"><a href="{{ Route::has($page->slug) ? route($page->slug) : route('page.show', $page->slug) }}">{{ $page->title }}</a></li>
          </ul>
        </div>
      </div>
    </div>
    <!-- breedcrumb section end   -->

    <!-- Page Section Start  -->
    <section class="section section--xl">
      <div class="container">
        <div class="row">
          <div class="col-lg-10 offset-lg-1">
            <div class="page-content">

              <h2 class="font-title--sm page-content__title">{{ $page->title }}</h2>

              @if ($page->featured_image)
                <div class="page-content__image">
                  <img
                    src="{{ Storage::disk('public')->url($page->featured_image) }}"
                    alt="{{ $page->title }}"
                  />
                </div>
              @endif

              <div class="font-body--md-400 page-content__body">
                {{-- Stored content is plain text with line breaks, so it is fully
                     escaped and then line breaks are restored. No raw HTML is
                     rendered, which keeps this safe without a sanitiser. --}}
                {!! nl2br(e($page->content)) !!}
              </div>

            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- Page Section end  -->

    <script src="{{ asset('lib/js/jquery.min.js') }}"></script>
    <script src="{{ asset('lib/js/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('lib/js/bvselect.js') }}"></script>
    <script src="{{ asset('lib/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
</x-layouts.app>
