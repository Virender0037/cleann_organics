<x-admin-layout title="Products">
<main class="pc-container-edit">    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Products</h4>
            <p class="text-muted mb-0">Manage product catalog</p>
        </div>

        <div>
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
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <span class="mx-2">›</span>
        <span>Catalog</span>
        <span class="mx-2">›</span>
        <span>Products</span>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Product List</h5>
        </div>

        <div class="card-body">

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="row mb-3">
                <div class="col-md-4">
                    <form method="GET" action="{{ route('admin.catalog.products.index') }}">
                        <input type="text"
                               name="search"
                               class="form-control"
                               value="{{ request('search') }}"
                               placeholder="Search Product"
                               onchange="this.form.submit()">
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
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
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td>{{ $product->id }}</td>

                                <td>
                                    <img src="https://placehold.co/50x50"
                                         class="rounded"
                                         width="50"
                                         height="50"
                                         alt="Product">
                                </td>

                                <td>
                                    <strong>{{ $product->name }}</strong>
                                    <br>
                                    <small class="text-muted">Slug: {{ $product->slug }}</small>
                                </td>

                                <td>{{ $product->category->name ?? '—' }}</td>

                                <td>{{ $product->variants_count }}</td>

                                <td>
                                    @php
                                        $statusBadge = match ($product->status) {
                                            'active' => 'bg-success',
                                            'inactive' => 'bg-secondary',
                                            default => 'bg-warning',
                                        };
                                    @endphp
                                    <span class="badge {{ $statusBadge }}">{{ ucfirst($product->status) }}</span>
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
                                        <span class="badge bg-success">In Stock</span>
                                    @else
                                        <span class="badge bg-danger">Out of Stock</span>
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
                                    <a href="{{ route('admin.catalog.products.edit', $product) }}" class="btn btn-sm btn-info" title="Edit Product">
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
                                <td colspan="10" class="text-center text-muted">No products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $products->links() }}
            </div>

        </div>
    </div>
</main>
</x-admin-layout>