<x-admin-layout title="Coupons">
    <main class="pc-container-edit">

        <x-admin.page-header title="Coupons" subtitle="Manage discount coupons and promotional offers">
            <x-slot:actions>
                <a href="{{ route('admin.sales.coupons.create') }}" class="btn btn-primary">
                    <i class="ph ph-plus me-1"></i>
                    Add Coupon
                </a>
            </x-slot:actions>
        </x-admin.page-header>

        <x-admin.breadcrumb :items="[['label' => 'Sales'], ['label' => 'Coupons']]" />

        @include('admin.partials.alerts')

        <x-admin.table-card title="Coupon List">
            <x-slot:toolbar>
                <x-admin.filter-toolbar action="{{ route('admin.sales.coupons.index') }}">
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
                </x-admin.filter-toolbar>
            </x-slot:toolbar>

            <x-slot:head>
                <th>#</th>
                <th>Code</th>
                <th>Type</th>
                <th>Discount</th>
                <th>Min Order</th>
                <th>Usage</th>
                <th>Validity</th>
                <th>Status</th>
                <th width="130">Action</th>
            </x-slot:head>

            @forelse ($coupons as $coupon)
                @php
                    $isExpired = $coupon->end_date->isPast();
                    $displayStatus = $isExpired ? 'expired' : $coupon->status;
                @endphp
                <tr>
                    <td>{{ $coupon->id }}</td>
                    <td><span class="badge bg-light text-dark">{{ $coupon->code }}</span></td>
                    <td>{{ $coupon->type === 'percentage' ? 'Percentage' : 'Fixed Amount' }}</td>
                    <td>{{ $coupon->type === 'percentage' ? number_format((float) $coupon->value, 2).'%' : '₹'.number_format((float) $coupon->value, 2) }}</td>
                    <td>₹{{ number_format((float) $coupon->minimum_order_amount, 2) }}</td>
                    <td>{{ $coupon->used_count }} / {{ $coupon->usage_limit ?? 'Unlimited' }}</td>
                    <td>{{ $coupon->start_date->format('d M Y') }} - {{ $coupon->end_date->format('d M Y') }}</td>
                    <td>
                        <x-admin.status-badge :status="$displayStatus" :map="['expired' => 'danger']" />
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
                    <td colspan="9">
                        <x-admin.empty-state>No coupons found.</x-admin.empty-state>
                    </td>
                </tr>
            @endforelse

            <x-slot:pagination>
                {{ $coupons->links() }}
            </x-slot:pagination>
        </x-admin.table-card>

    </main>
</x-admin-layout>
