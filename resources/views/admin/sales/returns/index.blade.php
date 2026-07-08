<x-admin-layout title="Returns">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Returns</h4>
                <p class="text-muted mb-0">Manage customer return requests and refunds</p>
            </div>

            <a href="#" class="btn btn-light-secondary">
                <i class="ph ph-download-simple me-1"></i>
                Export
            </a>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>Sales</span>
            <span class="mx-2">›</span>
            <span>Returns</span>
        </div>

        <div class="card">

            <div class="card-header">
                <h5>Return Requests</h5>
            </div>

            <div class="card-body">

                <div class="row mb-4">

                    <div class="col-md-4">
                        <input class="form-control"
                               placeholder="Search Order / Return ID / Customer">
                    </div>

                    <div class="col-md-3">
                        <select class="form-select">
                            <option>All Status</option>
                            <option>Pending</option>
                            <option>Approved</option>
                            <option>Rejected</option>
                            <option>Refunded</option>
                        </select>
                    </div>

                </div>

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>

                        <tr>

                            <th>#</th>

                            <th>Return ID</th>

                            <th>Order</th>

                            <th>Customer</th>

                            <th>Reason</th>

                            <th>Refund</th>

                            <th>Status</th>

                            <th>Date</th>

                            <th width="130">Action</th>

                        </tr>

                        </thead>

                        <tbody>

                        <tr>

                            <td>1</td>

                            <td>#RET-1001</td>

                            <td>#ORD-1001</td>

                            <td>Rahul Sharma</td>

                            <td>Damaged Product</td>

                            <td>₹650</td>

                            <td>
                                <span class="badge bg-warning">
                                    Pending
                                </span>
                            </td>

                            <td>26 Jun 2026</td>

                            <td>

                                <a href="{{ route('admin.sales.returns.show') }}"
                                   class="btn btn-sm btn-info">
                                    <i class="ph ph-eye"></i>
                                </a>

                            </td>

                        </tr>

                        <tr>

                            <td>2</td>

                            <td>#RET-1002</td>

                            <td>#ORD-1005</td>

                            <td>Amit Verma</td>

                            <td>Wrong Item</td>

                            <td>₹299</td>

                            <td>
                                <span class="badge bg-success">
                                    Refunded
                                </span>
                            </td>

                            <td>25 Jun 2026</td>

                            <td>

                                <a href="{{ route('admin.sales.returns.show') }}"
                                   class="btn btn-sm btn-info">
                                    <i class="ph ph-eye"></i>
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