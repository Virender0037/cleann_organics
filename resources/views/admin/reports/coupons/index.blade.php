<x-admin-layout title="Coupons Report">
<main class="pc-container-edit admin-content-padded">

    <x-admin.page-header title="Coupons Report" subtitle="Coupon usage, discount given and revenue from paid orders">
        <x-slot:actions>
            <a href="{{ route('admin.reports.coupons.export', request()->query()) }}" class="btn btn-light-secondary">
                <i class="ph ph-download-simple me-1"></i>Export
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[['label' => 'Reports'], ['label' => 'Coupons Report']]" />

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
            <p class="text-muted mb-1">Total Coupons</p>
            <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
        </div></div></div>

        <div class="col-md-3"><div class="card"><div class="card-body">
            <p class="text-muted mb-1">Active</p>
            <h4 class="mb-0 text-success">{{ number_format($stats['active']) }}</h4>
        </div></div></div>

        <div class="col-md-3"><div class="card"><div class="card-body">
            <p class="text-muted mb-1">Expired</p>
            <h4 class="mb-0 text-danger">{{ number_format($stats['expired']) }}</h4>
        </div></div></div>

        <div class="col-md-3"><div class="card"><div class="card-body">
            <p class="text-muted mb-1">Discount Given</p>
            <h4 class="mb-0">₹{{ number_format($stats['discount_given'], 2) }}</h4>
            <small class="text-muted">All coupon orders</small>
        </div></div></div>
    </div>

    <x-admin.table-card title="Coupon Usage">
        <x-slot:toolbar>
            <x-admin.filter-toolbar action="{{ route('admin.reports.coupons.index') }}">
                <div class="col-md-4">
                    <input name="search" class="form-control" value="{{ request('search') }}"
                           placeholder="Search coupon code">
                </div>

                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        @foreach ($types as $type)
                            <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="state" class="form-select">
                        <option value="">All States</option>
                        @foreach ($states as $state)
                            <option value="{{ $state }}" @selected(request('state') === $state)>{{ ucfirst($state) }}</option>
                        @endforeach
                    </select>
                </div>

                <x-slot:submit>
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </x-slot:submit>
            </x-admin.filter-toolbar>
        </x-slot:toolbar>

        <x-slot:head>
            <th>Code</th>
            <th>Type / Value</th>
            <th class="text-end">Used</th>
            <th class="text-end">Limit</th>
            <th>State</th>
            <th class="text-end">Orders</th>
            <th class="text-end">Discount Generated</th>
            <th class="text-end">Paid Revenue</th>
        </x-slot:head>

        @forelse ($rows as $row)
            <tr>
                <td>{{ $row['code'] }}</td>
                <td>
                    {{ ucfirst($row['type']) }}
                    &mdash;
                    @if ($row['type'] === 'percentage')
                        {{ rtrim(rtrim(number_format($row['value'], 2), '0'), '.') }}%
                    @else
                        ₹{{ number_format($row['value'], 2) }}
                    @endif
                </td>
                <td class="text-end">{{ number_format($row['used_count']) }}</td>
                <td class="text-end">{{ $row['usage_limit'] !== null ? number_format($row['usage_limit']) : '∞' }}</td>
                <td>
                    <x-admin.status-badge :status="$row['state']" :map="['upcoming' => 'info', 'expired' => 'warning']" />
                </td>
                <td class="text-end">{{ number_format($row['orders_count']) }}</td>
                <td class="text-end">₹{{ number_format($row['discount_generated'], 2) }}</td>
                <td class="text-end">₹{{ number_format($row['paid_revenue'], 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8">
                    <x-admin.empty-state>No coupons found.</x-admin.empty-state>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $rows->links() }}
        </x-slot:pagination>
    </x-admin.table-card>

</main>
</x-admin-layout>
