<x-admin-layout title="Products Report">
<main class="pc-container-edit admin-content-padded">

    <x-admin.page-header title="Products Report" subtitle="Units sold, orders and revenue per product from paid orders">
        <x-slot:actions>
            <a href="{{ route('admin.reports.products.export', request()->query()) }}" class="btn btn-light-secondary">
                <i class="ph ph-download-simple me-1"></i>Export
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[['label' => 'Reports'], ['label' => 'Products Report']]" />

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
            <p class="text-muted mb-1">Total Products</p>
            <h4 class="mb-0">{{ number_format($stats['total_products']) }}</h4>
        </div></div></div>

        <div class="col-md-3"><div class="card"><div class="card-body">
            <p class="text-muted mb-1">Active Products</p>
            <h4 class="mb-0 text-success">{{ number_format($stats['active_products']) }}</h4>
        </div></div></div>

        <div class="col-md-3"><div class="card"><div class="card-body">
            <p class="text-muted mb-1">Products With Sales</p>
            <h4 class="mb-0">{{ number_format($stats['products_with_sales']) }}</h4>
            <small class="text-muted">In selected period</small>
        </div></div></div>

        <div class="col-md-3"><div class="card"><div class="card-body">
            <p class="text-muted mb-1">Units Sold</p>
            <h4 class="mb-0">{{ number_format($stats['units_sold']) }}</h4>
            <small class="text-muted">Paid orders only</small>
        </div></div></div>
    </div>

    <x-admin.table-card title="Product Performance">
        <x-slot:toolbar>
            <x-admin.filter-toolbar action="{{ route('admin.reports.products.index') }}">
                <div class="col-md-3">
                    <input name="search" class="form-control" value="{{ request('search') }}"
                           placeholder="Search product name">
                </div>

                <div class="col-md-2">
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>

                <div class="col-md-2">
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>

                <div class="col-md-3">
                    <select name="category_id" class="form-select">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                                {{ $category->name }}
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
            <th>Product</th>
            <th>Category</th>
            <th>Status</th>
            <th class="text-end">Units Sold</th>
            <th class="text-end">Orders</th>
            <th class="text-end">Revenue</th>
        </x-slot:head>

        @forelse ($rows as $row)
            <tr>
                <td>
                    {{ $row['name'] }}
                    @if (! $row['product_id'])
                        <span class="badge bg-secondary">Deleted</span>
                    @endif
                </td>
                <td>{{ $row['category'] }}</td>
                <td>
                    @if ($row['product_status'])
                        <x-admin.status-badge :status="$row['product_status']" />
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td class="text-end">{{ number_format($row['units_sold']) }}</td>
                <td class="text-end">{{ number_format($row['order_count']) }}</td>
                <td class="text-end">₹{{ number_format($row['revenue'], 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6">
                    <x-admin.empty-state>No product sales found for this period.</x-admin.empty-state>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $rows->links() }}
        </x-slot:pagination>
    </x-admin.table-card>

</main>
</x-admin-layout>
