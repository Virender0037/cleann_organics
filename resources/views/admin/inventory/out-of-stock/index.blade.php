<x-admin-layout title="Out of Stock Products">
  <main class="pc-container-edit">

    <x-admin.page-header title="Out of Stock Products" subtitle="Products and variants with zero stock or marked as out of stock">
        <x-slot:actions>
            <a href="{{ route('admin.inventory.out-of-stock.export', request()->query()) }}" class="btn btn-light-secondary">
                <i class="ph ph-download-simple me-1"></i>
                Export
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[['label' => 'Inventory'], ['label' => 'Out of Stock']]" />

    @include('admin.partials.alerts')

    <x-admin.table-card title="Out of Stock List">
        <x-slot:toolbar>
            <x-admin.filter-toolbar action="{{ route('admin.inventory.out-of-stock.index') }}">
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
            <th>Stock Status</th>
            <th>Last Updated</th>
            <th width="120">Action</th>
        </x-slot:head>

        @forelse ($variants as $variant)
            <tr class="table-danger">
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
                    <span class="fw-bold text-danger">{{ $variant->stock_quantity }}</span>
                </td>

                <td>
                    <x-admin.status-badge status="out_of_stock" />
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
                <td colspan="9">
                    <x-admin.empty-state>No out-of-stock variants found.</x-admin.empty-state>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $variants->links() }}
        </x-slot:pagination>
    </x-admin.table-card>

 </main>
</x-admin-layout>
