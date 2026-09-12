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
            <li class="active"><a href="#">Verify Email</a></li>
          </ul>
        </div>
      </div>
    </div>
    <!-- breedcrumb section end   -->

    <!-- Verify Email Section Start  -->
    <section class="sign-in section section--xl">
      <div class="container">
        <div class="form-wrapper">
          <h6 class="font-title--sm">Verify Your Email</h6>

          <p class="font-body--md-400" style="color:#666666;text-align:center;margin-bottom:24px;">
            {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
          </p>

          @if (session('status') == 'verification-link-sent')
            <p class="font-body--md-400" style="color:#00B307;text-align:center;margin-bottom:24px;">
              {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </p>
          @endif

          <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;">
            <form method="POST" action="{{ route('verification.send') }}">
              @csrf
              <button class="button button--md" type="submit">{{ __('Resend Verification Email') }}</button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="button button--md button--disable">{{ __('Log Out') }}</button>
            </form>
          </div>
        </div>
      </div>
    </section>
    <!-- Verify Email Section end  -->

    <script src="{{ asset('lib/js/jquery.min.js') }}"></script>
    <script src="{{ asset('lib/js/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('lib/js/bvselect.js') }}"></script>
    <script src="{{ asset('lib/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
</x-layouts.app>
