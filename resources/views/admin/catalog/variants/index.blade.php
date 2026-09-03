<x-admin-layout title="Product Variants">
<main class="pc-container-edit">

    <x-admin.page-header title="Product Variants" subtitle="Manage product variant pricing, stock and images">
        <x-slot:actions>
            <a href="{{ route('admin.catalog.variants.import') }}" class="btn btn-light-primary me-2">
                <i class="ph ph-upload-simple me-1"></i>
                Import
            </a>

            <a href="{{ route('admin.catalog.variants.export', request()->query()) }}" class="btn btn-light-secondary me-2">
                <i class="ph ph-download-simple me-1"></i>
                Export
            </a>

            <a href="{{ route('admin.catalog.variants.create') }}" class="btn btn-primary">
                <i class="ph ph-plus me-1"></i>
                Add Variant
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[['label' => 'Catalog'], ['label' => 'Variants']]" />

    @include('admin.partials.alerts')

    <x-admin.table-card title="Variant List">
        <x-slot:toolbar>
            <x-admin.filter-toolbar action="{{ route('admin.catalog.variants.index') }}">
                <div class="col-md-4">
                    <input type="text"
                           name="search"
                           class="form-control"
                           value="{{ request('search') }}"
                           placeholder="Search variant"
                           onchange="this.form.submit()">
                </div>

                <div class="col-md-3">
                    <select name="product_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Filter by Product</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected((int) request('product_id') === $product->id)>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="stock_status" class="form-select" onchange="this.form.submit()">
                        <option value="">Filter by Stock</option>
                        <option value="in_stock" @selected(request('stock_status') === 'in_stock')>In Stock</option>
                        <option value="out_of_stock" @selected(request('stock_status') === 'out_of_stock')>Out of Stock</option>
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
            <th>Unit</th>
            <th>Stock</th>
            <th>Price</th>
            <th>Status</th>
            <th width="120">Action</th>
        </x-slot:head>

        @forelse ($variants as $variant)
            <tr>
                <td>{{ $variant->id }}</td>

                <td>
                    <img src="{{ $variant->primaryImage ? \Illuminate\Support\Facades\Storage::url($variant->primaryImage->image) : 'https://placehold.co/50x50' }}"
                         width="50"
                         height="50"
                         class="rounded"
                         alt="Variant">
                </td>

                <td>
                    <strong>{{ $variant->product->name ?? '—' }}</strong>
                </td>

                <td>
                    {{ $variant->variant_name ?? '—' }}
                    @if ($variant->is_default)
                        <br>
                        <small class="text-muted">Default Variant</small>
                    @endif
                </td>

                <td>{{ $variant->sku ?? '—' }}</td>

                <td>{{ $variant->unit ?? '—' }}</td>

                <td>
                    <x-admin.status-badge :status="$variant->stock_status" :label="(string) $variant->stock_quantity" />
                </td>

                <td>{{ $variant->single_price !== null ? '₹'.$variant->single_price : '—' }}</td>

                <td>
                    <x-admin.status-badge :status="$variant->status" />
                </td>

                <td>
                    <a href="{{ route('admin.catalog.variants.edit', $variant) }}"
                       class="btn btn-sm btn-warning"
                       title="Edit Variant">
                        <i class="ph ph-pencil-simple"></i>
                    </a>

                    <form action="{{ route('admin.catalog.variants.destroy', $variant) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this variant?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" title="Delete Variant">
                            <i class="ph ph-trash"></i>
                        </button>
                    </form>
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
