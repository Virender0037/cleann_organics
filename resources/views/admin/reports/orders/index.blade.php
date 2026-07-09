<x-admin-layout title="Orders Report">
    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Orders Report</h4>
                <p class="text-muted mb-0">Track order status, delivery progress and order volume</p>
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
            <span>Orders Report</span>
        </div>

        <div class="row mb-4">

            <div class="col-md-3">
                <div class="card"><div class="card-body">
                    <p class="text-muted mb-1">Total Orders</p>
                    <h4>320</h4>
                </div></div>
            </div>

            <div class="col-md-3">
                <div class="card"><div class="card-body">
                    <p class="text-muted mb-1">Delivered</p>
                    <h4 class="text-success">245</h4>
                </div></div>
            </div>

            <div class="col-md-3">
                <div class="card"><div class="card-body">
                    <p class="text-muted mb-1">Pending</p>
                    <h4 class="text-warning">48</h4>
                </div></div>
            </div>

            <div class="col-md-3">
                <div class="card"><div class="card-body">
                    <p class="text-muted mb-1">Cancelled</p>
                    <h4 class="text-danger">27</h4>
                </div></div>
            </div>

        </div>

        <div class="card">
            <div class="card-header">
                <h5>Order Status Report</h5>
            </div>

            <div class="card-body table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Order No.</th>
                            <th>Customer</th>
                            <th>Order Date</th>
                            <th>Payment</th>
                            <th>Order Status</th>
                            <th>Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>#ORD-1001</td>
                            <td>Rahul Sharma</td>
                            <td>09 Jul 2026</td>
                            <td><span class="badge bg-success">Paid</span></td>
                            <td><span class="badge bg-success">Delivered</span></td>
                            <td>₹1,250.00</td>
                        </tr>

                        <tr>
                            <td>2</td>
                            <td>#ORD-1002</td>
                            <td>Amit Verma</td>
                            <td>08 Jul 2026</td>
                            <td><span class="badge bg-warning text-dark">Pending</span></td>
                            <td><span class="badge bg-warning text-dark">Pending</span></td>
                            <td>₹799.00</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</x-admin-layout>