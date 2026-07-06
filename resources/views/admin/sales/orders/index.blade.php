<x-admin-layout title="Orders">
    <main class="pc-container-edit">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Orders</h4>
            <p class="text-muted mb-0">Manage customer orders and order status</p>
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
        <span>Sales</span>
        <span class="mx-2">›</span>
        <span>Orders</span>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Order List</h5>
        </div>

        <div class="card-body">

            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Search order number or customer">
                </div>

                <div class="col-md-3">
                    <select class="form-select">
                        <option value="">All Order Status</option>
                        <option>Pending</option>
                        <option>Confirmed</option>
                        <option>Packed</option>
                        <option>Shipped</option>
                        <option>Delivered</option>
                        <option>Cancelled</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select class="form-select">
                        <option value="">All Payment Status</option>
                        <option>Pending</option>
                        <option>Paid</option>
                        <option>Failed</option>
                        <option>Refunded</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Order No.</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Order Status</th>
                            <th>Date</th>
                            <th width="140">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>1</td>

                            <td>
                                <strong>#ORD-1001</strong>
                            </td>

                            <td>
                                Rahul Sharma
                                <br>
                                <small class="text-muted">rahul@email.com</small>
                            </td>

                            <td>₹1,250.00</td>

                            <td>
                                <span class="badge bg-success">Paid</span>
                                <br>
                                <small class="text-muted">UPI</small>
                            </td>

                            <td>
                                <span class="badge bg-primary">Confirmed</span>
                            </td>

                            <td>26 Jun 2026</td>

                            <td>
                                <a href="#" class="btn btn-sm btn-info" title="View Order">
                                    <i class="ph ph-eye"></i>
                                </a>

                                <a href="#" class="btn btn-sm btn-warning" title="Update Status">
                                    <i class="ph ph-pencil-simple"></i>
                                </a>
                            </td>
                        </tr>

                        <tr>
                            <td>2</td>

                            <td>
                                <strong>#ORD-1002</strong>
                            </td>

                            <td>
                                Amit Verma
                                <br>
                                <small class="text-muted">amit@email.com</small>
                            </td>

                            <td>₹799.00</td>

                            <td>
                                <span class="badge bg-warning">Pending</span>
                                <br>
                                <small class="text-muted">COD</small>
                            </td>

                            <td>
                                <span class="badge bg-warning">Pending</span>
                            </td>

                            <td>26 Jun 2026</td>

                            <td>
                                <a href="#" class="btn btn-sm btn-info" title="View Order">
                                    <i class="ph ph-eye"></i>
                                </a>

                                <a href="#" class="btn btn-sm btn-warning" title="Update Status">
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
