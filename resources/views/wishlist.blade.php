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
            <li class="active"><a href="{{ route('wishlist') }}">Wishlist</a></li>
          </ul>
        </div>
      </div>
    </div>
    <!-- breedcrumb section end   -->

    <!-- Shopping Cart Section Start   -->
    <section class="shoping-cart section section--xl">
      <div class="container">
        <div class="section__head justify-content-center">
          <h2 class="section--title-four font-title--sm">My Wishlist</h2>
        </div>
        <div class="shoping-cart__content">
          @if ($lines->isEmpty())
            <div class="cart-table" style="padding:60px 24px;text-align:center;">
              <p class="font-body--lg-400" style="margin-bottom:16px;">Your wishlist is empty.</p>
              <a href="{{ route('shop') }}" class="button button--md">Continue Shopping</a>
            </div>
          @else
          <div class="cart-table">
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th scope="col" class="cart-table-title">Product</th>
                    <th scope="col" class="cart-table-title">Price</th>
                    <th scope="col" class="cart-table-title">Stock Status</th>
                    <th scope="col" class="cart-table-title"></th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($lines as $wishlist)
                    @php
                        $product = $wishlist->product;
                        $variant = $product->variants->first();
                        $headline = $variant?->headlineTier();
                        $available = $variant?->isPurchasable() ?? false;
                        $thumbnail = $product->thumbnailImage();
                    @endphp
                    <tr data-wishlist-line data-product-id="{{ $product->id }}">
                      <!-- Product item  -->
                      <td class="cart-table-item align-middle">
                        <a
                          href="{{ route('products.show', $product->slug) }}"
                          class="cart-table__product-item"
                        >
                          <div class="cart-table__product-item-img">
                            <img
                              src="{{ storage_image_url($thumbnail?->image, asset('images/products/img-01.png')) }}"
                              alt="{{ $product->name }}"
                            />
                          </div>
                          <h5 class="font-body--lg-400">{{ $product->name }}</h5>
                        </a>
                      </td>
                      <!-- Price  -->
                      <td class="cart-table-item order-date align-middle">
                        @if ($headline)
                          <p class="font-body--lg-500">
                            @if ($variant->hasMultipleTiers())
                              <span class="font-body--md-400">From</span>
                            @endif
                            ₹{{ number_format($headline['price'], 2) }}
                            @if ($headline['compare_price'])
                              <del>₹{{ number_format($headline['compare_price'], 2) }}</del>
                            @endif
                          </p>
                        @else
                          <p class="font-body--lg-500">Price unavailable</p>
                        @endif
                      </td>
                      <!-- Stock Status  -->
                      <td class="cart-table-item stock-status align-middle">
                        @if ($available)
                          <span class="font-body--md-400 in">{{ $variant->stockLabel() }}</span>
                        @else
                          <span class="font-body--md-400 out">{{ $variant ? $variant->stockLabel() : 'Unavailable' }}</span>
                        @endif
                      </td>
                      <td class="cart-table-item add-cart align-middle">
                        <div class="add-cart__wrapper">
                          @if ($available)
                            <form action="{{ route('cart.items.store') }}" method="POST" data-cart-form="add">
                              @csrf
                              <input type="hidden" name="product_variant_id" value="{{ $variant->id }}" />
                              <input type="hidden" name="quantity" value="1" />
                              <button type="submit" class="button button--md">Add to Cart</button>
                            </form>
                          @else
                            <button type="button" class="button button--md button--disable" disabled aria-disabled="true">Add to Cart</button>
                          @endif
                          <form action="{{ route('wishlist.destroy', $product) }}" method="POST" data-wishlist-form>
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-item" aria-label="Remove {{ $product->name }} from wishlist">
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
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>

          <div class="shoping-cart__mobile">
            @foreach ($lines as $wishlist)
              @php
                  $product = $wishlist->product;
                  $variant = $product->variants->first();
                  $headline = $variant?->headlineTier();
                  $available = $variant?->isPurchasable() ?? false;
                  $thumbnail = $product->thumbnailImage();
              @endphp
              <div class="shoping-card" data-wishlist-line data-product-id="{{ $product->id }}">
                <div class="shoping-card__img-wrapper">
                  <a href="{{ route('products.show', $product->slug) }}">
                    <img
                      src="{{ storage_image_url($thumbnail?->image, asset('images/products/img-01.png')) }}"
                      alt="{{ $product->name }}"
                    />
                  </a>
                </div>
                <h5 class="shoping-card__product-caption font-body--lg-400">
                  <a href="{{ route('products.show', $product->slug) }}" style="color:inherit;">{{ $product->name }}</a>
                  @if ($available)
                    <span class="tag tag--in">{{ $variant->stockLabel() }}</span>
                  @else
                    <span class="tag tag--out">{{ $variant ? $variant->stockLabel() : 'Unavailable' }}</span>
                  @endif
                </h5>

                <h6 class="shoping-card__product-price font-body--lg-600">
                  @if ($headline)
                    ₹{{ number_format($headline['price'], 2) }}
                    @if ($headline['compare_price'])
                      <del class="prev-price">₹{{ number_format($headline['compare_price'], 2) }}</del>
                    @endif
                  @else
                    Price unavailable
                  @endif
                </h6>

                @if ($available)
                  <form action="{{ route('cart.items.store') }}" method="POST" data-cart-form="add" style="display:inline;">
                    @csrf
                    <input type="hidden" name="product_variant_id" value="{{ $variant->id }}" />
                    <input type="hidden" name="quantity" value="1" />
                    <button type="submit" class="button button--md">Add to Cart</button>
                  </form>
                @else
                  <button type="button" class="button button--md button--disable" disabled aria-disabled="true">Add to Cart</button>
                @endif
                <form action="{{ route('wishlist.destroy', $product) }}" method="POST" data-wishlist-form style="display:inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="close-btn" aria-label="Remove {{ $product->name }} from wishlist">
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
            @endforeach
          </div>
          @endif
        </div>
      </div>
    </section>
    <!-- Shopping Cart Section End    -->


    <script src="{{ asset('lib/js/jquery.min.js') }}"></script>
    <script src="{{ asset('lib/js/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('lib/js/bvselect.js') }}"></script>
    <script src="{{ asset('lib/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
  </body>
</html>
</x-layouts.app>
