<x-admin-layout title="Payments">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Payments</h4>
                <p class="text-muted mb-0">Manage customer payment transactions</p>
            </div>

            <div>
                <a href="#" class="btn btn-light-secondary">
                    <i class="ph ph-download-simple me-1"></i>
                    Export Payments
                </a>
            </div>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>Sales</span>
            <span class="mx-2">›</span>
            <span>Payments</span>
        </div>

        <div class="card">

            <div class="card-header">
                <h5>Payment Transactions</h5>
            </div>

            <div class="card-body">

                <div class="row mb-4">

                    <div class="col-md-4">
                        <input
                            type="text"
                            class="form-control"
                            placeholder="Search Order / Customer / Transaction">
                    </div>

                    <div class="col-md-3">
                        <select class="form-select">
                            <option>All Payment Methods</option>
                            <option>Razorpay</option>
                            <option>Stripe</option>
                            <option>UPI</option>
                            <option>COD</option>
                            <option>Cashfree</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select class="form-select">
                            <option>All Status</option>
                            <option>Paid</option>
                            <option>Pending</option>
                            <option>Failed</option>
                            <option>Refunded</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">
                            Search
                        </button>
                    </div>

                </div>

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>

                            <tr>
                                <th>#</th>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Transaction ID</th>
                                <th>Method</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Paid On</th>
                                <th width="120">Action</th>
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
                                    <small class="text-muted">
                                        rahul@email.com
                                    </small>
                                </td>

                                <td>
                                    TXN458965214
                                </td>

                                <td>
                                    <span class="badge bg-primary">
                                        Razorpay
                                    </span>
                                </td>

                                <td>
                                    ₹1,250.00
                                </td>

                                <td>
                                    <span class="badge bg-success">
                                        Paid
                                    </span>
                                </td>

                                <td>
                                    26 Jun 2026
                                    <br>
                                    <small class="text-muted">
                                        10:45 AM
                                    </small>
                                </td>

                                <td>

                                    <button class="btn btn-info btn-sm" title="View Payment">
                                        <i class="ph ph-eye"></i>
                                    </button>

                                    <button class="btn btn-success btn-sm" title="Download Receipt">
                                        <i class="ph ph-download-simple"></i>
                                    </button>

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
                                    <small class="text-muted">
                                        amit@email.com
                                    </small>
                                </td>

                                <td>
                                    TXN458965215
                                </td>

                                <td>
                                    <span class="badge bg-warning text-dark">
                                        COD
                                    </span>
                                </td>

                                <td>
                                    ₹799.00
                                </td>

                                <td>
                                    <span class="badge bg-warning text-dark">
                                        Pending
                                    </span>
                                </td>

                                <td>
                                    --
                                </td>

                                <td>

                                    <button class="btn btn-info btn-sm" title="View Payment">
                                        <i class="ph ph-eye"></i>
                                    </button>

                                </td>

                            </tr>

                            <tr>

                                <td>3</td>

                                <td>
                                    <strong>#ORD-1003</strong>
                                </td>

                                <td>
                                    Priya Patel
                                    <br>
                                    <small class="text-muted">
                                        priya@email.com
                                    </small>
                                </td>

                                <td>
                                    TXN458965216
                                </td>

                                <td>
                                    <span class="badge bg-info">
                                        UPI
                                    </span>
                                </td>

                                <td>
                                    ₹2,350.00
                                </td>

                                <td>
                                    <span class="badge bg-danger">
                                        Failed
                                    </span>
                                </td>

                                <td>
                                    25 Jun 2026
                                </td>

                                <td>

                                    <button class="btn btn-info btn-sm" title="View Payment">
                                        <i class="ph ph-eye"></i>
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