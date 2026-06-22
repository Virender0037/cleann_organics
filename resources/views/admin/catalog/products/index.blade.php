<x-admin-layout title="Products">
<main class="pc-container-edit">    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Products</h4>
            <p class="text-muted mb-0">Manage product catalog</p>
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
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Search Product">
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
                        <tr>
                            <td>1</td>

                            <td>
                                <img src="https://placehold.co/50x50"
                                     class="rounded"
                                     width="50"
                                     height="50"
                                     alt="Product">
                            </td>

                            <td>
                                <strong>Organic Honey</strong>
                                <br>
                                <small class="text-muted">Slug: organic-honey</small>
                            </td>

                            <td>Organic Foods</td>

                            <td>3</td>

                            <td>
                                <span class="badge bg-success">Active</span>
                            </td>

                            <td>
                                <span class="badge bg-primary">Yes</span>
                            </td>

                            <td>
                                <span class="badge bg-success">In Stock</span>
                            </td>

                            <td>₹199 - ₹599</td>

                            <td>
                                <a href="{{ route('admin.catalog.products.edit') }}" class="btn btn-sm btn-info" title="Edit Product">
                                    <i class="ph ph-pencil-simple"></i>
                                </a>

                                <a href="#" class="btn btn-sm btn-danger" title="Delete Product">
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