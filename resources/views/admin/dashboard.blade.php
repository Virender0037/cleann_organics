<x-admin-layout title="Dashboard">
    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Dashboard</h4>
                <p class="text-muted mb-0">Overview of your store's performance</p>
            </div>
        </div>

        <div class="row mb-4">

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-1">Total Revenue</p>
                        <h4 class="mb-0">₹{{ number_format((float) $stats['total_revenue'], 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-1">Total Orders</p>
                        <h4 class="mb-0">{{ $stats['total_orders'] }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-1">Pending Orders</p>
                        <h4 class="mb-0 text-warning">{{ $stats['pending_orders'] }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-1">Total Customers</p>
                        <h4 class="mb-0">{{ $stats['total_customers'] }}</h4>
                    </div>
                </div>
            </div>

        </div>

        <div class="row mb-4">

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-1">Total Products</p>
                        <h4 class="mb-0">{{ $stats['total_products'] }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-1">Low Stock Variants</p>
                        <h4 class="mb-0 text-warning">{{ $stats['low_stock_count'] }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-1">Out of Stock Variants</p>
                        <h4 class="mb-0 text-danger">{{ $stats['out_of_stock_count'] }}</h4>
                    </div>
                </div>
            </div>

        </div>

        <div class="card">
            <div class="card-header">
                <h5>Recent Orders</h5>
            </div>

            <div class="card-body table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Order No.</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Order Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($recentOrders as $order)
                            <tr>
                                <td><strong>#{{ $order->order_number }}</strong></td>

                                <td>
                                    {{ $order->user->name ?? '—' }}
                                    <br>
                                    <small class="text-muted">{{ $order->user->email ?? '' }}</small>
                                </td>

                                <td>₹{{ number_format((float) $order->grand_total, 2) }}</td>

                                <td>
                                    @php
                                        $paymentBadge = match ($order->payment_status) {
                                            'paid' => 'bg-success',
                                            'failed' => 'bg-danger',
                                            'refunded' => 'bg-secondary',
                                            default => 'bg-warning',
                                        };
                                    @endphp
                                    <span class="badge {{ $paymentBadge }}">{{ ucfirst($order->payment_status) }}</span>
                                </td>

                                <td>
                                    @php
                                        $statusBadge = match ($order->order_status) {
                                            'delivered' => 'bg-success',
                                            'cancelled' => 'bg-danger',
                                            'pending' => 'bg-warning',
                                            default => 'bg-primary',
                                        };
                                    @endphp
                                    <span class="badge {{ $statusBadge }}">{{ ucfirst($order->order_status) }}</span>
                                </td>

                                <td>{{ $order->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No orders yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</x-admin-layout>
