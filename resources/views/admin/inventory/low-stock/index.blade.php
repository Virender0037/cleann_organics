<x-admin-layout title="Low Stock Products">
   <main class="pc-container-edit">

    <x-admin.page-header title="Low Stock Products" subtitle="Products where stock quantity is below or equal to the low stock limit">
        <x-slot:actions>
            <a href="{{ route('admin.inventory.low-stock.export', request()->query()) }}" class="btn btn-light-secondary">
                <i class="ph ph-download-simple me-1"></i>
                Export
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[['label' => 'Inventory'], ['label' => 'Low Stock']]" />

    @include('admin.partials.alerts')

    <x-admin.table-card title="Low Stock List">
        <x-slot:toolbar>
            <x-admin.filter-toolbar action="{{ route('admin.inventory.low-stock.index') }}">
                <div class="col-md-4">
                    <input type="text"
                           name="search"
                           class="form-control"
                           value="{{ request('search') }}"
                           placeholder="Search product, variant or SKU"
                           onchange="this.form.submit()">
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
            <th>Required Stock</th>
            <th>Status</th>
            <th width="120">Action</th>
        </x-slot:head>

        @forelse ($variants as $variant)
            <tr class="table-warning">
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
                    <span class="fw-bold text-warning">{{ $variant->stock_quantity }}</span>
                </td>

                <td>{{ $variant->low_stock_quantity }}</td>

                <td>
                    <span class="badge bg-warning text-dark">{{ $variant->low_stock_quantity - $variant->stock_quantity }} Required</span>
                </td>

                <td>
                    <x-admin.status-badge status="low_stock" />
                </td>

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
                    <x-admin.empty-state>No low-stock variants found.</x-admin.empty-state>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $variants->links() }}
        </x-slot:pagination>
    </x-admin.table-card>

 </main>
</x-admin-layout>
