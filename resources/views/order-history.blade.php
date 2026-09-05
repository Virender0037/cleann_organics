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
            <li class="active"><a href="{{ route('order-history') }}">Order History</a></li>
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
                  <h2 class="font-body--xxl-500">Order History</h2>
                </div>

                @if ($orders->isEmpty())
                  <div style="border:1px solid #e5e5e5;border-radius:8px;padding:48px 24px;text-align:center;">
                    <p class="font-body--lg-400" style="margin-bottom:16px;">You haven't placed any orders yet.</p>
                    <a href="{{ route('shop') }}" class="button button--md">Start Shopping</a>
                  </div>
                @else
                  <div class="dashboard__order-history-table">
                    <div class="table-responsive">
                      <table class="table">
                        <thead>
                          <tr>
                            <th scope="col" class="dashboard__order-history-table-title">Order</th>
                            <th scope="col" class="dashboard__order-history-table-title">Date</th>
                            <th scope="col" class="dashboard__order-history-table-title">Total</th>
                            <th scope="col" class="dashboard__order-history-table-title">Payment</th>
                            <th scope="col" class="dashboard__order-history-table-title">Status</th>
                            <th scope="col" class="dashboard__order-history-table-title"></th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach ($orders as $order)
                            <tr>
                              <td class="dashboard__order-history-table-item order-id">#{{ $order->order_number }}</td>
                              <td class="dashboard__order-history-table-item order-date">{{ $order->created_at->format('d M Y') }}</td>
                              <td class="dashboard__order-history-table-item">₹{{ number_format($order->grand_total, 2) }}</td>
                              <td class="dashboard__order-history-table-item">
                                {{ strtoupper(str_replace('_', ' ', $order->payment_method)) }}
                                <span class="font-body--md-400" style="text-transform:capitalize;color:#666666;">({{ $order->payment_status }})</span>
                              </td>
                              <td class="dashboard__order-history-table-item order-status" style="text-transform:capitalize;">{{ $order->order_status }}</td>
                              <td class="dashboard__order-history-table-item">
                                <a href="{{ route('orders.show', $order) }}">View Details</a>
                              </td>
                            </tr>
                          @endforeach
                        </tbody>
                      </table>
                    </div>
                  </div>

                  <div style="margin-top:24px;">
                    {{ $orders->links() }}
                  </div>
                @endif
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
