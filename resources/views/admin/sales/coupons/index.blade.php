<x-admin-layout title="Coupons">
    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Coupons</h4>
                <p class="text-muted mb-0">Manage discount coupons and promotional offers</p>
            </div>

            <a href="{{ route('admin.sales.coupons.create') }}" class="btn btn-primary">
                <i class="ph ph-plus me-1"></i>
                Add Coupon
            </a>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>Sales</span>
            <span class="mx-2">›</span>
            <span>Coupons</span>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Coupon List</h5>
            </div>

            <div class="card-body">

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form method="GET" action="{{ route('admin.sales.coupons.index') }}">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <input type="text"
                                   name="search"
                                   class="form-control"
                                   value="{{ request('search') }}"
                                   placeholder="Search coupon code"
                                   onchange="this.form.submit()">
                        </div>

                        <div class="col-md-3">
                            <select name="type" class="form-select" onchange="this.form.submit()">
                                <option value="">All Types</option>
                                <option value="percentage" @selected(request('type') === 'percentage')>Percentage</option>
                                <option value="fixed" @selected(request('type') === 'fixed')>Fixed Amount</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="active" @selected(request('status') === 'active')>Active</option>
                                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                                <option value="expired" @selected(request('status') === 'expired')>Expired</option>
                            </select>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Code</th>
                                <th>Type</th>
                                <th>Discount</th>
                                <th>Min Order</th>
                                <th>Usage</th>
                                <th>Validity</th>
                                <th>Status</th>
                                <th width="130">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($coupons as $coupon)
                                @php $isExpired = $coupon->end_date->isPast(); @endphp
                                <tr>
                                    <td>{{ $coupon->id }}</td>
                                    <td><span class="badge bg-light text-dark">{{ $coupon->code }}</span></td>
                                    <td>{{ $coupon->type === 'percentage' ? 'Percentage' : 'Fixed Amount' }}</td>
                                    <td>{{ $coupon->type === 'percentage' ? number_format((float) $coupon->value, 2).'%' : '₹'.number_format((float) $coupon->value, 2) }}</td>
                                    <td>₹{{ number_format((float) $coupon->minimum_order_amount, 2) }}</td>
                                    <td>{{ $coupon->used_count }} / {{ $coupon->usage_limit ?? 'Unlimited' }}</td>
                                    <td>{{ $coupon->start_date->format('d M Y') }} - {{ $coupon->end_date->format('d M Y') }}</td>
                                    <td>
                                        @if ($isExpired)
                                            <span class="badge bg-danger">Expired</span>
                                        @elseif ($coupon->status === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.sales.coupons.edit', $coupon) }}" class="btn btn-sm btn-info" title="Edit Coupon">
                                            <i class="ph ph-pencil-simple"></i>
                                        </a>

                                        <form action="{{ route('admin.sales.coupons.destroy', $coupon) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this coupon?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete Coupon">
                                                <i class="ph ph-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No coupons found.</td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                <div class="d-flex justify-content-end">
                    {{ $coupons->links() }}
                </div>
            </div>
        </div>

    </main>
</x-admin-layout>