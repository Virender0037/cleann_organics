<x-admin-layout title="Payments">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Payments</h4>
                <p class="text-muted mb-0">Manage customer payment transactions</p>
            </div>

            <div>
                <a href="#" class="btn btn-light-secondary">
                    <i class="ph ph-download-simple me-1"></i>
                    Export Payments
                </a>
            </div>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>Sales</span>
            <span class="mx-2">›</span>
            <span>Payments</span>
        </div>

        <div class="card">

            <div class="card-header">
                <h5>Payment Transactions</h5>
            </div>

            <div class="card-body">

                <form method="GET" action="{{ route('admin.sales.payments.index') }}">
                    <div class="row mb-4">

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

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                Search
                            </button>
                        </div>

                    </div>
                </form>

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>

                            <tr>
                                <th>#</th>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Transaction ID</th>
                                <th>Method</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Paid On</th>
                                <th width="120">Action</th>
                            </tr>

                        </thead>

                        <tbody>

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
                                        <span class="badge bg-info">
                                            {{ strtoupper($payment->payment_method) }}
                                        </span>
                                    </td>

                                    <td>
                                        ₹{{ number_format((float) $payment->amount, 2) }}
                                    </td>

                                    <td>
                                        @php
                                            $statusBadge = match ($payment->status) {
                                                'paid' => 'bg-success',
                                                'failed' => 'bg-danger',
                                                'refunded' => 'bg-secondary',
                                                default => 'bg-warning text-dark',
                                            };
                                        @endphp
                                        <span class="badge {{ $statusBadge }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
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
                                    <td colspan="9" class="text-center text-muted">No payments found.</td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

                <div class="d-flex justify-content-end">
                    {{ $payments->links() }}
                </div>

            </div>

        </div>

    </main>

</x-admin-layout>