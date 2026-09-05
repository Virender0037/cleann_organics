<x-admin-layout title="Orders Report">
<main class="pc-container-edit admin-content-padded">

    <x-admin.page-header title="Orders Report" subtitle="Order volume, fulfilment progress and status breakdown">
        <x-slot:actions>
            <a href="{{ route('admin.reports.orders.export', request()->query()) }}" class="btn btn-light-secondary">
                <i class="ph ph-download-simple me-1"></i>Export
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[['label' => 'Reports'], ['label' => 'Orders Report']]" />

    @include('admin.partials.alerts')

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-3"><div class="card"><div class="card-body">
            <p class="text-muted mb-1">Total Orders</p>
            <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
        </div></div></div>

        <div class="col-md-3"><div class="card"><div class="card-body">
            <p class="text-muted mb-1">Delivered</p>
            <h4 class="mb-0 text-success">{{ number_format($stats['delivered']) }}</h4>
        </div></div></div>

        <div class="col-md-3"><div class="card"><div class="card-body">
            <p class="text-muted mb-1">In Progress</p>
            <h4 class="mb-0 text-warning">{{ number_format($stats['in_progress']) }}</h4>
            <small class="text-muted">Not delivered, not cancelled</small>
        </div></div></div>

        <div class="col-md-3"><div class="card"><div class="card-body">
            <p class="text-muted mb-1">Cancelled</p>
            <h4 class="mb-0 text-danger">{{ number_format($stats['cancelled']) }}</h4>
        </div></div></div>
    </div>

    <x-admin.table-card title="Orders">
        <x-slot:toolbar>
            <x-admin.filter-toolbar action="{{ route('admin.reports.orders.index') }}">
                <div class="col-md-3">
                    <input name="search" class="form-control" value="{{ request('search') }}"
                           placeholder="Order # / Customer / Email">
                </div>

                <div class="col-md-2">
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>

                <div class="col-md-2">
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>

                <div class="col-md-2">
                    <select name="order_status" class="form-select">
                        <option value="">All Order Status</option>
                        @foreach ($orderStatuses as $status)
                            <option value="{{ $status }}" @selected(request('order_status') === $status)>
                                {{ ucwords(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="payment_status" class="form-select">
                        <option value="">All Payment Status</option>
                        @foreach ($paymentStatuses as $status)
                            <option value="{{ $status }}" @selected(request('payment_status') === $status)>
                                {{ ucwords(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="payment_method" class="form-select">
                        <option value="">All Methods</option>
                        @foreach ($paymentMethods as $method)
                            <option value="{{ $method }}" @selected(request('payment_method') === $method)>
                                {{ ucwords(str_replace('_', ' ', $method)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <x-slot:submit>
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </x-slot:submit>
            </x-admin.filter-toolbar>
        </x-slot:toolbar>

        <x-slot:head>
            <th>Order #</th>
            <th>Customer</th>
            <th>Date</th>
            <th>Payment Method</th>
            <th>Payment Status</th>
            <th>Order Status</th>
            <th class="text-end">Grand Total</th>
            <th></th>
        </x-slot:head>

        @forelse ($orders as $order)
            <tr>
                <td>#{{ $order->order_number }}</td>
                <td>
                    {{ $order->user->name ?? '—' }}
                    <div class="text-muted small">{{ $order->user->email ?? '' }}</div>
                </td>
                <td>{{ $order->created_at->format('d M Y') }}</td>
                <td>{{ ucwords(str_replace('_', ' ', (string) $order->payment_method)) }}</td>
                <td><x-admin.status-badge :status="$order->payment_status" /></td>
                <td><x-admin.status-badge :status="$order->order_status" /></td>
                <td class="text-end">₹{{ number_format((float) $order->grand_total, 2) }}</td>
                <td>
                    <a href="{{ route('admin.sales.orders.show', $order) }}" class="btn btn-sm btn-light-primary">View</a>
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
