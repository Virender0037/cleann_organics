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

            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Search product, variant or SKU">
                </div>

                <div class="col-md-3">
                    <select class="form-select">
                        <option value="">All Products</option>
                        <option>Organic Honey</option>
                        <option>Cold Pressed Mustard Oil</option>
                    </select>
                </div>
            </div>

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
                        <tr class="table-danger">
                            <td>1</td>

                            <td>
                                <img src="https://placehold.co/50x50"
                                     width="50"
                                     height="50"
                                     class="rounded"
                                     alt="Product">
                            </td>

                            <td>
                                <strong>Organic Jaggery</strong>
                            </td>

                            <td>1kg</td>

                            <td>JAG-1KG</td>

                            <td>
                                <span class="fw-bold text-danger">0</span>
                            </td>

                            <td>
                                <span class="badge bg-danger">Out of Stock</span>
                            </td>

                            <td>26 Jun 2026</td>

                            <td>
                                <a href="{{ route('admin.catalog.variants.edit') }}"
                                   class="btn btn-sm btn-info"
                                   title="Edit Variant">
                                    <i class="ph ph-pencil-simple"></i>
                                </a>
                            </td>
                        </tr>

                        <tr class="table-danger">
                            <td>2</td>

                            <td>
                                <img src="https://placehold.co/50x50"
                                     width="50"
                                     height="50"
                                     class="rounded"
                                     alt="Product">
                            </td>

                            <td>
                                <strong>Organic Honey</strong>
                            </td>

                            <td>250g</td>

                            <td>HON-250</td>

                            <td>
                                <span class="fw-bold text-danger">0</span>
                            </td>

                            <td>
                                <span class="badge bg-danger">Out of Stock</span>
                            </td>

                            <td>26 Jun 2026</td>

                            <td>
                                <a href="{{ route('admin.catalog.variants.edit') }}"
                                   class="btn btn-sm btn-info"
                                   title="Edit Variant">
                                    <i class="ph ph-pencil-simple"></i>
                                </a>
                            </td>
                        </tr>
                    </tbody>

                </table>
            </div>

        </div>
    </div>
 </main>
</x-admin-layout>