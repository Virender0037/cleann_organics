<x-admin-layout title="Orders">
    <main class="pc-container-edit">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Orders</h4>
            <p class="text-muted mb-0">Manage customer orders and order status</p>
        </div>

        <div>
            <a href="#" class="btn btn-light-secondary">
                <i class="ph ph-download-simple me-1"></i>
                Export
            </a>
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <span class="mx-2">›</span>
        <span>Sales</span>
        <span class="mx-2">›</span>
        <span>Orders</span>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Order List</h5>
        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('admin.sales.orders.index') }}">
                <div class="row mb-3">
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

                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">Go</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Order No.</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Order Status</th>
                            <th>Date</th>
                            <th width="140">Action</th>
                        </tr>
                    </thead>

                    <tbody>
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
                                    @php
                                        $paymentBadge = match ($order->payment_status) {
                                            'paid' => 'bg-success',
                                            'failed' => 'bg-danger',
                                            'refunded' => 'bg-secondary',
                                            default => 'bg-warning',
                                        };
                                    @endphp
                                    <span class="badge {{ $paymentBadge }}">{{ ucfirst($order->payment_status) }}</span>
                                    <br>
                                    <small class="text-muted">{{ strtoupper($order->payment_method) }}</small>
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
                                <td colspan="8" class="text-center text-muted">No orders found.</td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $orders->links() }}
            </div>

        </div>
    </div>
    </main>
</x-admin-layout>
