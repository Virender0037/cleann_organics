<x-admin-layout title="Orders">
    <main class="pc-container-edit">

        <x-admin.page-header title="Orders" subtitle="Manage customer orders and order status">
            <x-slot:actions>
                <a href="{{ route('admin.sales.orders.export', request()->query()) }}" class="btn btn-light-secondary">
                    <i class="ph ph-download-simple me-1"></i>
                    Export
                </a>
            </x-slot:actions>
        </x-admin.page-header>

        <x-admin.breadcrumb :items="[['label' => 'Sales'], ['label' => 'Orders']]" />

        @include('admin.partials.alerts')

        <x-admin.table-card title="Order List">
            <x-slot:toolbar>
                <x-admin.filter-toolbar action="{{ route('admin.sales.orders.index') }}">
                    <div class="col-md-3">
                        <input type="text"
                               name="search"
                               class="form-control"
                               value="{{ request('search') }}"
                               placeholder="Search order number or customer">
                    </div>

                    <div class="col-md-2">
                        <select name="order_status" class="form-select">
                            <option value="">All Order Status</option>
                            @foreach (['pending' => 'Pending', 'confirmed' => 'Confirmed', 'packed' => 'Packed', 'shipped' => 'Shipped', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'] as $value => $label)
                                <option value="{{ $value }}" @selected(request('order_status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select name="payment_status" class="form-select">
                            <option value="">All Payment Status</option>
                            @foreach (['pending' => 'Pending', 'paid' => 'Paid', 'failed' => 'Failed', 'refunded' => 'Refunded'] as $value => $label)
                                <option value="{{ $value }}" @selected(request('payment_status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <input type="date" name="from" class="form-control" value="{{ request('from') }}" title="From date">
                    </div>

                    <div class="col-md-2">
                        <input type="date" name="to" class="form-control" value="{{ request('to') }}" title="To date">
                    </div>

                    <x-slot:submit>
                        <button type="submit" class="btn btn-primary w-100">Go</button>
                    </x-slot:submit>
                </x-admin.filter-toolbar>
            </x-slot:toolbar>

            <x-slot:head>
                <th>#</th>
                <th>Order No.</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Order Status</th>
                <th>Date</th>
                <th width="140">Action</th>
            </x-slot:head>

            @forelse ($orders as $order)
                <tr>
                    <td>{{ $order->id }}</td>

                    <td>
                        <strong>#{{ $order->order_number }}</strong>
                    </td>

                    <td>
                        {{ $order->user->name ?? '—' }}
                        <br>
                        <small class="text-muted">{{ $order->user->email ?? '' }}</small>
                    </td>

                    <td>₹{{ number_format((float) $order->grand_total, 2) }}</td>

                    <td>
                        <x-admin.status-badge :status="$order->payment_status" />
                        <br>
                        <small class="text-muted">{{ strtoupper($order->payment_method) }}</small>
                    </td>

                    <td>
                        <x-admin.status-badge :status="$order->order_status" />
                    </td>

                    <td>{{ $order->created_at->format('d M Y') }}</td>

                    <td>
                        <a href="{{ route('admin.sales.orders.show', $order) }}"
                        class="btn btn-sm btn-info"
                        title="View Order">
                            <i class="ph ph-eye"></i>
                        </a>

                        <button class="btn btn-sm btn-success"
                                title="Print Invoice" disabled>
                            <i class="ph ph-printer"></i>
                        </button>

                        <button class="btn btn-sm btn-warning"
                                title="Update Status" disabled>
                            <i class="ph ph-arrows-clockwise"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">
                        <x-admin.empty-state>No orders found.</x-admin.empty-state>
                    </td>
                </tr>
            @endforelse

            <x-slot:pagination>
                {{ $orders->links() }}
            </x-slot:pagination>
        </x-admin.table-card>

    </main>
</x-admin-layout>
