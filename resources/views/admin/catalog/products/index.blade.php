<x-admin-layout title="Products">
<main class="pc-container-edit">

    <x-admin.page-header title="Products" subtitle="Manage product catalog">
        <x-slot:actions>
            <a href="{{ route('admin.catalog.products.import') }}" class="btn btn-light-primary me-2">
                <i class="ph ph-upload-simple me-1"></i>
                Import
            </a>

            <a href="{{ route('admin.catalog.products.export', request()->query()) }}" class="btn btn-light-secondary me-2">
                <i class="ph ph-download-simple me-1"></i>
                Export
            </a>

            <a href="{{ route('admin.catalog.products.create') }}"
            class="btn btn-primary">
                <i class="ph ph-plus"></i>
                Add Product
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[['label' => 'Catalog'], ['label' => 'Products']]" />

    @include('admin.partials.alerts')

    <x-admin.table-card title="Product List">
        <x-slot:toolbar>
            <x-admin.filter-toolbar action="{{ route('admin.catalog.products.index') }}">
                <div class="col-md-4">
                    <input type="text"
                           name="search"
                           class="form-control"
                           value="{{ request('search') }}"
                           placeholder="Search Product"
                           onchange="this.form.submit()">
                </div>
            </x-admin.filter-toolbar>
        </x-slot:toolbar>

        <x-slot:head>
            <th>#</th>
            <th>Image</th>
            <th>Product</th>
            <th>Category</th>
            <th>Variants</th>
            <th>Status</th>
            <th>Featured</th>
            <th>Stock</th>
            <th>Price Range</th>
            <th>Action</th>
        </x-slot:head>

        @forelse ($products as $product)
            <tr>
                <td>{{ $product->id }}</td>

                <td>
                    @if ($thumbnail = $product->thumbnailImage())
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($thumbnail->image) }}"
                             class="rounded"
                             width="50"
                             height="50"
                             style="object-fit: cover;"
                             alt="{{ $product->name }}">
                    @else
                        <img src="https://placehold.co/50x50"
                             class="rounded"
                             width="50"
                             height="50"
                             alt="Product">
                    @endif
                </td>

                <td>
                    <strong>{{ $product->name }}</strong>
                    <br>
                    <small class="text-muted">Slug: {{ $product->slug }}</small>
                </td>

                <td>{{ $product->category->name ?? '—' }}</td>

                <td>{{ $product->variants_count }}</td>

                <td>
                    <x-admin.status-badge :status="$product->status" />
                </td>

                <td>
                    <span class="badge {{ $product->is_featured ? 'bg-primary' : 'bg-secondary' }}">
                        {{ $product->is_featured ? 'Yes' : 'No' }}
                    </span>
                </td>

                <td>
                    @if ($product->variants_count === 0)
                        —
                    @elseif ($product->variants_sum_stock_quantity > 0)
                        <x-admin.status-badge status="in_stock" label="In Stock" />
                    @else
                        <x-admin.status-badge status="out_of_stock" label="Out of Stock" />
                    @endif
                </td>

                <td>
                    @if ($product->variants_min_single_price === null)
                        —
                    @elseif ($product->variants_min_single_price == $product->variants_max_single_price)
                        ₹{{ $product->variants_min_single_price }}
                    @else
                        ₹{{ $product->variants_min_single_price }} - ₹{{ $product->variants_max_single_price }}
                    @endif
                </td>

                <td>
                    <a href="{{ route('admin.catalog.products.edit', $product) }}" class="btn btn-sm btn-warning" title="Edit Product">
                        <i class="ph ph-pencil-simple"></i>
                    </a>

                    <form action="{{ route('admin.catalog.products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this product?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" title="Delete Product">
                            <i class="ph ph-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10">
                    <x-admin.empty-state>No products found.</x-admin.empty-state>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $products->links() }}
        </x-slot:pagination>
    </x-admin.table-card>
</main>
</x-admin-layout>
