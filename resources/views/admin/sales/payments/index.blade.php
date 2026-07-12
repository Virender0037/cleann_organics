<x-admin-layout title="Payments">

    <main class="pc-container-edit">

        <x-admin.page-header title="Payments" subtitle="Manage customer payment transactions">
            <x-slot:actions>
                <a href="{{ route('admin.sales.payments.export', request()->query()) }}" class="btn btn-light-secondary">
                    <i class="ph ph-download-simple me-1"></i>
                    Export Payments
                </a>
            </x-slot:actions>
        </x-admin.page-header>

        <x-admin.breadcrumb :items="[['label' => 'Sales'], ['label' => 'Payments']]" />

        @include('admin.partials.alerts')

        <x-admin.table-card title="Payment Transactions">
            <x-slot:toolbar>
                <x-admin.filter-toolbar action="{{ route('admin.sales.payments.index') }}">
                    <div class="col-md-4">
                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            value="{{ request('search') }}"
                            placeholder="Search Order / Customer / Transaction">
                    </div>

                    <div class="col-md-3">
                        <select name="payment_method" class="form-select">
                            <option value="">All Payment Methods</option>
                            <option value="upi" @selected(request('payment_method') === 'upi')>UPI</option>
                            <option value="cod" @selected(request('payment_method') === 'cod')>COD</option>
                            <option value="bank_transfer" @selected(request('payment_method') === 'bank_transfer')>Bank Transfer</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                            <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                            <option value="refunded" @selected(request('status') === 'refunded')>Refunded</option>
                        </select>
                    </div>

                    <x-slot:submit>
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </x-slot:submit>
                </x-admin.filter-toolbar>
            </x-slot:toolbar>

            <x-slot:head>
                <th>#</th>
                <th>Order</th>
                <th>Customer</th>
                <th>Transaction ID</th>
                <th>Method</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Paid On</th>
                <th width="120">Action</th>
            </x-slot:head>

            @forelse ($payments as $payment)
                <tr>

                    <td>{{ $payment->id }}</td>

                    <td>
                        <strong>#{{ $payment->order->order_number ?? '—' }}</strong>
                    </td>

                    <td>
                        {{ $payment->order->user->name ?? '—' }}
                        <br>
                        <small class="text-muted">
                            {{ $payment->order->user->email ?? '' }}
                        </small>
                    </td>

                    <td>
                        {{ $payment->transaction_id ?? '—' }}
                    </td>

                    <td>
                        <span class="badge bg-info text-dark">
                            {{ strtoupper($payment->payment_method) }}
                        </span>
                    </td>

                    <td>
                        ₹{{ number_format((float) $payment->amount, 2) }}
                    </td>

                    <td>
                        <x-admin.status-badge :status="$payment->status" />
                    </td>

                    <td>
                        @if ($payment->paid_at)
                            {{ $payment->paid_at->format('d M Y') }}
                            <br>
                            <small class="text-muted">{{ $payment->paid_at->format('h:i A') }}</small>
                        @else
                            --
                        @endif
                    </td>

                    <td>

                        <a href="{{ route('admin.sales.payments.show', $payment) }}"
                        class="btn btn-info btn-sm"
                        title="View Payment">
                            <i class="ph ph-eye"></i>
                        </a>

                        <button class="btn btn-success btn-sm" title="Download Receipt" disabled>
                            <i class="ph ph-download-simple"></i>
                        </button>

                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="9">
                        <x-admin.empty-state>No payments found.</x-admin.empty-state>
                    </td>
                </tr>
            @endforelse

            <x-slot:pagination>
                {{ $payments->links() }}
            </x-slot:pagination>
        </x-admin.table-card>

    </main>

</x-admin-layout>
