<x-admin-layout title="Dashboard">
    <main class="pc-container-edit admin-dashboard">

        <x-admin.page-header title="Dashboard" subtitle="Overview of your store's performance" />

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

        <x-admin.table-card title="Recent Orders">
            <x-slot:head>
                <th>Order No.</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Order Status</th>
                <th>Date</th>
            </x-slot:head>

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
                        <x-admin.status-badge :status="$order->payment_status" />
                    </td>

                    <td>
                        <x-admin.status-badge :status="$order->order_status" />
                    </td>

                    <td>{{ $order->created_at->format('d M Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <x-admin.empty-state>No orders yet.</x-admin.empty-state>
                    </td>
                </tr>
            @endforelse
        </x-admin.table-card>

    </main>
</x-admin-layout>
