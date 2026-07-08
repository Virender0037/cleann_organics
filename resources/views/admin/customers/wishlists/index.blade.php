<x-admin-layout title="Customer Wishlist">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Customer Wishlist</h4>
                <p class="text-muted mb-0">
                    Products saved by the customer for future purchase
                </p>
            </div>

            <a href="{{ route('admin.customers.show') }}" class="btn btn-light">
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

        <div class="row mb-4">

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-1">Customer</h6>
                        <h5 class="mb-1">Rahul Sharma</h5>
                        <p class="text-muted mb-0">rahul@email.com</p>
                        <small>+91 9876543210</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-1">Wishlist Items</h6>
                        <h4 class="mb-0">6</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-1">In Stock</h6>
                        <h4 class="text-success mb-0">4</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-1">Out of Stock</h6>
                        <h4 class="text-danger mb-0">1</h4>
                    </div>
                </div>
            </div>

        </div>

        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Wishlist Items</h5>

                <span class="badge bg-primary">
                    Total Items : 6
                </span>
            </div>

            <div class="card-body">

                <div class="row mb-4">
                    <div class="col-md-4">
                        <input type="text"
                               class="form-control"
                               placeholder="Search product name or SKU">
                    </div>

                    <div class="col-md-3">
                        <select class="form-select">
                            <option>All Categories</option>
                            <option>Organic Foods</option>
                            <option>Organic Oils</option>
                            <option>Spices</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select class="form-select">
                            <option>All Stock Status</option>
                            <option>In Stock</option>
                            <option>Low Stock</option>
                            <option>Out of Stock</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">
                            Search
                        </button>
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
                                <th>Variant</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Added On</th>
                                <th width="130">Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            <tr>
                                <td>1</td>

                                <td>
                                    <img src="https://placehold.co/60x60"
                                         class="rounded border"
                                         width="60"
                                         height="60"
                                         alt="Organic Honey">
                                </td>

                                <td>
                                    <strong>Organic Honey</strong>
                                    <br>
                                    <small class="text-muted">SKU : HON-500</small>
                                </td>

                                <td>Organic Foods</td>

                                <td>500gm</td>

                                <td>₹299.00</td>

                                <td>
                                    <span class="badge bg-success">In Stock</span>
                                </td>

                                <td>22 Jun 2026</td>

                                <td>
                                    <a href="{{ route('admin.catalog.products.edit') }}"
                                       class="btn btn-sm btn-info"
                                       title="View Product">
                                        <i class="ph ph-eye"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger"
                                            title="Remove from Wishlist">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </td>
                            </tr>

                            <tr>
                                <td>2</td>

                                <td>
                                    <img src="https://placehold.co/60x60"
                                         class="rounded border"
                                         width="60"
                                         height="60"
                                         alt="Cold Pressed Oil">
                                </td>

                                <td>
                                    <strong>Cold Pressed Oil</strong>
                                    <br>
                                    <small class="text-muted">SKU : OIL-1L</small>
                                </td>

                                <td>Organic Oils</td>

                                <td>1 Litre</td>

                                <td>₹650.00</td>

                                <td>
                                    <span class="badge bg-warning text-dark">Low Stock</span>
                                </td>

                                <td>20 Jun 2026</td>

                                <td>
                                    <a href="{{ route('admin.catalog.products.edit') }}"
                                       class="btn btn-sm btn-info"
                                       title="View Product">
                                        <i class="ph ph-eye"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger"
                                            title="Remove from Wishlist">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </td>
                            </tr>

                            <tr>
                                <td>3</td>

                                <td>
                                    <img src="https://placehold.co/60x60"
                                         class="rounded border"
                                         width="60"
                                         height="60"
                                         alt="Organic Turmeric Powder">
                                </td>

                                <td>
                                    <strong>Organic Turmeric Powder</strong>
                                    <br>
                                    <small class="text-muted">SKU : TUR-250</small>
                                </td>

                                <td>Spices</td>

                                <td>250gm</td>

                                <td>₹180.00</td>

                                <td>
                                    <span class="badge bg-danger">Out of Stock</span>
                                </td>

                                <td>18 Jun 2026</td>

                                <td>
                                    <a href="{{ route('admin.catalog.products.edit') }}"
                                       class="btn btn-sm btn-info"
                                       title="View Product">
                                        <i class="ph ph-eye"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger"
                                            title="Remove from Wishlist">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </td>
                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </main>

</x-admin-layout>