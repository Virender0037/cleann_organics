<x-admin-layout title="Products Report">
    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Products Report</h4>
                <p class="text-muted mb-0">Track product sales, stock and performance</p>
            </div>

            <button class="btn btn-light-secondary">
                <i class="ph ph-download-simple me-1"></i>
                Export
            </button>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>Reports</span>
            <span class="mx-2">›</span>
            <span>Products Report</span>
        </div>

        <div class="row mb-4">
            <div class="col-md-3"><div class="card"><div class="card-body">
                <p class="text-muted mb-1">Total Products</p>
                <h4>158</h4>
            </div></div></div>

            <div class="col-md-3"><div class="card"><div class="card-body">
                <p class="text-muted mb-1">Active Products</p>
                <h4 class="text-success">142</h4>
            </div></div></div>

            <div class="col-md-3"><div class="card"><div class="card-body">
                <p class="text-muted mb-1">Best Sellers</p>
                <h4>18</h4>
            </div></div></div>

            <div class="col-md-3"><div class="card"><div class="card-body">
                <p class="text-muted mb-1">Out of Stock</p>
                <h4 class="text-danger">7</h4>
            </div></div></div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Product Performance</h5>
            </div>

            <div class="card-body table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Sold Qty</th>
                            <th>Revenue</th>
                            <th>Stock</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Organic Honey</td>
                            <td>Organic Foods</td>
                            <td>120</td>
                            <td>₹35,880</td>
                            <td>80</td>
                            <td><span class="badge bg-success">Active</span></td>
                        </tr>

                        <tr>
                            <td>2</td>
                            <td>Cold Pressed Oil</td>
                            <td>Organic Oils</td>
                            <td>72</td>
                            <td>₹46,800</td>
                            <td>4</td>
                            <td><span class="badge bg-warning text-dark">Low Stock</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</x-admin-layout>