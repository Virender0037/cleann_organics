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
            <li class="active">
              <a href="{{ route('shopping-cart') }}">Shopping cart</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <!-- breedcrumb section end   -->

    <!-- Shopping Cart Section Start   -->
    <section class="shoping-cart section section--xl">
      <div class="container">
        <div class="section__head justify-content-center">
          <h2 class="section--title-four font-title--sm">My Shopping Cart</h2>
        </div>
        @unless ($lines->isEmpty())
          <x-frontend.offer-bar :offers="$offers" :eligible="$eligibleAmount" />
        @endunless
        <div class="row shoping-cart__content">
          <div class="col-lg-8">
            <div class="cart-table">
              <div class="table-responsive">
                <table class="table">
                  <thead>
                    <tr>
                      <th scope="col" class="cart-table-title">Product</th>
                      <th scope="col" class="cart-table-title">Price</th>
                      <th scope="col" class="cart-table-title">quantity</th>
                      <th scope="col" class="cart-table-title">Subtotal</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse ($lines as $line)
                    <tr data-cart-line data-item-key="{{ $line['key'] }}">
                      <!-- Product item  -->
                      <td class="cart-table-item align-middle">
                        <a
                          href="{{ $line['product_url'] ?? '#' }}"
                          class="cart-table__product-item"
                        >
                          <div class="cart-table__product-item-img">
                            <img
                              src="{{ $line['thumbnail_url'] ?? asset('images/products/img-01.png') }}"
                              alt="{{ $line['product']->name ?? 'Product' }}"
                            />
                          </div>
                          <div>
                            <h5 class="font-body--lg-400">{{ $line['product']->name ?? 'Product' }}</h5>
                            @if ($line['available'])
                              <p class="font-body--md-400">{{ $line['variant_label'] }}</p>
                            @else
                              <p class="font-body--md-400" style="color:#c0392b;">No longer available</p>
                            @endif
                          </div>
                        </a>
                      </td>
                      <!-- Price  -->
                      <td class="cart-table-item order-date align-middle">
                        @if ($line['available'])
                          ₹{{ number_format($line['unit_price'], 2) }}
                        @else
                          —
                        @endif
                      </td>
                      <!-- quantity -->
                      <td class="cart-table-item order-total align-middle">
                        @if ($line['available'])
                          <form action="{{ route('cart.items.update', $line['key']) }}" method="POST" data-cart-form="update">
                            @csrf
                            @method('PATCH')
                            <div class="counter-btn-wrapper">
                              <button
                                type="button"
                                class="counter-btn-dec counter-btn"
                                aria-label="Decrease quantity for {{ $line['product']->name ?? 'item' }}"
                                onclick="var i=this.nextElementSibling; if (parseInt(i.value,10) > parseInt(i.min,10)) { i.stepDown(); i.form.requestSubmit(); }"
                              >-</button>
                              <input
                                type="number"
                                name="quantity"
                                class="counter-btn-counter"
                                min="1"
                                max="{{ $line['variant']->stock_quantity }}"
                                value="{{ $line['quantity'] }}"
                                aria-label="Quantity for {{ $line['product']->name ?? 'item' }}"
                                onchange="this.form.requestSubmit()"
                              />
                              <button
                                type="button"
                                class="counter-btn-inc counter-btn"
                                aria-label="Increase quantity for {{ $line['product']->name ?? 'item' }}"
                                onclick="var i=this.previousElementSibling; if (parseInt(i.value,10) < parseInt(i.max,10)) { i.stepUp(); i.form.requestSubmit(); }"
                              >+</button>
                            </div>
                          </form>
                        @else
                          —
                        @endif
                      </td>
                      <!-- Subtotal  -->
                      <td class="cart-table-item order-subtotal align-middle">
                        <div
                          class="
                            d-flex
                            justify-content-between
                            align-items-center
                          "
                        >
                          <p class="font-body--md-500">₹{{ number_format($line['subtotal'], 2) }}</p>
                          <form action="{{ route('cart.items.destroy', $line['key']) }}" method="POST" data-cart-form="remove">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-item" aria-label="Remove {{ $line['product']->name ?? 'item' }} from cart">
                              <svg
                                width="24"
                                height="25"
                                viewBox="0 0 24 25"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                              >
                                <path
                                  d="M12 23.5C18.0748 23.5 23 18.5748 23 12.5C23 6.42525 18.0748 1.5 12 1.5C5.92525 1.5 1 6.42525 1 12.5C1 18.5748 5.92525 23.5 12 23.5Z"
                                  stroke="#CCCCCC"
                                  stroke-miterlimit="10"
                                />
                                <path
                                  d="M16 8.5L8 16.5"
                                  stroke="#666666"
                                  stroke-width="1.5"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"
                                />
                                <path
                                  d="M16 16.5L8 8.5"
                                  stroke="#666666"
                                  stroke-width="1.5"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"
                                />
                              </svg>
                            </button>
                          </form>
                        </div>
                      </td>
                    </tr>
                    @empty
                    <tr>
                      <td colspan="4" class="align-middle" style="text-align:center; padding: 40px 0;">
                        <p class="font-body--lg-400" style="margin-bottom:16px;">Your cart is empty.</p>
                        <a href="{{ route('shop') }}" class="button button--md">Continue Shopping</a>
                      </td>
                    </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
              <!-- Action Buttons  -->
              <div class="cart-table-action-btn d-flex">
                <a
                  href="{{ route('shop') }}"
                  class="button button--md button--disable shop"
                  >Return to Shop</a
                >
                @if ($lines->isNotEmpty())
                  <form action="{{ route('cart.clear') }}" method="POST" data-cart-form="clear" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="button button--md button--disable update">Clear Cart</button>
                  </form>
                @endif
              </div>
            </div>

            <div class="shoping-cart__mobile">
              @forelse ($lines as $line)
              <div class="shoping-card" data-cart-line data-item-key="{{ $line['key'] }}">
                <div class="shoping-card__img-wrapper">
                  <img
                    src="{{ $line['thumbnail_url'] ?? asset('images/products/img-01.png') }}"
                    alt="{{ $line['product']->name ?? 'product-item' }}"
                  />
                </div>
                <h5 class="shoping-card__product-caption font-body--lg-400">
                  {{ $line['product']->name ?? 'Product' }}
                </h5>

                <h6 class="shoping-card__product-price font-body--lg-400">
                  @if ($line['available'])
                    ₹{{ number_format($line['unit_price'], 2) }}
                  @else
                    <span style="color:#c0392b;">Unavailable</span>
                  @endif
                </h6>

                @if ($line['available'])
                  <form action="{{ route('cart.items.update', $line['key']) }}" method="POST" data-cart-form="update">
                    @csrf
                    @method('PATCH')
                    <div class="counter-btn-wrapper">
                      <button
                        type="button"
                        class="counter-btn-dec counter-btn"
                        aria-label="Decrease quantity for {{ $line['product']->name ?? 'item' }}"
                        onclick="var i=this.nextElementSibling; if (parseInt(i.value,10) > parseInt(i.min,10)) { i.stepDown(); i.form.requestSubmit(); }"
                      >-</button>
                      <input
                        type="number"
                        name="quantity"
                        class="counter-btn-counter"
                        min="1"
                        max="{{ $line['variant']->stock_quantity }}"
                        value="{{ $line['quantity'] }}"
                        aria-label="Quantity for {{ $line['product']->name ?? 'item' }}"
                        onchange="this.form.requestSubmit()"
                      />
                      <button
                        type="button"
                        class="counter-btn-inc counter-btn"
                        aria-label="Increase quantity for {{ $line['product']->name ?? 'item' }}"
                        onclick="var i=this.previousElementSibling; if (parseInt(i.value,10) < parseInt(i.max,10)) { i.stepUp(); i.form.requestSubmit(); }"
                      >+</button>
                    </div>
                  </form>
                @endif
                <h6 class="shoping-card__product-totalprice font-body--lg-600">
                  ₹{{ number_format($line['subtotal'], 2) }}
                </h6>
                <form action="{{ route('cart.items.destroy', $line['key']) }}" method="POST" data-cart-form="remove">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="close-btn" aria-label="Remove {{ $line['product']->name ?? 'item' }} from cart">
                    <svg
                      width="24"
                      height="24"
                      viewBox="0 0 24 24"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        d="M12 23C18.0748 23 23 18.0748 23 12C23 5.92525 18.0748 1 12 1C5.92525 1 1 5.92525 1 12C1 18.0748 5.92525 23 12 23Z"
                        stroke="#CCCCCC"
                        stroke-miterlimit="10"
                      />
                      <path
                        d="M16 8L8 16"
                        stroke="#666666"
                        stroke-width="1.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                      <path
                        d="M16 16L8 8"
                        stroke="#666666"
                        stroke-width="1.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                    </svg>
                  </button>
                </form>
              </div>
              @empty
              <div class="shoping-card">
                <p class="font-body--lg-400" style="text-align:center; padding: 24px 0;">Your cart is empty.</p>
                <a href="{{ route('shop') }}" class="button button--md" style="display:block; text-align:center;">Continue Shopping</a>
              </div>
              @endforelse

              @if ($lines->isNotEmpty())
              <div class="cart-table-action-btn d-flex">
                <a
                  href="{{ route('shop') }}"
                  class="button button--md button--disable shop"
                  >Return to Shop</a
                >
                <form action="{{ route('cart.clear') }}" method="POST" data-cart-form="clear" style="display:inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="button button--md button--disable update">Clear Cart</button>
                </form>
              </div>
              @endif
            </div>

            <!-- newsletter  -->
            <div class="newsletter-card">
              <h5 class="newsletter-card-title font-body--xxl-500">
                Coupon Code
              </h5>
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
                    <input type="text" id="coupon-code" name="code" placeholder="Enter Code" />
                    <button class="button button--lg" type="submit">
                      Apply Coupon
                    </button>
                  </div>
                </form>
              @endif
            </div>
          </div>

          <div class="col-lg-4">
            <div class="bill-card">
              <div class="bill-card__content">
                <div class="bill-card__header">
                  <h2 class="bill-card__header-title font-body--xxl-500">
                    Order Summery
                  </h2>
                </div>
                <div class="bill-card__body">
                  <!-- memo  -->
                  <div class="bill-card__memo">
                    <!-- Subtotal  -->
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
                    <!-- Shipping  -->
                    <div class="bill-card__memo-item shipping">
                      <p class="font-body--md-400">Shipping:</p>
                      <span class="font-body--md-500">{{ $shippingEstimate > 0 ? '₹'.number_format($shippingEstimate, 2) : 'Free' }}</span>
                    </div>
                    <!-- total  -->
                    <div class="bill-card__memo-item total">
                      <p class="font-body--lg-400">Total:</p>
                      <span class="font-body--xl-500">₹{{ number_format($subtotal - $discountAmount + $shippingEstimate, 2) }}</span>
                    </div>
                    <p class="bill-card__tax-note font-body--sm-400">
                      Inclusive of all taxes{{ $taxIncluded > 0 ? " (includes GST ₹".number_format($taxIncluded, 2).")" : "" }}.
                      Free shipping on orders above ₹{{ rtrim(rtrim(number_format(app(\App\Services\Storefront\StorefrontSettings::class)->freeShippingThreshold(), 2, '.', ''), '0'), '.') }}.
                      Final shipping is confirmed at checkout.
                    </p>
                  </div>
                  @if ($lines->isEmpty())
                    <button
                      class="button button--lg w-100"
                      style="margin-top: 20px"
                      type="button"
                      disabled
                      aria-disabled="true"
                    >
                      Proceed to Checkout
                    </button>
                  @else
                    <a
                      href="{{ route('checkout') }}"
                      class="button button--lg w-100"
                      style="margin-top: 20px; display: block; text-align: center;"
                    >
                      Proceed to Checkout
                    </a>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- Shopping Cart Section End    -->

    <script src="{{ asset('lib/js/jquery.min.js') }}"></script>
    <script src="{{ asset('lib/js/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('lib/js/bvselect.js') }}"></script>
    <script src="{{ asset('lib/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ admin_asset('js/cart.js') }}"></script>
  </body>
</html>
</x-layouts.app>
