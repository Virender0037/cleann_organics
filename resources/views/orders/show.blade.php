<x-layouts.app :noindex="true">
    @php
        // Maps the enum onto the template's fixed 4-step tracker. Cancelled
        // orders don't fit that linear progression, so they're called out
        // separately instead of being forced onto a step.
        $currentStep = match ($order->order_status) {
            'pending' => 1,
            'confirmed', 'packed' => 2,
            'shipped' => 3,
            'delivered' => 4,
            default => 1,
        };
    @endphp
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
            <li>
              <a href="{{ route('user-dashboard') }}">Account<span> > </span></a>
            </li>
            <li class="active"><a href="{{ route('orders.show', $order) }}">Order Details</a></li>
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
            <x-account-nav active="orders" />
          </div>
          <div class="col-lg-9 section--xl pt-0">
            <div class="container">
              <div class="dashboard__order-history">
                <div class="dashboard__order-history-title">
                  <h2 class="font-body--xxl-500">Order Details</h2>
                  <a href="{{ route('order-history') }}">back to list</a>
                </div>

                <div class="dashboard__details-content">
                  <div class="row">
                    <div class="col-xl-8">
                      <div class="dashboard__details-card">
                        <div class="dashboard__details-card-item">
                          <h5 class="dashboard__details-card-title">Delivery Address</h5>
                          @php $ship = $order->shippingSnapshot(); @endphp
                          @if ($ship['name'])
                            <div class="dashboard__details-card-item__inner">
                              <h2 class="font-body--lg-400 name">{{ $ship['name'] }}</h2>
                              <p class="font-body--md-400">
                                {{ $ship['address_line_1'] }}@if ($ship['address_line_2']), {{ $ship['address_line_2'] }}@endif,
                                {{ $ship['city'] }}, {{ $ship['state'] }} {{ $ship['pincode'] }}, {{ $ship['country'] }}
                              </p>
                            </div>
                            <div class="dashboard__details-card-item__inner">
                              <div class="dashboard__details-card-item__inner-contact">
                                <h5 class="title">Phone</h5>
                                <p class="font-body--md-400">{{ $ship['phone'] }}</p>
                              </div>
                            </div>
                          @else
                            <p class="font-body--md-400">No delivery address recorded for this order.</p>
                          @endif
                        </div>

                        <div class="dashboard__details-card-item">
                          <h5 class="dashboard__details-card-title">Billing Address</h5>
                          @if ($order->billing_same_as_shipping)
                            <p class="font-body--md-400">Same as delivery address.</p>
                          @else
                            @php $bill = $order->billingSnapshot(); @endphp
                            <div class="dashboard__details-card-item__inner">
                              <h2 class="font-body--lg-400 name">{{ $bill['name'] }}</h2>
                              <p class="font-body--md-400">
                                {{ $bill['address_line_1'] }}@if ($bill['address_line_2']), {{ $bill['address_line_2'] }}@endif,
                                {{ $bill['city'] }}, {{ $bill['state'] }} {{ $bill['pincode'] }}, {{ $bill['country'] }}
                              </p>
                              <p class="font-body--md-400">{{ $bill['phone'] }}</p>
                            </div>
                          @endif
                        </div>
                      </div>
                    </div>
                    <div class="col-xl-4">
                      <div class="dashboard__totalpayment-card">
                        <div class="dashboard__totalpayment-card-header">
                          <div class="dashboard__totalpayment-card-header-item">
                            <h5 class="title">Order Number:</h5>
                            <p class="details order-id">#{{ $order->order_number }}</p>
                          </div>
                          <div class="dashboard__totalpayment-card-header-item">
                            <h5 class="title">Order Date:</h5>
                            <p class="details order-id">{{ $order->created_at->format('d M Y') }}</p>
                          </div>
                        </div>

                        <div class="dashboard__totalpayment-card-body">
                          <div class="dashboard__totalpayment-card-body-item">
                            <h5 class="font-body--md-400">Order Status:</h5>
                            <p class="font-body--md-500" style="text-transform:capitalize;">{{ $order->order_status }}</p>
                          </div>
                          <div class="dashboard__totalpayment-card-body-item">
                            <h5 class="font-body--md-400">Payment:</h5>
                            <p class="font-body--md-500">
                              {{ strtoupper(str_replace('_', ' ', $order->payment_method)) }}
                              <span class="font-body--md-400" style="text-transform:capitalize;color:#666666;">&middot; {{ $order->payment_status }}</span>
                            </p>
                          </div>
                          <div class="dashboard__totalpayment-card-body-item">
                            <h5 class="font-body--md-400">Subtotal:</h5>
                            <p class="font-body--md-500">₹{{ number_format($order->subtotal, 2) }}</p>
                          </div>
                          @if ($order->discount_amount > 0)
                            <div class="dashboard__totalpayment-card-body-item">
                              <h5 class="font-body--md-400">Discount:</h5>
                              <p class="font-body--md-500">&minus; ₹{{ number_format($order->discount_amount, 2) }}</p>
                            </div>
                          @endif
                          <div class="dashboard__totalpayment-card-body-item">
                            <h5 class="font-body--md-400">
                              Shipping:@if ($order->shipping_zone_name) <span class="font-body--md-400" style="color:#666666;">({{ $order->shipping_zone_name }})</span>@endif
                            </h5>
                            <p class="font-body--md-500">
                              {{ $order->shipping_amount > 0 ? '₹'.number_format($order->shipping_amount, 2) : 'Free' }}
                            </p>
                          </div>
                          @if ($order->tax_amount > 0)
                            <div class="dashboard__totalpayment-card-body-item">
                              <h5 class="font-body--md-400">Tax:</h5>
                              <p class="font-body--md-500">₹{{ number_format($order->tax_amount, 2) }}</p>
                            </div>
                          @endif
                          <div class="dashboard__totalpayment-card-body-item total">
                            <h5 class="font-body--xl-400">Total:</h5>
                            <p class="font-body--xl-500">₹{{ number_format($order->grand_total, 2) }}</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                @if ($order->order_status === 'cancelled')
                  <p class="font-body--lg-400" style="color:#EA4B48;margin:24px 0;">This order was cancelled.</p>
                @else
                  <div class="progress__bar progress__bar-1x">
                    <div class="progress__bar-border">
                      <span class="progress__bar-border-active"></span>
                    </div>
                    @foreach (['Order received', 'Processing', 'On the way', 'Delivered'] as $index => $label)
                      @php $step = $index + 1; @endphp
                      <div class="progress__bar-item @if ($step <= $currentStep) active @endif">
                        <div class="progress__bar-item-ball">
                          @if ($step < $currentStep)
                            <p class="font-body--md-400 count-number count-number-active">{{ sprintf('%02d', $step) }}</p>
                            <span class="check-mark">
                              <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16.6663 5.83301L7.49967 14.9997L3.33301 10.833" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                              </svg>
                            </span>
                          @else
                            <p class="font-body--md-400 count-number @if ($step === $currentStep) count-number-active @endif">{{ sprintf('%02d', $step) }}</p>
                          @endif
                        </div>
                        <h2 class="font-body--md-400">{{ $label }}</h2>
                      </div>
                    @endforeach
                  </div>
                @endif

                <div class="dashboard__order-history-table dashboard__order-history-table__product-content">
                  <div class="table-responsive">
                    <table class="table">
                      <thead>
                        <tr>
                          <th scope="col" class="dashboard__order-history-table-title">Product</th>
                          <th scope="col" class="dashboard__order-history-table-title">Price</th>
                          <th scope="col" class="dashboard__order-history-table-title">quantity</th>
                          <th scope="col" class="dashboard__order-history-table-title">Subtotal</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($order->items as $item)
                          @php
                              $thumbnail = $item->product?->thumbnailImage();
                          @endphp
                          <tr>
                            <td class="dashboard__order-history-table-item align-middle">
                              <div class="dashboard__product-item">
                                <div class="dashboard__product-item-img">
                                  <img src="{{ $thumbnail ? \Illuminate\Support\Facades\Storage::url($thumbnail->image) : asset('images/products/img-01.png') }}" alt="{{ $item->product_name }}" />
                                </div>
                                <div>
                                  <h5 class="font-body--md-400">{{ $item->product_name }}</h5>
                                  @php
                                      // Variant details as snapshotted at order time — never re-read
                                      // from the live variant.
                                      $variantBits = array_filter([
                                          $item->variant_sku ? 'SKU '.$item->variant_sku : null,
                                          $item->variant_size,
                                          $item->variant_color,
                                          $item->variant_pack_quantity ? 'Pack of '.$item->variant_pack_quantity : null,
                                          ($item->weight && $item->unit) ? rtrim(rtrim(number_format((float) $item->weight, 2), '0'), '.').' '.$item->unit : null,
                                      ]);
                                  @endphp
                                  @if ($variantBits)
                                    <p class="font-body--md-400" style="color:#666666;">{{ implode(' · ', $variantBits) }}</p>
                                  @endif
                                </div>
                              </div>
                            </td>
                            <td class="dashboard__order-history-table-item order-date align-middle">₹{{ number_format($item->unit_price, 2) }}</td>
                            <td class="dashboard__order-history-table-item order-total align-middle">
                              <p class="order-total-price">x{{ $item->quantity }}</p>
                            </td>
                            <td class="dashboard__order-history-table-item order-status align-middle" style="text-align: left">
                              <p class="font-body--md-500">₹{{ number_format($item->total_price, 2) }}</p>
                            </td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- dashboard Secton  End  -->

    <script src="{{ asset('lib/js/jquery.min.js') }}"></script>
    <script src="{{ asset('lib/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
  </body>
</html>
</x-layouts.app>
