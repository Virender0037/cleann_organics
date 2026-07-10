<x-admin-layout title="Returns">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Returns</h4>
                <p class="text-muted mb-0">Manage customer return requests and refunds</p>
            </div>

            <a href="#" class="btn btn-light-secondary">
                <i class="ph ph-download-simple me-1"></i>
                Export
            </a>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>Sales</span>
            <span class="mx-2">›</span>
            <span>Returns</span>
        </div>

        <div class="card">

            <div class="card-header">
                <h5>Return Requests</h5>
            </div>

            <div class="card-body">

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form method="GET" action="{{ route('admin.sales.returns.index') }}">
                    <div class="row mb-4">

                        <div class="col-md-4">
                            <input name="search"
                                   class="form-control"
                                   value="{{ request('search') }}"
                                   placeholder="Search Order / Return ID / Customer">
                        </div>

                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                @foreach (['requested' => 'Requested', 'approved' => 'Approved', 'rejected' => 'Rejected', 'picked_up' => 'Picked Up', 'received' => 'Received', 'refunded' => 'Refunded'] as $value => $label)
                                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Search</button>
                        </div>

                    </div>
                </form>

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>

                        <tr>

                            <th>#</th>

                            <th>Return ID</th>

                            <th>Order</th>

                            <th>Customer</th>

                            <th>Reason</th>

                            <th>Refund</th>

                            <th>Status</th>

                            <th>Date</th>

                            <th width="130">Action</th>

                        </tr>

                        </thead>

                        <tbody>

                        @forelse ($returns as $return)
                            <tr>

                                <td>{{ $return->id }}</td>

                                <td>#{{ $return->return_number }}</td>

                                <td>#{{ $return->order->order_number ?? '—' }}</td>

                                <td>{{ $return->user->name ?? '—' }}</td>

                                <td>{{ \Illuminate\Support\Str::limit($return->reason, 40) }}</td>

                                <td>₹{{ number_format((float) $return->refund_amount, 2) }}</td>

                                <td>
                                    @php
                                        $statusBadge = match ($return->status) {
                                            'approved' => 'bg-primary',
                                            'rejected' => 'bg-danger',
                                            'picked_up', 'received' => 'bg-info',
                                            'refunded' => 'bg-success',
                                            default => 'bg-warning',
                                        };
                                    @endphp
                                    <span class="badge {{ $statusBadge }}">
                                        {{ ucwords(str_replace('_', ' ', $return->status)) }}
                                    </span>
                                </td>

                                <td>{{ $return->created_at->format('d M Y') }}</td>

                                <td>

                                    <a href="{{ route('admin.sales.returns.show', $return) }}"
                                       class="btn btn-sm btn-info">
                                        <i class="ph ph-eye"></i>
                                    </a>

                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">No return requests found.</td>
                            </tr>
                        @endforelse

                        </tbody>

                    </table>

                </div>

                <div class="d-flex justify-content-end">
                    {{ $returns->links() }}
                </div>

            </div>

        </div>

    </main>

</x-admin-layout>