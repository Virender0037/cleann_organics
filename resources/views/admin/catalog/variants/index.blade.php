<x-admin-layout title="Product Variants">
<main class="pc-container-edit"> 
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Product Variants</h4>
            <p class="text-muted mb-0">Manage product variant pricing, stock and images</p>
        </div>

        <div>
            <a href="#" class="btn btn-light-primary me-2">
                <i class="ph ph-upload-simple me-1"></i>
                Import
            </a>

            <a href="#" class="btn btn-light-secondary me-2">
                <i class="ph ph-download-simple me-1"></i>
                Export
            </a>

            <a href="{{ route('admin.catalog.variants.create') }}" class="btn btn-primary">
                <i class="ph ph-plus me-1"></i>
                Add Variant
            </a>
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <span class="mx-2">›</span>
        <span>Catalog</span>
        <span class="mx-2">›</span>
        <span>Variants</span>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Variant List</h5>
        </div>

        <div class="card-body">

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form method="GET" action="{{ route('admin.catalog.variants.index') }}">
                <div class="row mb-3">
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
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
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
                        </tr>
                    </thead>

                    <tbody>
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
                                    <span class="badge {{ $variant->stock_status === 'in_stock' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $variant->stock_quantity }}
                                    </span>
                                </td>

                                <td>{{ $variant->single_price !== null ? '₹'.$variant->single_price : '—' }}</td>

                                <td>
                                    <span class="badge {{ $variant->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ ucfirst($variant->status) }}
                                    </span>
                                </td>

                                <td>
                                    <a href="{{ route('admin.catalog.variants.edit', $variant) }}"
                                       class="btn btn-sm btn-info"
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
                                <td colspan="10" class="text-center text-muted">No variants found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $variants->links() }}
            </div>

        </div>
    </div>
</main>
</x-admin-layout>