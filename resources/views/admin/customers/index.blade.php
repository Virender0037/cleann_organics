<x-admin-layout title="Customers">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>
                <h4 class="mb-1">Customers</h4>
                <p class="text-muted mb-0">
                    Manage registered customers and their accounts
                </p>
            </div>

            <div>

                <button class="btn btn-light-secondary me-2">
                    <i class="ph ph-download-simple me-1"></i>
                    Export
                </button>

            </div>

        </div>

        <div class="mb-3">

            <a href="{{ route('admin.dashboard') }}">Dashboard</a>

            <span class="mx-2">›</span>

            <span>Customers</span>

        </div>

        <div class="card">

            <div class="card-header">

                <h5>Customer List</h5>

            </div>

            <div class="card-body">

                <div class="row mb-4">

                    <div class="col-md-4">

                        <input
                            type="text"
                            class="form-control"
                            placeholder="Search customer">

                    </div>

                    <div class="col-md-3">

                        <select class="form-select">

                            <option>All Status</option>

                            <option>Active</option>

                            <option>Inactive</option>

                            <option>Blocked</option>

                        </select>

                    </div>

                    <div class="col-md-3">

                        <select class="form-select">

                            <option>All Customers</option>

                            <option>New Customers</option>

                            <option>Returning Customers</option>

                        </select>

                    </div>

                </div>

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>

                        <tr>

                            <th>#</th>

                            <th>Customer</th>

                            <th>Phone</th>

                            <th>Total Orders</th>

                            <th>Total Spent</th>

                            <th>Last Order</th>

                            <th>Status</th>

                            <th width="140">Action</th>

                        </tr>

                        </thead>

                        <tbody>

                        <tr>

                            <td>1</td>

                            <td>

                                <strong>Rahul Sharma</strong>

                                <br>

                                <small class="text-muted">
                                    rahul@email.com
                                </small>

                            </td>

                            <td>

                                +91 9876543210

                            </td>

                            <td>

                                18

                            </td>

                            <td>

                                ₹18,450

                            </td>

                            <td>

                                26 Jun 2026

                            </td>

                            <td>

                                <span class="badge bg-success">
                                    Active
                                </span>

                            </td>

                            <td>

                                <a href="{{ route('admin.customers.show') }}"
                                   class="btn btn-sm btn-info">
                                    <i class="ph ph-eye"></i>
                                </a>

                                <a href="{{ route('admin.customers.edit') }}"
                                class="btn btn-sm btn-warning">
                                    <i class="ph ph-pencil-simple"></i>
                                </a>

                                <button class="btn btn-sm btn-danger"
                                        title="Delete Customer">
                                    <i class="ph ph-trash"></i>
                                </button>

                            </td>

                        </tr>

                        <tr>

                            <td>2</td>

                            <td>

                                <strong>Amit Verma</strong>

                                <br>

                                <small class="text-muted">
                                    amit@email.com
                                </small>

                            </td>

                            <td>

                                +91 9988776655

                            </td>

                            <td>

                                6

                            </td>

                            <td>

                                ₹6,250

                            </td>

                            <td>

                                21 Jun 2026

                            </td>

                            <td>

                                <span class="badge bg-success">
                                    Active
                                </span>

                            </td>

                            <td>

                                <a href="{{ route('admin.customers.show') }}"
                                   class="btn btn-sm btn-info">
                                    <i class="ph ph-eye"></i>
                                </a>

                                <a href="{{ route('admin.customers.edit') }}"
                                class="btn btn-sm btn-warning">
                                    <i class="ph ph-pencil-simple"></i>
                                </a>

                                <button class="btn btn-sm btn-danger"
                                        title="Delete Customer">
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