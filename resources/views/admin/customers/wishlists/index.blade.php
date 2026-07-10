<x-admin-layout title="Customer Wishlist">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Customer Wishlist</h4>
                <p class="text-muted mb-0">
                    Products saved by the customer for future purchase
                </p>
            </div>

            <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-light">
                <i class="ph ph-arrow-left me-1"></i>
                Back
            </a>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>

            <a href="{{ route('admin.customers.index') }}">Customers</a>
            <span class="mx-2">›</span>

            <span>Wishlist</span>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @php
            $inStockCount = $wishlists->filter(fn ($w) => $w->product?->defaultVariant?->stock_status === 'in_stock')->count();
            $outOfStockCount = $wishlists->filter(fn ($w) => ($w->product?->defaultVariant?->stock_status ?? 'out_of_stock') === 'out_of_stock')->count();
        @endphp

        <div class="row mb-4">

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-1">Customer</h6>
                        <h5 class="mb-1">{{ $customer->name }}</h5>
                        <p class="text-muted mb-0">{{ $customer->email }}</p>
                        <small>{{ $customer->phone ?? '—' }}</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-1">Wishlist Items</h6>
                        <h4 class="mb-0">{{ $wishlists->total() }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-1">In Stock (this page)</h6>
                        <h4 class="text-success mb-0">{{ $inStockCount }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-1">Out of Stock (this page)</h6>
                        <h4 class="text-danger mb-0">{{ $outOfStockCount }}</h4>
                    </div>
                </div>
            </div>

        </div>

        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Wishlist Items</h5>

                <span class="badge bg-primary">
                    Total Items : {{ $wishlists->total() }}
                </span>
            </div>

            <div class="card-body">

                <form method="GET" action="{{ route('admin.customers.wishlists.index', $customer) }}">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <input type="text"
                                   name="search"
                                   class="form-control"
                                   value="{{ request('search') }}"
                                   placeholder="Search product name or SKU">
                        </div>

                        <div class="col-md-3">
                            <select name="category_id" class="form-select">
                                <option value="">All Categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((int) request('category_id') === $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <select name="stock_status" class="form-select">
                                <option value="">All Stock Status</option>
                                <option value="in_stock" @selected(request('stock_status') === 'in_stock')>In Stock</option>
                                <option value="out_of_stock" @selected(request('stock_status') === 'out_of_stock')>Out of Stock</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                Search
                            </button>
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
                                <th>Category</th>
                                <th>Variant</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Added On</th>
                                <th width="130">Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse ($wishlists as $wishlist)
                                @php $variant = $wishlist->product?->defaultVariant; @endphp
                                <tr>
                                    <td>{{ $wishlist->id }}</td>

                                    <td>
                                        <img src="{{ $variant?->primaryImage ? \Illuminate\Support\Facades\Storage::url($variant->primaryImage->image) : 'https://placehold.co/60x60' }}"
                                             class="rounded border"
                                             width="60"
                                             height="60"
                                             alt="{{ $wishlist->product->name ?? 'Product' }}">
                                    </td>

                                    <td>
                                        <strong>{{ $wishlist->product->name ?? '—' }}</strong>
                                        <br>
                                        <small class="text-muted">SKU : {{ $variant->sku ?? '—' }}</small>
                                    </td>

                                    <td>{{ $wishlist->product->category->name ?? '—' }}</td>

                                    <td>{{ $variant->variant_name ?? '—' }}</td>

                                    <td>{{ $variant && $variant->single_price !== null ? '₹'.number_format((float) $variant->single_price, 2) : '—' }}</td>

                                    <td>
                                        @if (! $variant)
                                            <span class="badge bg-secondary">—</span>
                                        @elseif ($variant->stock_status === 'in_stock')
                                            <span class="badge bg-success">In Stock</span>
                                        @else
                                            <span class="badge bg-danger">Out of Stock</span>
                                        @endif
                                    </td>

                                    <td>{{ $wishlist->created_at->format('d M Y') }}</td>

                                    <td>
                                        @if ($wishlist->product)
                                            <a href="{{ route('admin.catalog.products.edit', $wishlist->product) }}"
                                               class="btn btn-sm btn-info"
                                               title="View Product">
                                                <i class="ph ph-eye"></i>
                                            </a>
                                        @endif

                                        <form action="{{ route('admin.customers.wishlists.destroy', [$customer, $wishlist]) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove from wishlist?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Remove from Wishlist">
                                                <i class="ph ph-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No wishlist items.</td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

                <div class="d-flex justify-content-end">
                    {{ $wishlists->links() }}
                </div>

            </div>

        </div>

    </main>

</x-admin-layout>