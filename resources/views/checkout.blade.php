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
                    stroke="#808080" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span> > </span>
              </a>
            </li>
            <li class="active"><a href="{{ route('checkout') }}">Checkout</a></li>
          </ul>
        </div>
      </div>
    </div>
    <!-- breedcrumb section end   -->

    <section class="shoping-cart section section--xl">
      <div class="container">
        <div class="section__head justify-content-center">
          <h2 class="section--title-four font-title--sm">Checkout</h2>
        </div>

        @if ($lines->isEmpty())
          <div class="cart-table" style="padding:60px 24px;text-align:center;">
            <p class="font-body--lg-400" style="margin-bottom:16px;">Your cart is empty.</p>
            <a href="{{ route('shop') }}" class="button button--md">Continue Shopping</a>
          </div>
        @elseif ($hasUnavailableLines)
          <div class="cart-table" style="padding:60px 24px;text-align:center;">
            <p class="font-body--lg-400" style="margin-bottom:16px;">
              Some items in your cart are no longer available. Please review your cart before checking out.
            </p>
            <a href="{{ route('shopping-cart') }}" class="button button--md">Review Cart</a>
          </div>
        @else
          <div class="row shoping-cart__content">
            <div class="col-lg-8">
              <!-- Delivery Address -->
              <div class="cart-table" style="padding:24px;margin-bottom:24px;">
                <h5 class="font-body--xxl-500" style="margin-bottom:16px;">Delivery Address</h5>

                @if ($errors->any())
                  <div style="margin-bottom:16px;">
                    @foreach ($errors->all() as $error)
                      <p class="font-body--md-400" style="color:#EA4B48;">{{ $error }}</p>
                    @endforeach
                  </div>
                @endif

                @if ($addresses->isNotEmpty())
                  <form method="GET" action="{{ route('checkout') }}">
                    @foreach ($addresses as $address)
                      <label style="display:block;border:1px solid {{ $selectedAddress?->id === $address->id ? '#00B307' : '#e5e5e5' }};border-radius:8px;padding:16px;margin-bottom:12px;cursor:pointer;">
                        <input
                          type="radio"
                          name="address_id"
                          value="{{ $address->id }}"
                          onchange="this.form.requestSubmit()"
                          @checked($selectedAddress?->id === $address->id)
                        />
                        <span class="font-body--md-500">
                          {{ $address->name }}
                          <span style="text-transform:capitalize;color:#666666;font-weight:400;">({{ $address->type }})</span>
                          @if ($address->is_default)
                            <span style="color:#00B307;font-weight:400;">&middot; Default</span>
                          @endif
                        </span>
                        <span class="font-body--md-400" style="display:block;">
                          {{ $address->address_line_1 }}@if ($address->address_line_2), {{ $address->address_line_2 }}@endif,
                          {{ $address->city }}, {{ $address->state }} {{ $address->pincode }}, {{ $address->country }}
                          &middot; {{ $address->phone }}
                        </span>
                      </label>
                    @endforeach
                  </form>
                @endif

                <details @if ($addresses->isEmpty()) open @endif>
                  <summary style="cursor:pointer;color:#00B307;font-weight:500;">+ Add a new address</summary>
                  <form action="{{ route('addresses.store') }}" method="POST" style="margin-top:16px;">
                    @csrf
                    @include('partials.address-fields', ['address' => null, 'prefix' => 'checkout-new'])
                    <div class="contact-form-btn">
                      <button class="button button--md" type="submit">Save Address</button>
                    </div>
                  </form>
                </details>
              </div>

              <!-- Coupon Code -->
              <div class="newsletter-card" style="margin-bottom:24px;">
                <h5 class="newsletter-card-title font-body--xxl-500">Coupon Code</h5>
                @if ($appliedCoupon)
                  <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;">
                    <p class="font-body--md-400">
                      Applied: <strong>{{ $appliedCoupon->code }}</strong>
                      &mdash; you save ₹{{ number_format($discountAmount, 2) }}
                    </p>
                    <form action="{{ route('checkout.coupon.remove') }}" method="POST">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="button button--md button--disable">Remove</button>
                    </form>
                  </div>
                @else
                  <form action="{{ route('checkout.coupon.apply') }}" method="POST">
                    @csrf
                    <div class="newsletter-card__input">
                      <input type="text" name="code" placeholder="Enter Code" aria-label="Coupon code" />
                      <button class="button button--lg" type="submit">Apply Coupon</button>
                    </div>
                  </form>
                @endif
              </div>

              <!-- Payment Method -->
              <div class="cart-table" style="padding:24px;margin-bottom:24px;">
                <h5 class="font-body--xxl-500" style="margin-bottom:16px;">Payment Method</h5>

                <form id="place-order-form" action="{{ route('checkout.store') }}" method="POST">
                  @csrf
                  <input type="hidden" name="address_id" value="{{ $selectedAddress?->id }}" />

                  <label style="display:block;margin-bottom:12px;cursor:pointer;">
                    <input type="radio" name="payment_method" value="cod" checked /> Cash on Delivery
                  </label>
                  <label style="display:block;margin-bottom:12px;cursor:pointer;">
                    <input type="radio" name="payment_method" value="upi" /> UPI
                  </label>
                  <label style="display:block;cursor:pointer;">
                    <input type="radio" name="payment_method" value="bank_transfer" /> Bank Transfer
                  </label>
                </form>
              </div>

              <!-- Order Review -->
              <div class="cart-table">
                <div class="table-responsive">
                  <table class="table">
                    <thead>
                      <tr>
                        <th scope="col" class="cart-table-title">Product</th>
                        <th scope="col" class="cart-table-title">Price</th>
                        <th scope="col" class="cart-table-title">Quantity</th>
                        <th scope="col" class="cart-table-title">Subtotal</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($lines as $line)
                        <tr>
                          <td class="cart-table-item align-middle">
                            <div class="cart-table__product-item">
                              <div class="cart-table__product-item-img">
                                <img src="{{ $line['thumbnail_url'] ?? asset('images/products/img-01.png') }}" alt="{{ $line['product']->name ?? 'Product' }}" />
                              </div>
                              <div>
                                <h5 class="font-body--lg-400">{{ $line['product']->name ?? 'Product' }}</h5>
                                <p class="font-body--md-400">{{ $line['variant_label'] }}</p>
                              </div>
                            </div>
                          </td>
                          <td class="cart-table-item order-date align-middle">₹{{ number_format($line['unit_price'], 2) }}</td>
                          <td class="cart-table-item order-total align-middle">{{ $line['quantity'] }}</td>
                          <td class="cart-table-item order-subtotal align-middle">₹{{ number_format($line['subtotal'], 2) }}</td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <div class="col-lg-4">
              <div class="bill-card">
                <div class="bill-card__content">
                  <div class="bill-card__header">
                    <h2 class="bill-card__header-title font-body--xxl-500">Order Summery</h2>
                  </div>
                  <div class="bill-card__body">
                    <div class="bill-card__memo">
                      <div class="bill-card__memo-item subtotal">
                        <p class="font-body--md-400">Subtotal:</p>
                        <span class="font-body--md-500">₹{{ number_format($subtotal, 2) }}</span>
                      </div>
                      @if ($discountAmount > 0)
                        <div class="bill-card__memo-item subtotal">
                          <p class="font-body--md-400">Discount:</p>
                          <span class="font-body--md-500">&minus; ₹{{ number_format($discountAmount, 2) }}</span>
                        </div>
                      @endif
                      <div class="bill-card__memo-item shipping">
                        <p class="font-body--md-400">Shipping:</p>
                        <span class="font-body--md-500">
                          {{ $shippingAmount > 0 ? '₹'.number_format($shippingAmount, 2) : 'Free' }}
                        </span>
                      </div>
                      @if ($taxAmount > 0)
                        <div class="bill-card__memo-item subtotal">
                          <p class="font-body--md-400">Tax:</p>
                          <span class="font-body--md-500">₹{{ number_format($taxAmount, 2) }}</span>
                        </div>
                      @endif
                      <div class="bill-card__memo-item total">
                        <p class="font-body--lg-400">Total:</p>
                        <span class="font-body--xl-500">₹{{ number_format($grandTotal ?? $subtotal, 2) }}</span>
                      </div>
                    </div>

                    <button
                      class="button button--lg w-100"
                      style="margin-top: 20px"
                      type="submit"
                      form="place-order-form"
                      @unless ($selectedAddress) disabled aria-disabled="true" title="Add a delivery address first" @endunless
                    >
                      Place Order
                    </button>
                    @unless ($selectedAddress)
                      <p class="font-body--md-400" style="margin-top:12px;color:#EA4B48;">Add a delivery address to continue.</p>
                    @endunless
                  </div>
                </div>
              </div>
            </div>
          </div>
        @endif
      </div>
    </section>

    <script src="{{ asset('lib/js/jquery.min.js') }}"></script>
    <script src="{{ asset('lib/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
  </body>
</html>
</x-layouts.app>
