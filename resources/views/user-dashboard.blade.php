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
            <li class="active"><a href="{{ route('user-dashboard') }}">Dashboard</a></li>
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
            <x-account-nav active="dashboard" />
          </div>
          <div class="col-lg-9 section--xl pt-0">
            <div class="container">
              <!-- Greeting + profile -->
              <div class="dashboard__user-profile dashboard-card" style="margin-bottom:24px;">
                <div class="dashboard__user-profile-info">
                  <h5 class="font-body--xxl-500 name">Hello, {{ $user->name }}</h5>
                  <p class="font-body--md-400 designation">{{ $user->email }}@if ($user->phone) &middot; {{ $user->phone }}@endif</p>
                  <a href="{{ route('account-setting') }}" class="edit font-body--lg-500">Edit Profile</a>
                </div>
              </div>

              <!-- Metrics -->
              <div class="row" style="margin-bottom:8px;">
                @foreach ([
                    ['label' => 'Total Orders', 'value' => $metrics['total']],
                    ['label' => 'Active Orders', 'value' => $metrics['active']],
                    ['label' => 'Delivered', 'value' => $metrics['delivered']],
                    ['label' => 'Cancelled', 'value' => $metrics['cancelled']],
                    ['label' => 'Saved Addresses', 'value' => $metrics['addresses']],
                ] as $metric)
                  <div class="col-lg-4 col-md-6" style="margin-bottom:16px;">
                    <div style="border:1px solid #e5e5e5;border-radius:8px;padding:20px;">
                      <p class="font-body--xxxl-600" style="margin-bottom:4px;">{{ $metric['value'] }}</p>
                      <p class="font-body--md-400" style="color:#666666;">{{ $metric['label'] }}</p>
                    </div>
                  </div>
                @endforeach
              </div>

              <!-- Recent orders -->
              <div class="dashboard__order-history" style="margin-top: 24px">
                <div class="dashboard__order-history-title">
                  <h2 class="font-body--xxl-500">Recent Orders</h2>
                  <a href="{{ route('order-history') }}" class="font-body--lg-500">View All</a>
                </div>
                <div class="dashboard__order-history-table">
                  <div class="table-responsive">
                    <table class="table">
                      <thead>
                        <tr>
                          <th scope="col" class="dashboard__order-history-table-title">Order</th>
                          <th scope="col" class="dashboard__order-history-table-title">Date</th>
                          <th scope="col" class="dashboard__order-history-table-title">Total</th>
                          <th scope="col" class="dashboard__order-history-table-title">Status</th>
                          <th scope="col" class="dashboard__order-history-table-title"></th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse ($recentOrders as $order)
                          <tr>
                            <td class="dashboard__order-history-table-item order-id">#{{ $order->order_number }}</td>
                            <td class="dashboard__order-history-table-item order-date">{{ $order->created_at->format('d M Y') }}</td>
                            <td class="dashboard__order-history-table-item">₹{{ number_format($order->grand_total, 2) }}</td>
                            <td class="dashboard__order-history-table-item order-status" style="text-transform:capitalize;">{{ $order->order_status }}</td>
                            <td class="dashboard__order-history-table-item">
                              <a href="{{ route('orders.show', $order) }}">View Details</a>
                            </td>
                          </tr>
                        @empty
                          <tr>
                            <td colspan="5" style="text-align:center;padding:32px 0;">
                              <p class="font-body--md-400" style="margin-bottom:12px;">You haven't placed any orders yet.</p>
                              <a href="{{ route('shop') }}" class="button button--md">Start Shopping</a>
                            </td>
                          </tr>
                        @endforelse
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
