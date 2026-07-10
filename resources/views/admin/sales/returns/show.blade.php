<x-admin-layout title="Return Details">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Return #{{ $return->return_number }}</h4>
                <p class="text-muted mb-0">Review return request, refund details and activity</p>
            </div>

            <div>
                @if ($return->status === 'requested')
                    <form action="{{ route('admin.sales.returns.status', $return) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="approved">
                        <button type="submit" class="btn btn-success me-2">
                            <i class="ph ph-check me-1"></i>
                            Approve
                        </button>
                    </form>

                    <form action="{{ route('admin.sales.returns.status', $return) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="rejected">
                        <button type="submit" class="btn btn-danger me-2">
                            <i class="ph ph-x me-1"></i>
                            Reject
                        </button>
                    </form>
                @endif

                <a href="{{ route('admin.sales.returns.index') }}" class="btn btn-light">
                    <i class="ph ph-arrow-left me-1"></i>
                    Back
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>Sales</span>
            <span class="mx-2">›</span>
            <a href="{{ route('admin.sales.returns.index') }}">Returns</a>
            <span class="mx-2">›</span>
            <span>Return Details</span>
        </div>

        <div class="row">

            <div class="col-lg-8">

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Returned Products</h5>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Variant</th>
                                        <th>SKU</th>
                                        <th>Qty</th>
                                        <th>Refund Amount</th>
                                        <th>Item Reason</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse ($return->items as $item)
                                        <tr>
                                            <td><strong>{{ $item->orderItem->product_name ?? '—' }}</strong></td>
                                            <td>{{ $item->orderItem->variant_size ?? $item->orderItem->variant_color ?? $item->orderItem->variant_pack_quantity ?? '—' }}</td>
                                            <td>{{ $item->orderItem->variant_sku ?? '—' }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>₹{{ number_format((float) $item->refund_amount, 2) }}</td>
                                            <td>{{ $item->reason ?? '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No items on this return.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Return Reason</h5>
                    </div>

                    <div class="card-body">
                        <p class="mb-0">{{ $return->reason }}</p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Uploaded Proof Images</h5>
                    </div>

                    <div class="card-body">
                        <p class="text-muted mb-0">Not available — this system doesn't store proof-image uploads for returns yet.</p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Return Timeline</h5>
                    </div>

                    <div class="card-body">
                        @if ($return->status === 'rejected')
                            <p class="text-danger mb-0"><strong>Return Rejected</strong></p>
                        @else
                            @php
                                $reachedPickedUp = in_array($return->status, ['picked_up', 'received', 'refunded']);
                                $reachedReceived = in_array($return->status, ['received', 'refunded']);
                            @endphp
                            <div class="d-flex justify-content-between text-center">
                                <div>
                                    <span class="badge bg-success rounded-pill mb-2">✓</span>
                                    <p class="mb-0 fw-bold">Requested</p>
                                    <small>{{ $return->created_at->format('d M Y') }}</small>
                                </div>

                                <div>
                                    <span class="badge {{ $return->approved_at ? 'bg-success' : 'bg-light text-dark' }} rounded-pill mb-2">
                                        {{ $return->approved_at ? '✓' : '○' }}
                                    </span>
                                    <p class="mb-0 fw-bold">Approved</p>
                                    <small>{{ $return->approved_at ? $return->approved_at->format('d M Y') : 'Pending' }}</small>
                                </div>

                                <div>
                                    <span class="badge {{ $reachedPickedUp ? 'bg-success' : 'bg-light text-dark' }} rounded-pill mb-2">
                                        {{ $reachedPickedUp ? '✓' : '○' }}
                                    </span>
                                    <p class="mb-0 fw-bold">Picked Up</p>
                                    <small>{{ $reachedPickedUp ? 'Done' : 'Pending' }}</small>
                                </div>

                                <div>
                                    <span class="badge {{ $reachedReceived ? 'bg-success' : 'bg-light text-dark' }} rounded-pill mb-2">
                                        {{ $reachedReceived ? '✓' : '○' }}
                                    </span>
                                    <p class="mb-0 fw-bold">Received</p>
                                    <small>{{ $reachedReceived ? 'Done' : 'Pending' }}</small>
                                </div>

                                <div>
                                    <span class="badge {{ $return->refunded_at ? 'bg-success' : 'bg-light text-dark' }} rounded-pill mb-2">
                                        {{ $return->refunded_at ? '✓' : '○' }}
                                    </span>
                                    <p class="mb-0 fw-bold">Refunded</p>
                                    <small>{{ $return->refunded_at ? $return->refunded_at->format('d M Y') : 'Pending' }}</small>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Internal Notes</h5>
                    </div>

                    <div class="card-body">
                        @if ($return->admin_note)
                            <p class="mb-0">{{ $return->admin_note }}</p>
                        @else
                            <p class="text-muted mb-0">No notes on this return.</p>
                        @endif
                    </div>
                </div>

            </div>

            <div class="col-lg-4">

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Return Summary</h5>
                    </div>

                    <div class="card-body">
                        <p class="mb-2"><strong>Return ID:</strong> #{{ $return->return_number }}</p>
                        <p class="mb-2"><strong>Order ID:</strong> #{{ $return->order->order_number ?? '—' }}</p>
                        <p class="mb-2">
                            <strong>Status:</strong>
                            @php
                                $summaryBadge = match ($return->status) {
                                    'approved' => 'bg-primary',
                                    'rejected' => 'bg-danger',
                                    'picked_up', 'received' => 'bg-info',
                                    'refunded' => 'bg-success',
                                    default => 'bg-warning text-dark',
                                };
                            @endphp
                            <span class="badge {{ $summaryBadge }}">{{ ucwords(str_replace('_', ' ', $return->status)) }}</span>
                        </p>
                        <p class="mb-2"><strong>Requested Date:</strong> {{ $return->created_at->format('d M Y') }}</p>
                        <p class="mb-0"><strong>Refund Amount:</strong> ₹{{ number_format((float) $return->refund_amount, 2) }}</p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Customer</h5>
                    </div>

                    <div class="card-body">
                        <p class="mb-1"><strong>{{ $return->user->name ?? '—' }}</strong></p>
                        <p class="mb-1">{{ $return->user->email ?? '—' }}</p>
                        <p class="mb-0">{{ $return->user->phone ?? '—' }}</p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Refund Details</h5>
                    </div>

                    <div class="card-body">
                        <p class="mb-2"><strong>Refund Amount:</strong> ₹{{ number_format((float) $return->refund_amount, 2) }}</p>
                        <p class="mb-0">
                            <strong>Refunded At:</strong>
                            {{ $return->refunded_at ? $return->refunded_at->format('d M Y, h:i A') : 'Not yet refunded' }}
                        </p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Pickup Details</h5>
                    </div>

                    <div class="card-body">
                        <p class="text-muted mb-0">Not available — this system doesn't track courier/pickup details for returns yet.</p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Activity Log</h5>
                    </div>

                    <div class="card-body">
                        <p class="text-muted mb-0">Not available — no activity log is recorded for returns in this system yet.</p>
                    </div>
                </div>

            </div>

        </div>

    </main>

</x-admin-layout>