<x-admin-layout title="Categories">
    <div class="pc-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4 class="mb-0">Categories</h4>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="#" class="btn btn-primary">
                            <i class="ph ph-plus"></i>
                            Add Category
                        </a>
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
                                    <img src="https://via.placeholder.com/50"
                                         class="rounded"
                                         width="50">
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
                                    <a href="#"
                                       class="btn btn-sm btn-info">
                                        <i class="ph ph-pencil"></i>
                                    </a>

                                    <a href="#"
                                       class="btn btn-sm btn-danger">
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
</x-admin-layout>