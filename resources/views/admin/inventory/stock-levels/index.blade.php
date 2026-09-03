<x-admin-layout title="Stock Levels">
  <main class="pc-container-edit">

    <x-admin.page-header title="Stock Levels" subtitle="Monitor product variant stock and inventory status">
        <x-slot:actions>
            <a href="{{ route('admin.inventory.stock-levels.export', request()->query()) }}" class="btn btn-light-secondary">
                <i class="ph ph-download-simple me-1"></i>
                Export
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[['label' => 'Inventory'], ['label' => 'Stock Levels']]" />

    @include('admin.partials.alerts')

    <x-admin.table-card title="Stock Level List">
        <x-slot:toolbar>
            <x-admin.filter-toolbar action="{{ route('admin.inventory.stock-levels.index') }}">
                <div class="col-md-4">
                    <input type="text"
                           name="search"
                           class="form-control"
                           value="{{ request('search') }}"
                           placeholder="Search product, variant or SKU"
                           onchange="this.form.submit()">
                </div>

                <div class="col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Stock Status</option>
                        <option value="in_stock" @selected(request('status') === 'in_stock')>In Stock</option>
                        <option value="out_of_stock" @selected(request('status') === 'out_of_stock')>Out of Stock</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="product_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Products</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected((int) request('product_id') === $product->id)>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </x-admin.filter-toolbar>
        </x-slot:toolbar>

        <x-slot:head>
            <th>#</th>
            <th>Image</th>
            <th>Product</th>
            <th>Variant</th>
            <th>SKU</th>
            <th>Current Stock</th>
            <th>Low Stock Limit</th>
            <th>Stock Status</th>
            <th>Last Updated</th>
            <th width="120">Action</th>
        </x-slot:head>

        @forelse ($variants as $variant)
            @php
                $isOutOfStock = $variant->stock_quantity == 0 || $variant->stock_status === 'out_of_stock';
                $isLowStock = ! $isOutOfStock && $variant->stock_quantity <= $variant->low_stock_quantity;
                $displayStatus = $isOutOfStock ? 'out_of_stock' : ($isLowStock ? 'low_stock' : 'in_stock');
                $rowClass = $isOutOfStock ? 'table-danger' : ($isLowStock ? 'table-warning' : '');
                $stockTextClass = $isOutOfStock ? 'text-danger' : ($isLowStock ? 'text-warning' : 'text-success');
            @endphp
            <tr class="{{ $rowClass }}">
                <td>{{ $variant->id }}</td>

                <td>
                    <img src="{{ $variant->primaryImage ? \Illuminate\Support\Facades\Storage::url($variant->primaryImage->image) : 'https://placehold.co/50x50' }}"
                         width="50"
                         height="50"
                         class="rounded"
                         alt="Product">
                </td>

                <td>
                    <strong>{{ $variant->product->name ?? '—' }}</strong>
                </td>

                <td>{{ $variant->variant_name ?? '—' }}</td>

                <td>{{ $variant->sku ?? '—' }}</td>

                <td>
                    <span class="fw-bold {{ $stockTextClass }}">{{ $variant->stock_quantity }}</span>
                </td>

                <td>{{ $variant->low_stock_quantity }}</td>

                <td>
                    <x-admin.status-badge :status="$displayStatus" />
                </td>

                <td>{{ $variant->updated_at->format('d M Y') }}</td>

                <td>
                    <a href="{{ route('admin.catalog.variants.edit', $variant) }}"
                       class="btn btn-sm btn-warning"
                       title="Edit Variant">
                        <i class="ph ph-pencil-simple"></i>
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10">
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
