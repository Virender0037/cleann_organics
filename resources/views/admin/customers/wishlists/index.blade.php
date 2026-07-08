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

        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">

                <h5 class="mb-0">Wishlist Items</h5>

                <span class="badge bg-primary">
                    Total Items : 6
                </span>

            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>

                        <tr>

                            <th>#</th>

                            <th>Image</th>

                            <th>Product</th>

                            <th>Category</th>

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
                                     width="60">
                            </td>

                            <td>
                                <strong>Organic Honey</strong><br>
                                <small class="text-muted">
                                    SKU : HON-500
                                </small>
                            </td>

                            <td>Organic Foods</td>

                            <td>₹299</td>

                            <td>
                                <span class="badge bg-success">
                                    In Stock
                                </span>
                            </td>

                            <td>22 Jun 2026</td>

                            <td>

                                <button
                                    class="btn btn-info btn-sm"
                                    title="View Product">

                                    <i class="ph ph-eye"></i>

                                </button>

                                <button
                                    class="btn btn-danger btn-sm"
                                    title="Remove">

                                    <i class="ph ph-trash"></i>

                                </button>

                            </td>

                        </tr>

                        <tr>

                            <td>2</td>

                            <td>
                                <img src="https://placehold.co/60x60"
                                     class="rounded border"
                                     width="60">
                            </td>

                            <td>
                                <strong>Cold Pressed Oil</strong><br>
                                <small class="text-muted">
                                    SKU : OIL-1L
                                </small>
                            </td>

                            <td>Organic Oils</td>

                            <td>₹650</td>

                            <td>
                                <span class="badge bg-warning text-dark">
                                    Low Stock
                                </span>
                            </td>

                            <td>20 Jun 2026</td>

                            <td>

                                <button
                                    class="btn btn-info btn-sm">

                                    <i class="ph ph-eye"></i>

                                </button>

                                <button
                                    class="btn btn-danger btn-sm">

                                    <i class="ph ph-trash"></i>

                                </button>

                            </td>

                        </tr>

                        <tr>

                            <td>3</td>

                            <td>
                                <img src="https://placehold.co/60x60"
                                     class="rounded border"
                                     width="60">
                            </td>

                            <td>
                                <strong>Organic Turmeric Powder</strong><br>
                                <small class="text-muted">
                                    SKU : TUR-250
                                </small>
                            </td>

                            <td>Spices</td>

                            <td>₹180</td>

                            <td>
                                <span class="badge bg-danger">
                                    Out of Stock
                                </span>
                            </td>

                            <td>18 Jun 2026</td>

                            <td>

                                <button
                                    class="btn btn-info btn-sm">

                                    <i class="ph ph-eye"></i>

                                </button>

                                <button
                                    class="btn btn-danger btn-sm">

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