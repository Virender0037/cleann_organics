<x-admin-layout title="Payment Details">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Payment Details</h4>
                <p class="text-muted mb-0">View complete transaction information</p>
            </div>

            <a href="{{ route('admin.sales.payments.index') }}" class="btn btn-light">
                <i class="ph ph-arrow-left me-1"></i>
                Back
            </a>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>Sales</span>
            <span class="mx-2">›</span>
            <a href="{{ route('admin.sales.payments.index') }}">Payments</a>
            <span class="mx-2">›</span>
            <span>Payment Details</span>
        </div>

        <div class="row">

            <div class="col-lg-8">

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Transaction Information</h5>
                    </div>

                    <div class="card-body">
                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label class="text-muted">Transaction ID</label>
                                <p class="fw-bold mb-0">{{ $payment->transaction_id ?? '—' }}</p>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="text-muted">Order Number</label>
                                <p class="fw-bold mb-0">#{{ $payment->order->order_number ?? '—' }}</p>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="text-muted">Payment Method</label>
                                <p class="mb-0">
                                    <span class="badge bg-info">{{ strtoupper($payment->payment_method) }}</span>
                                </p>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="text-muted">Payment Status</label>
                                <p class="mb-0">
                                    @php
                                        $statusBadge = match ($payment->status) {
                                            'paid' => 'bg-success',
                                            'failed' => 'bg-danger',
                                            'refunded' => 'bg-secondary',
                                            default => 'bg-warning text-dark',
                                        };
                                    @endphp
                                    <span class="badge {{ $statusBadge }}">{{ ucfirst($payment->status) }}</span>
                                </p>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="text-muted">Amount</label>
                                <p class="fw-bold mb-0">₹{{ number_format((float) $payment->amount, 2) }}</p>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="text-muted">Paid At</label>
                                <p class="mb-0">{{ $payment->paid_at ? $payment->paid_at->format('d M Y, h:i A') : '—' }}</p>
                            </div>

                            @if ($payment->admin_note)
                                <div class="col-md-12 mb-3">
                                    <label class="text-muted">Admin Note</label>
                                    <p class="mb-0">{{ $payment->admin_note }}</p>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Gateway Response</h5>
                    </div>

                    <div class="card-body">
                        <p class="text-muted mb-0">Not available — this system doesn't record raw gateway response payloads.</p>
                    </div>
                </div>

            </div>

            <div class="col-lg-4">

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Customer</h5>
                    </div>

                    <div class="card-body">
                        <p class="mb-1"><strong>{{ $payment->order->user->name ?? '—' }}</strong></p>
                        <p class="mb-1">{{ $payment->order->user->email ?? '—' }}</p>
                        <p class="mb-0">{{ $payment->order->user->phone ?? '—' }}</p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Order Summary</h5>
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Order Total</span>
                            <strong>₹{{ number_format((float) ($payment->order->grand_total ?? 0), 2) }}</strong>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Payment Recorded</span>
                            <strong class="text-success">₹{{ number_format((float) $payment->amount, 2) }}</strong>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span>Balance</span>
                            <strong>₹{{ number_format((float) (($payment->order->grand_total ?? 0) - $payment->amount), 2) }}</strong>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Actions</h5>
                    </div>

                    <div class="card-body d-grid gap-2">
                        <button class="btn btn-light-primary" disabled>
                            <i class="ph ph-download-simple me-1"></i>
                            Download Receipt
                        </button>

                        <button class="btn btn-light-secondary" disabled>
                            <i class="ph ph-printer me-1"></i>
                            Print Receipt
                        </button>

                        <button class="btn btn-light-danger" disabled>
                            <i class="ph ph-arrow-counter-clockwise me-1"></i>
                            Refund Payment
                        </button>
                    </div>
                </div>

            </div>

        </div>

    </main>

</x-admin-layout>