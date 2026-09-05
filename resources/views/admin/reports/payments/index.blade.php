<x-admin-layout title="Payments Report">
<main class="pc-container-edit admin-content-padded">

    <x-admin.page-header title="Payments Report" subtitle="Payment transactions and collection totals for the selected period">
        <x-slot:actions>
            <a href="{{ route('admin.reports.payments.export', request()->query()) }}" class="btn btn-light-secondary">
                <i class="ph ph-download-simple me-1"></i>Export
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[['label' => 'Reports'], ['label' => 'Payments Report']]" />

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
            <p class="text-muted mb-1">Collected</p>
            <h4 class="mb-0 text-success">₹{{ number_format($metrics['collected'], 2) }}</h4>
            <small class="text-muted">Payments with status paid</small>
        </div></div></div>

        <div class="col-md-3"><div class="card"><div class="card-body">
            <p class="text-muted mb-1">Pending</p>
            <h4 class="mb-0 text-warning">₹{{ number_format($metrics['pending'], 2) }}</h4>
        </div></div></div>

        <div class="col-md-3"><div class="card"><div class="card-body">
            <p class="text-muted mb-1">Failed</p>
            <h4 class="mb-0 text-danger">₹{{ number_format($metrics['failed'], 2) }}</h4>
            <small class="text-muted">{{ number_format($metrics['failed_count']) }} transaction(s)</small>
        </div></div></div>

        <div class="col-md-3"><div class="card"><div class="card-body">
            <p class="text-muted mb-1">Refunded</p>
            <h4 class="mb-0 text-danger">₹{{ number_format($metrics['refunded'], 2) }}</h4>
        </div></div></div>
    </div>

    <x-admin.table-card title="Payment Transactions">
        <x-slot:toolbar>
            <x-admin.filter-toolbar action="{{ route('admin.reports.payments.index') }}">
                <div class="col-md-3">
                    <input name="search" class="form-control" value="{{ request('search') }}"
                           placeholder="Txn ID / Order # / Customer">
                </div>

                <div class="col-md-2">
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>

                <div class="col-md-2">
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>

                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>
                                {{ ucfirst($status) }}
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
            <th>Transaction ID</th>
            <th>Customer</th>
            <th>Method</th>
            <th class="text-end">Amount</th>
            <th>Status</th>
            <th>Paid At</th>
            <th>Date</th>
        </x-slot:head>

        @forelse ($payments as $payment)
            <tr>
                <td>#{{ $payment->order->order_number ?? '—' }}</td>
                <td>{{ $payment->transaction_id ?? '—' }}</td>
                <td>
                    {{ $payment->order->user->name ?? '—' }}
                    <div class="text-muted small">{{ $payment->order->user->email ?? '' }}</div>
                </td>
                <td>{{ ucwords(str_replace('_', ' ', (string) $payment->payment_method)) }}</td>
                <td class="text-end">₹{{ number_format((float) $payment->amount, 2) }}</td>
                <td><x-admin.status-badge :status="$payment->status" /></td>
                <td>{{ $payment->paid_at?->format('d M Y H:i') ?? '—' }}</td>
                <td>{{ $payment->created_at->format('d M Y') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8">
                    <x-admin.empty-state>No payments found for this period.</x-admin.empty-state>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $payments->links() }}
        </x-slot:pagination>
    </x-admin.table-card>

</main>
</x-admin-layout>
