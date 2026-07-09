<x-admin-layout title="Sales Report">
    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Sales Report</h4>
                <p class="text-muted mb-0">Track total sales, revenue and refund performance</p>
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
            <span>Sales Report</span>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="date" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <input type="date" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <select class="form-select">
                            <option>All Status</option>
                            <option>Completed</option>
                            <option>Pending</option>
                            <option>Cancelled</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <button class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-1">Total Sales</p>
                        <h4 class="mb-0">₹2,45,000</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-1">Total Orders</p>
                        <h4 class="mb-0">320</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-1">Average Order Value</p>
                        <h4 class="mb-0">₹765</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-1">Refund Amount</p>
                        <h4 class="mb-0 text-danger">₹8,500</h4>
                    </div>
                </div>
            </div>

        </div>

        <div class="card">
            <div class="card-header">
                <h5>Sales List</h5>
            </div>

            <div class="card-body table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Order No.</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>09 Jul 2026</td>
                            <td>#ORD-1001</td>
                            <td>Rahul Sharma</td>
                            <td>3</td>
                            <td><span class="badge bg-success">Paid</span></td>
                            <td><span class="badge bg-primary">Delivered</span></td>
                            <td>₹1,250.00</td>
                        </tr>

                        <tr>
                            <td>2</td>
                            <td>08 Jul 2026</td>
                            <td>#ORD-1002</td>
                            <td>Priya Patel</td>
                            <td>2</td>
                            <td><span class="badge bg-warning text-dark">Pending</span></td>
                            <td><span class="badge bg-info">Shipped</span></td>
                            <td>₹799.00</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</x-admin-layout>