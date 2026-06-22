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
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Search variant">
                </div>

                <div class="col-md-3">
                    <select class="form-select">
                        <option value="">Filter by Product</option>
                        <option>Organic Honey</option>
                        <option>Cold Pressed Oil</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select class="form-select">
                        <option value="">Filter by Stock</option>
                        <option>In Stock</option>
                        <option>Out of Stock</option>
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
                            <th>Unit</th>
                            <th>Stock</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th width="120">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>1</td>

                            <td>
                                <img src="https://placehold.co/50x50"
                                     width="50"
                                     height="50"
                                     class="rounded"
                                     alt="Variant">
                            </td>

                            <td>
                                <strong>Organic Honey</strong>
                            </td>

                            <td>
                                500g
                                <br>
                                <small class="text-muted">Default Variant</small>
                            </td>

                            <td>HON-500</td>

                            <td>gram</td>

                            <td>
                                <span class="badge bg-success">120</span>
                            </td>

                            <td>₹299</td>

                            <td>
                                <span class="badge bg-success">Active</span>
                            </td>

                            <td>
                                <a href="{{ route('admin.catalog.variants.edit') }}"
                                   class="btn btn-sm btn-info"
                                   title="Edit Variant">
                                    <i class="ph ph-pencil-simple"></i>
                                </a>

                                <a href="#"
                                   class="btn btn-sm btn-danger"
                                   title="Delete Variant">
                                    <i class="ph ph-trash"></i>
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