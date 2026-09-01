<x-layouts.app>
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
            <li class="active"><a href="{{ route('profile.edit') }}">Profile</a></li>
          </ul>
        </div>
      </div>
    </div>
    <!-- breedcrumb section end   -->

    <!-- Profile Section Start  -->
    <div class="dashboard section">
      <div class="container">
        <div class="row dashboard__content">
          <div class="col-lg-3">
            <nav class="dashboard__nav">
              <h5 class="dashboard__nav-title font-body--xxl-500">navigation</h5>
              <ul class="dashboard__nav-item">
                <li class="dashboard__nav-item-link">
                  <a href="{{ route('user-dashboard') }}" class="font-body--lg-400">Dashboard</a>
                </li>
                <li class="dashboard__nav-item-link active">
                  <a href="{{ route('profile.edit') }}" class="font-body--lg-400">Profile</a>
                </li>
              </ul>
            </nav>
          </div>

          <div class="col-lg-9 section--xl pt-0">
            <div class="container">
              @include('profile.partials.update-profile-information-form')
              @include('profile.partials.update-password-form')
              @include('profile.partials.delete-user-form')
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Profile Section end  -->

    <script src="{{ asset('lib/js/jquery.min.js') }}"></script>
    <script src="{{ asset('lib/js/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('lib/js/bvselect.js') }}"></script>
    <script src="{{ asset('lib/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
</x-layouts.app>
