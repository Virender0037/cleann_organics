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
            <li class="active"><a href="{{ route('orders.manual-upi.pay', $order) }}">Complete Payment</a></li>
          </ul>
        </div>
      </div>
    </div>
    <!-- breedcrumb section end   -->

    <section class="shoping-cart section section--xl">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-6">
            <div class="cart-table" style="padding:32px 24px;">
              <div class="section__head justify-content-center" style="margin-bottom:24px;">
                <h2 class="section--title-four font-title--sm">
                  {{ $payment->status === 'rejected' ? 'Resubmit Payment Proof' : 'Complete Payment via UPI' }}
                </h2>
              </div>

              @if (session('error'))
                <p class="font-body--md-400" style="color:#EA4B48;text-align:center;margin-bottom:16px;">{{ session('error') }}</p>
              @endif

              <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <span class="font-body--md-400">Order Number</span>
                <span class="font-body--md-500">#{{ $order->order_number }}</span>
              </div>
              <div style="display:flex;justify-content:space-between;margin-bottom:24px;">
                <span class="font-body--lg-400">Amount Payable</span>
                <span class="font-body--xl-500">₹{{ number_format($details['amount'], 2) }}</span>
              </div>

              @unless ($details['available'])
                <p class="font-body--md-400" style="color:#EA4B48;text-align:center;margin-bottom:16px;">
                  Manual UPI payment isn't available right now. Please go back to your order and choose another payment method, or contact us for help.
                </p>
                <div style="text-align:center;">
                  <a href="{{ route('orders.show', $order) }}" class="button button--md">Back to Order</a>
                </div>
              @else
                @if ($payment->status === 'rejected')
                  <div style="background:#FDECEC;border:1px solid #EA4B48;border-radius:8px;padding:12px 16px;margin-bottom:24px;">
                    <p class="font-body--sm-500" style="color:#EA4B48;margin-bottom:4px;">Your last payment could not be verified.</p>
                    @if ($payment->admin_note)
                      <p class="font-body--sm-400" style="color:#666666;">{{ $payment->admin_note }}</p>
                    @endif
                    <p class="font-body--sm-400" style="color:#666666;margin-top:4px;">Please pay again and submit your reference below.</p>
                  </div>
                @endif

                <div style="text-align:center;margin-bottom:16px;">
                  {!! $details['qrSvg'] !!}
                </div>

                <div style="text-align:center;margin-bottom:24px;">
                  <p class="font-body--sm-400" style="color:#666666;margin-bottom:4px;">Pay to UPI ID</p>
                  <p class="font-body--md-500">{{ $details['upiId'] }}</p>
                </div>

                <form action="{{ route('orders.manual-upi.pay.store', $order) }}" method="POST" enctype="multipart/form-data">
                  @csrf

                  <div style="margin-bottom:16px;">
                    <label class="font-body--md-500" style="display:block;margin-bottom:6px;">UPI Transaction / UTR Reference</label>
                    <input type="text" name="upi_reference" required maxlength="100" value="{{ old('upi_reference', $payment->upi_reference) }}"
                      class="form-control" placeholder="e.g. 123456789012" style="width:100%;padding:10px 12px;border:1px solid #e5e5e5;border-radius:6px;" />
                    @error('upi_reference')
                      <p class="font-body--sm-400" style="color:#EA4B48;margin-top:4px;">{{ $message }}</p>
                    @enderror
                  </div>

                  <div style="margin-bottom:20px;">
                    <label class="font-body--md-500" style="display:block;margin-bottom:6px;">Payment Screenshot (optional)</label>
                    <input type="file" name="screenshot" accept="image/jpeg,image/png,image/webp" style="width:100%;" />
                    @error('screenshot')
                      <p class="font-body--sm-400" style="color:#EA4B48;margin-top:4px;">{{ $message }}</p>
                    @enderror
                  </div>

                  <button type="submit" class="button button--lg w-100">Submit Payment Proof</button>
                </form>

                <div style="text-align:center;margin-top:16px;">
                  <a href="{{ route('orders.show', $order) }}" class="font-body--sm-400" style="color:#666666;">Back to order</a>
                </div>
              @endunless
            </div>
          </div>
        </div>
      </div>
    </section>

    <script src="{{ asset('lib/js/jquery.min.js') }}"></script>
    <script src="{{ asset('lib/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
  </body>
</html>
</x-layouts.app>
