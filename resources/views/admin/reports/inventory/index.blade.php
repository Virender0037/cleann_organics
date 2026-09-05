<x-admin-layout title="Inventory Report">
<main class="pc-container-edit admin-content-padded">

    <x-admin.page-header title="Inventory Report" subtitle="Current stock snapshot across product variants">
        <x-slot:actions>
            <a href="{{ route('admin.reports.inventory.export', request()->query()) }}" class="btn btn-light-secondary">
                <i class="ph ph-download-simple me-1"></i>Export
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[['label' => 'Reports'], ['label' => 'Inventory Report']]" />

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
            <p class="text-muted mb-1">Active Variants</p>
            <h4 class="mb-0">{{ number_format($stats['active_variants']) }}</h4>
        </div></div></div>

        <div class="col-md-3"><div class="card"><div class="card-body">
            <p class="text-muted mb-1">In Stock</p>
            <h4 class="mb-0 text-success">{{ number_format($stats['in_stock']) }}</h4>
        </div></div></div>

        <div class="col-md-3"><div class="card"><div class="card-body">
            <p class="text-muted mb-1">Low Stock</p>
            <h4 class="mb-0 text-warning">{{ number_format($stats['low_stock']) }}</h4>
        </div></div></div>

        <div class="col-md-3"><div class="card"><div class="card-body">
            <p class="text-muted mb-1">Out of Stock</p>
            <h4 class="mb-0 text-danger">{{ number_format($stats['out_of_stock']) }}</h4>
        </div></div></div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card"><div class="card-body">
            <p class="text-muted mb-1">Inactive Variants</p>
            <h4 class="mb-0 text-muted">{{ number_format($stats['inactive_variants']) }}</h4>
            <small class="text-muted">Not counted as available</small>
        </div></div></div>
    </div>

    <x-admin.table-card title="Variant Stock">
        <x-slot:toolbar>
            <x-admin.filter-toolbar action="{{ route('admin.reports.inventory.index') }}">
                <div class="col-md-3">
                    <input name="search" class="form-control" value="{{ request('search') }}"
                           placeholder="SKU / Variant / Product">
                </div>

                <div class="col-md-3">
                    <select name="product_id" class="form-select">
                        <option value="">All Products</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected((string) request('product_id') === (string) $product->id)>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="stock" class="form-select">
                        <option value="">All Stock</option>
                        @foreach ($stockFilters as $filter)
                            <option value="{{ $filter }}" @selected(request('stock') === $filter)>
                                {{ ucwords(str_replace('_', ' ', $filter)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="variant_status" class="form-select">
                        <option value="">All Variant Status</option>
                        @foreach ($variantStatuses as $status)
                            <option value="{{ $status }}" @selected(request('variant_status') === $status)>
                                {{ ucfirst($status) }}
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
            <th>SKU</th>
            <th>Product</th>
            <th>Variant</th>
            <th>Category</th>
            <th class="text-end">Current Stock</th>
            <th class="text-end">Low Stock Threshold</th>
            <th>Stock Status</th>
            <th>Product Status</th>
            <th>Variant Status</th>
        </x-slot:head>

        @forelse ($variants as $variant)
            @php
                $stockState = ($variant->stock_quantity <= 0 || $variant->stock_status === 'out_of_stock')
                    ? 'out_of_stock'
                    : ($variant->stock_quantity <= $variant->low_stock_quantity ? 'low_stock' : 'in_stock');
            @endphp
            <tr>
                <td>{{ $variant->sku }}</td>
                <td>{{ $variant->product->name ?? '—' }}</td>
                <td>{{ $variant->variant_name }}</td>
                <td>{{ $variant->product->category->name ?? '—' }}</td>
                <td class="text-end">{{ number_format($variant->stock_quantity) }}</td>
                <td class="text-end">{{ number_format($variant->low_stock_quantity) }}</td>
                <td><x-admin.status-badge :status="$stockState" /></td>
                <td><x-admin.status-badge :status="$variant->product->status ?? 'inactive'" /></td>
                <td><x-admin.status-badge :status="$variant->status" /></td>
            </tr>
        @empty
            <tr>
                <td colspan="9">
                    <x-admin.empty-state>No variants found.</x-admin.empty-state>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $variants->links() }}
        </x-slot:pagination>
    </x-admin.table-card>

</main>
</x-admin-layout>
