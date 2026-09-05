<x-layouts.app :noindex="true">
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
                    stroke="#808080" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span> > </span>
              </a>
            </li>
            <li><a href="{{ route('user-dashboard') }}">Account<span> > </span></a></li>
            <li class="active"><a href="{{ route('account-setting') }}">Account Settings</a></li>
          </ul>
        </div>
      </div>
    </div>
    <!-- breedcrumb section end   -->

    <!-- dashboard Secton Start  -->
    <div class="dashboard section">
      <div class="container">
        <div class="row dashboard__content">
          <div class="col-lg-3">
            <x-account-nav active="settings" />
          </div>
          <div class="col-lg-9 section--xl pt-0">
            <div class="container">
              @include('profile.partials.update-profile-information-form')

              @include('profile.partials.update-password-form')

              <!-- My Addresses (Phase I — CRUD unchanged, surfaced here for Phase J) -->
              <div class="dashboard__content-card" id="addresses">
                <div class="dashboard__content-card-header">
                  <h5 class="font-body--xxl-500">My Addresses</h5>
                </div>
                <div class="dashboard__content-card-body">
                  @php
                      // Address forms and the profile form share the default
                      // error bag on this page; these keys only ever come
                      // from an address form, so a failed profile/password
                      // save doesn't light this section up.
                      $addressFailed = collect(['type', 'address_line_1', 'address_line_2', 'pincode'])
                          ->contains(fn ($k) => $errors->has($k));
                  @endphp
                  @if ($addressFailed)
                    <div style="margin-bottom:16px;">
                      @foreach ($errors->all() as $error)
                        <p class="font-body--md-400" style="color:#EA4B48;">{{ $error }}</p>
                      @endforeach
                    </div>
                  @endif
                  @forelse ($addresses as $savedAddress)
                    <div style="border:1px solid #e5e5e5;border-radius:8px;padding:16px;margin-bottom:16px;">
                      <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px;">
                        <div>
                          <p class="font-body--md-500">
                            {{ $savedAddress->name }}
                            <span class="font-body--md-400" style="text-transform:capitalize;color:#666666;">({{ $savedAddress->type }})</span>
                            @if ($savedAddress->is_default)
                              <span class="font-body--md-400" style="color:#00B307;">&middot; Default</span>
                            @endif
                          </p>
                          <p class="font-body--md-400">
                            {{ $savedAddress->address_line_1 }}@if ($savedAddress->address_line_2), {{ $savedAddress->address_line_2 }}@endif,
                            {{ $savedAddress->city }}, {{ $savedAddress->state }} {{ $savedAddress->pincode }}, {{ $savedAddress->country }}
                          </p>
                          <p class="font-body--md-400">{{ $savedAddress->phone }}</p>
                        </div>
                        <form action="{{ route('addresses.destroy', $savedAddress) }}" method="POST" onsubmit="return confirm('Delete this address?');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="button button--outline" style="padding:6px 16px;">Delete</button>
                        </form>
                      </div>
                      <details style="margin-top:12px;">
                        <summary style="cursor:pointer;color:#00B307;">Edit this address</summary>
                        <form action="{{ route('addresses.update', $savedAddress) }}" method="POST" style="margin-top:16px;">
                          @csrf
                          @method('PUT')
                          @include('partials.address-fields', ['address' => $savedAddress, 'prefix' => 'edit-'.$savedAddress->id])
                          <div class="contact-form-btn">
                            <button class="button button--md" type="submit">Save Address</button>
                          </div>
                        </form>
                      </details>
                    </div>
                  @empty
                    <p class="font-body--md-400" style="margin-bottom:16px;">You haven't saved any addresses yet.</p>
                  @endforelse

                  <details @if ($addresses->isEmpty()) open @endif>
                    <summary style="cursor:pointer;color:#00B307;font-weight:500;">+ Add a new address</summary>
                    <form action="{{ route('addresses.store') }}" method="POST" style="margin-top:16px;">
                      @csrf
                      @include('partials.address-fields', ['address' => null, 'prefix' => 'new'])
                      <div class="contact-form-btn">
                        <button class="button button--md" type="submit">Save Address</button>
                      </div>
                    </form>
                  </details>
                </div>
              </div>

              @include('profile.partials.delete-user-form')
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- dashboard Secton  End  -->

    <script src="{{ asset('lib/js/jquery.min.js') }}"></script>
    <script src="{{ asset('lib/js/bvselect.js') }}"></script>
    <script src="{{ asset('lib/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
  </body>
</html>
</x-layouts.app>
