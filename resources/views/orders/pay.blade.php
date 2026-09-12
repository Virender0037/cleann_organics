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
            <li class="active"><a href="{{ route('orders.pay', $order) }}">Complete Payment</a></li>
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
                <h2 class="section--title-four font-title--sm">Complete Payment</h2>
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
                <span class="font-body--xl-500">₹{{ number_format((float) $payment->amount, 2) }}</span>
              </div>

              @if ($gatewayError)
                <p class="font-body--md-400" style="color:#EA4B48;text-align:center;margin-bottom:16px;">{{ $gatewayError }}</p>
                <div style="display:flex;gap:12px;justify-content:center;">
                  <a href="{{ route('orders.pay', $order) }}" class="button button--md">Try Again</a>
                  <a href="{{ route('orders.show', $order) }}" class="button button--md button--disable">Back to Order</a>
                </div>
              @else
                <button id="razorpay-pay-button" type="button" class="button button--lg w-100">
                  Pay ₹{{ number_format((float) $payment->amount, 2) }} Now
                </button>
                <p class="font-body--sm-400" style="text-align:center;margin-top:12px;color:#666666;">
                  You'll be able to choose UPI, card, netbanking or wallet inside Razorpay's secure checkout.
                </p>
                <div style="text-align:center;margin-top:16px;">
                  <a href="{{ route('orders.show', $order) }}" class="font-body--sm-400" style="color:#666666;">Cancel and go back to order</a>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </section>

    @unless ($gatewayError)
      <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
      <script>
        (function () {
          var payButton = document.getElementById('razorpay-pay-button');
          if (!payButton) return;

          function openCheckout() {
            var options = {
              key: @json($razorpayKeyId),
              amount: @json($amountInPaise),
              currency: 'INR',
              name: @json(config('app.name')),
              description: 'Order #{{ $order->order_number }}',
              order_id: @json($payment->gateway_order_id),
              prefill: {
                name: @json($customerName),
                email: @json($customerEmail),
                contact: @json($customerPhone)
              },
              theme: { color: '#00B307' },
              handler: function (response) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = @json(route('orders.pay.verify', $order));

                var fields = {
                  _token: @json(csrf_token()),
                  razorpay_payment_id: response.razorpay_payment_id,
                  razorpay_order_id: response.razorpay_order_id,
                  razorpay_signature: response.razorpay_signature
                };

                Object.keys(fields).forEach(function (key) {
                  var input = document.createElement('input');
                  input.type = 'hidden';
                  input.name = key;
                  input.value = fields[key];
                  form.appendChild(input);
                });

                document.body.appendChild(form);
                form.submit();
              },
              modal: {
                ondismiss: function () {
                  fetch(@json(route('orders.pay.cancel', $order)), {
                    method: 'POST',
                    headers: {
                      'X-CSRF-TOKEN': @json(csrf_token()),
                      'Accept': 'application/json'
                    }
                  });
                }
              }
            };

            var rzp = new Razorpay(options);
            rzp.on('payment.failed', function () {
              // Razorpay already shows the customer a failure message inside
              // its own modal; the webhook (and, if the modal is then
              // dismissed, the ondismiss handler above) is what records it
              // server-side, so nothing else is needed here.
            });
            rzp.open();
          }

          payButton.addEventListener('click', openCheckout);
        })();
      </script>
    @endunless

    <script src="{{ asset('lib/js/jquery.min.js') }}"></script>
    <script src="{{ asset('lib/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
  </body>
</html>
</x-layouts.app>
