<x-admin-layout title="Product Reviews">
    <main class="pc-container-edit">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Product Reviews</h4>
            <p class="text-muted mb-0">Manage customer product reviews and ratings</p>
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
        <span>Catalog</span>
        <span class="mx-2">›</span>
        <span>Reviews</span>
    </div>

    <div class="card">

        <div class="card-header">
            <h5>Review List</h5>
        </div>

        <div class="card-body">

            <div class="row mb-3">

                <div class="col-md-4">
                    <input type="text"
                           class="form-control"
                           placeholder="Search Product or Customer">
                </div>

                <div class="col-md-3">
                    <select class="form-select">
                        <option>All Ratings</option>
                        <option>★★★★★ (5)</option>
                        <option>★★★★☆ (4)</option>
                        <option>★★★☆☆ (3)</option>
                        <option>★★☆☆☆ (2)</option>
                        <option>★☆☆☆☆ (1)</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select class="form-select">
                        <option>All Status</option>
                        <option>Pending</option>
                        <option>Approved</option>
                        <option>Rejected</option>
                    </select>
                </div>

            </div>

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Customer</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th width="160">Action</th>
                    </tr>
                    </thead>

                    <tbody>

                    <tr>

                        <td>1</td>

                        <td>
                            <strong>Organic Honey</strong>
                        </td>

                        <td>
                            Rahul Sharma
                            <br>
                            <small class="text-muted">
                                rahul@email.com
                            </small>
                        </td>

                        <td>
                            ⭐⭐⭐⭐⭐
                        </td>

                        <td style="max-width:250px">
                            Very good quality product.
                            Packaging was excellent and delivery was quick.
                        </td>

                        <td>
                            <span class="badge bg-warning">
                                Pending
                            </span>
                        </td>

                        <td>
                            25 Jun 2026
                        </td>

                        <td>

                            <button class="btn btn-sm btn-success"
                                    title="Approve">
                                <i class="ph ph-check"></i>
                            </button>

                            <button class="btn btn-sm btn-warning"
                                    title="Reject">
                                <i class="ph ph-x"></i>
                            </button>

                            <button class="btn btn-sm btn-info"
                                    title="View">
                                <i class="ph ph-eye"></i>
                            </button>

                            <button class="btn btn-sm btn-danger"
                                    title="Delete">
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