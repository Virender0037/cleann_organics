<x-admin-layout title="Out of Stock Products">
  <main class="pc-container-edit">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Out of Stock Products</h4>
            <p class="text-muted mb-0">Products and variants with zero stock or marked as out of stock</p>
        </div>

        <div>
            <a href="#" class="btn btn-light-secondary">
                <i class="ph ph-download-simple me-1"></i>
                Export
            </a>
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <span class="mx-2">›</span>
        <span>Inventory</span>
        <span class="mx-2">›</span>
        <span>Out of Stock</span>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Out of Stock List</h5>
        </div>

        <div class="card-body">

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="GET" action="{{ route('admin.inventory.out-of-stock.index') }}">
                <div class="row mb-3">
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
                            <th>Current Stock</th>
                            <th>Stock Status</th>
                            <th>Last Updated</th>
                            <th width="120">Action</th>
                        </tr>
                    </thead>

                    <tbody>
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
                                    <span class="badge bg-danger">Out of Stock</span>
                                </td>

                                <td>{{ $variant->updated_at->format('d M Y') }}</td>

                                <td>
                                    <a href="{{ route('admin.catalog.variants.edit', $variant) }}"
                                       class="btn btn-sm btn-info"
                                       title="Edit Variant">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">No out-of-stock variants found.</td>
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