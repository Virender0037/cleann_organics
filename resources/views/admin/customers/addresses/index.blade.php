<x-admin-layout title="Customer Addresses">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Customer Addresses</h4>
                <p class="text-muted mb-0">
                    Manage customer billing and shipping addresses
                </p>
            </div>

            <a href="{{ route('admin.customers.show') }}" class="btn btn-light">
                <i class="ph ph-arrow-left me-1"></i>
                Back
            </a>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <a href="{{ route('admin.customers.index') }}">Customers</a>
            <span class="mx-2">›</span>
            <span>Addresses</span>
        </div>

        <div class="row">

            <!-- Billing Address -->
            <div class="col-lg-6">

                <div class="card mb-4">

                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="ph ph-receipt me-2"></i>
                            Billing Address
                        </h5>

                       <a href="{{ route('admin.customers.addresses.edit') }}"
                        class="btn btn-sm btn-warning"
                        title="Edit Address">
                            <i class="ph ph-pencil-simple"></i>
                       </a>
                    </div>

                    <div class="card-body">

                        <p class="mb-1"><strong>Rahul Sharma</strong></p>

                        <p class="mb-1">
                            101, ABC Apartment
                        </p>

                        <p class="mb-1">
                            Satellite Road
                        </p>

                        <p class="mb-1">
                            Ahmedabad, Gujarat
                        </p>

                        <p class="mb-1">
                            India - 380015
                        </p>

                        <p class="mb-0">
                            Mobile : +91 9876543210
                        </p>

                    </div>

                </div>

            </div>

            <!-- Shipping Address -->
            <div class="col-lg-6">

                <div class="card mb-4">

                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="ph ph-truck me-2"></i>
                            Shipping Address
                        </h5>

                        <a href="{{ route('admin.customers.addresses.edit') }}"
                        class="btn btn-sm btn-warning"
                        title="Edit Address">
                            <i class="ph ph-pencil-simple"></i>
                        </a>
                    </div>

                    <div class="card-body">

                        <p class="mb-1"><strong>Rahul Sharma</strong></p>

                        <p class="mb-1">
                            B-204 Green Residency
                        </p>

                        <p class="mb-1">
                            SG Highway
                        </p>

                        <p class="mb-1">
                            Ahmedabad, Gujarat
                        </p>

                        <p class="mb-1">
                            India - 380054
                        </p>

                        <p class="mb-0">
                            Mobile : +91 9876543210
                        </p>

                    </div>

                </div>

            </div>

        </div>

        <!-- Additional Addresses -->

        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">

                <h5 class="mb-0">Saved Addresses</h5>

                <a href="{{ route('admin.customers.addresses.create') }}"
                class="btn btn-primary btn-sm">
                    <i class="ph ph-plus me-1"></i>
                    Add Address
                </a>

            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>

                        <tr>

                            <th>#</th>

                            <th>Address Type</th>

                            <th>Recipient</th>

                            <th>City</th>

                            <th>State</th>

                            <th>Country</th>

                            <th>Default</th>

                            <th width="120">Action</th>

                        </tr>

                        </thead>

                        <tbody>

                        <tr>

                            <td>1</td>

                            <td>
                                <span class="badge bg-primary">
                                    Home
                                </span>
                            </td>

                            <td>Rahul Sharma</td>

                            <td>Ahmedabad</td>

                            <td>Gujarat</td>

                            <td>India</td>

                            <td>
                                <span class="badge bg-success">
                                    Yes
                                </span>
                            </td>

                            <td>

                                <a href="{{ route('admin.customers.addresses.edit') }}"
                                class="btn btn-sm btn-warning"
                                title="Edit Address">
                                    <i class="ph ph-pencil-simple"></i>
                                </a>

                                <button class="btn btn-sm btn-danger" title="Delete">
                                    <i class="ph ph-trash"></i>
                                </button>

                            </td>

                        </tr>

                        <tr>

                            <td>2</td>

                            <td>
                                <span class="badge bg-info">
                                    Office
                                </span>
                            </td>

                            <td>Rahul Sharma</td>

                            <td>Ahmedabad</td>

                            <td>Gujarat</td>

                            <td>India</td>

                            <td>
                                <span class="badge bg-secondary">
                                    No
                                </span>
                            </td>

                            <td>

                                <a href="{{ route('admin.customers.addresses.edit') }}"
                                class="btn btn-sm btn-warning"
                                title="Edit Address">
                                    <i class="ph ph-pencil-simple"></i>
                                </a>

                                <button class="btn btn-sm btn-danger">
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