<x-admin-layout title="Categories">
<main class="pc-container">    
<div class="pc-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1">Categories</h4>
                        <p class="text-muted mb-0">
                            Manage product categories
                        </p>
                    </div>
                    <div>
                        <button class="btn btn-light-primary me-2">
                            <i class="ph ph-upload-simple"></i>
                            Import
                        </button>
                        <button class="btn btn-light-secondary me-2">
                            <i class="ph ph-download-simple"></i>
                            Export
                        </button>
                        <a href="{{ route('admin.catalog.categories.create') }}"
                        class="btn btn-primary">
                            <i class="ph ph-plus"></i>
                            Add Category
                        </a>
                    </div>
                </div>
                </div>
            </div>
        </div>

        <!-- Breadcrumb -->
        <div class="row">
            <div class="col-sm-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">Catalog</li>
                    <li class="breadcrumb-item active">Categories</li>
                </ul>
            </div>
        </div>

        <!-- Category Table -->
        <div class="card">
            <div class="card-header">
                <h5>Category List</h5>
            </div>

            <div class="card-body">

                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text"
                               class="form-control"
                               placeholder="Search Category">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">

                        <thead>
                            <tr>
                                <th width="80">#</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Status</th>
                                <th>Products</th>
                                <th width="150">Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            <tr>
                                <td>1</td>

                                <td>
                                <img src="{{ asset('assets/images/placeholder.png') }}" width="40" height="40" class="rounded" alt="Category">
                                </td>

                                <td>Organic Oils</td>

                                <td>
                                    organic-oils
                                </td>

                                <td>
                                    <span class="badge bg-success">
                                        Active
                                    </span>
                                </td>

                                <td>12</td>

                                <td>
                                    <a href="{{ route('admin.catalog.categories.edit') }}" class="btn btn-sm btn-info" title="Edit Category">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>

                                    <a href="#" class="btn btn-sm btn-danger" title="Delete Category">
                                        <i class="ph ph-trash"></i>
                                    </a>
                                </td>
                            </tr>

                        </tbody>

                    </table>
                </div>  
                <!-- Pagination -->
                <div class="d-flex justify-content-end">
                    <nav>
                        <ul class="pagination">
                            <li class="page-item disabled">
                                <span class="page-link">Previous</span>
                            </li>

                            <li class="page-item active">
                                <span class="page-link">1</span>
                            </li>

                            <li class="page-item">
                                <a class="page-link" href="#">2</a>
                            </li>

                            <li class="page-item">
                                <a class="page-link" href="#">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>

            </div>
        </div>
    </div>
</main>
</x-admin-layout>