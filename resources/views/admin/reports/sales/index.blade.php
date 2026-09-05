<x-admin-layout title="Sales Report">
<main class="pc-container-edit admin-content-padded">

    <x-admin.page-header title="Sales Report" subtitle="Paid revenue, order value and refunds for the selected period">
        <x-slot:actions>
            <a href="{{ route('admin.reports.sales.export', request()->query()) }}" class="btn btn-light-secondary">
                <i class="ph ph-download-simple me-1"></i>Export
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[['label' => 'Reports'], ['label' => 'Sales Report']]" />

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

    <x-admin.filter-toolbar action="{{ route('admin.reports.sales.index') }}">
        <div class="col-md-3">
            <label class="form-label">From</label>
            <input type="date" name="from" class="form-control" value="{{ request('from') }}">
        </div>

        <div class="col-md-3">
            <label class="form-label">To</label>
            <input type="date" name="to" class="form-control" value="{{ request('to') }}">
        </div>

        <div class="col-md-3">
            <label class="form-label">Payment Method</label>
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
            <label class="form-label d-none d-md-block">&nbsp;</label>
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </x-slot:submit>
    </x-admin.filter-toolbar>

    <p class="text-muted">
        Period:
        {{ $from ? $from->format('d M Y') : 'Beginning' }}
        &ndash;
        {{ $to ? $to->format('d M Y') : 'Today' }}
    </p>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card"><div class="card-body">
            <p class="text-muted mb-1">Placed Order Value</p>
            <h4 class="mb-0">₹{{ number_format($metrics['placed_order_value'], 2) }}</h4>
            <small class="text-muted">Non-cancelled orders (operational)</small>
        </div></div></div>

        <div class="col-md-3"><div class="card"><div class="card-body">
            <p class="text-muted mb-1">Paid Revenue</p>
            <h4 class="mb-0 text-success">₹{{ number_format($metrics['paid_revenue'], 2) }}</h4>
            <small class="text-muted">Orders with payment status paid</small>
        </div></div></div>

        <div class="col-md-3"><div class="card"><div class="card-body">
            <p class="text-muted mb-1">Paid Orders</p>
            <h4 class="mb-0">{{ number_format($metrics['paid_orders']) }}</h4>
        </div></div></div>

        <div class="col-md-3"><div class="card"><div class="card-body">
            <p class="text-muted mb-1">Average Order Value</p>
            <h4 class="mb-0">₹{{ number_format($metrics['average_order_value'], 2) }}</h4>
            <small class="text-muted">Paid revenue / paid orders</small>
        </div></div></div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card"><div class="card-body">
            <p class="text-muted mb-1">Refunded Value</p>
            <h4 class="mb-0 text-danger">₹{{ number_format($metrics['refunded_value'], 2) }}</h4>
            <small class="text-muted">Orders with payment status refunded</small>
        </div></div></div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h5>Paid Revenue Breakdown</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <tbody>
                        <tr><td>Subtotal</td><td class="text-end">₹{{ number_format($breakdown['subtotal'], 2) }}</td></tr>
                        <tr><td>Discount</td><td class="text-end">&minus;₹{{ number_format($breakdown['discount_amount'], 2) }}</td></tr>
                        <tr><td>Shipping</td><td class="text-end">₹{{ number_format($breakdown['shipping_amount'], 2) }}</td></tr>
                        <tr><td>Tax</td><td class="text-end">₹{{ number_format($breakdown['tax_amount'], 2) }}</td></tr>
                        <tr class="fw-bold"><td>Grand Total (Paid Revenue)</td><td class="text-end">₹{{ number_format($breakdown['grand_total'], 2) }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-admin.table-card title="Daily Paid Sales">
        <x-slot:head>
            <th>Date</th>
            <th class="text-end">Orders</th>
            <th class="text-end">Subtotal</th>
            <th class="text-end">Discount</th>
            <th class="text-end">Shipping</th>
            <th class="text-end">Tax</th>
            <th class="text-end">Paid Revenue</th>
        </x-slot:head>

        @forelse ($daily as $row)
            <tr>
                <td>{{ \Illuminate\Support\Carbon::parse($row->day)->format('d M Y') }}</td>
                <td class="text-end">{{ number_format((int) $row->orders) }}</td>
                <td class="text-end">₹{{ number_format((float) $row->subtotal, 2) }}</td>
                <td class="text-end">₹{{ number_format((float) $row->discount_amount, 2) }}</td>
                <td class="text-end">₹{{ number_format((float) $row->shipping_amount, 2) }}</td>
                <td class="text-end">₹{{ number_format((float) $row->tax_amount, 2) }}</td>
                <td class="text-end">₹{{ number_format((float) $row->grand_total, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7">
                    <x-admin.empty-state>No paid sales in this period.</x-admin.empty-state>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $daily->links() }}
        </x-slot:pagination>
    </x-admin.table-card>

</main>
</x-admin-layout>
