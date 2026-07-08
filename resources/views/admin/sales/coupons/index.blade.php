<x-admin-layout title="Coupons">
    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Coupons</h4>
                <p class="text-muted mb-0">Manage discount coupons and promotional offers</p>
            </div>

            <a href="{{ route('admin.sales.coupons.create') }}" class="btn btn-primary">
                <i class="ph ph-plus me-1"></i>
                Add Coupon
            </a>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>Sales</span>
            <span class="mx-2">›</span>
            <span>Coupons</span>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Coupon List</h5>
            </div>

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <input type="text" class="form-control" placeholder="Search coupon code or name">
                    </div>

                    <div class="col-md-3">
                        <select class="form-select">
                            <option>All Types</option>
                            <option>Percentage</option>
                            <option>Fixed Amount</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select class="form-select">
                            <option>All Status</option>
                            <option>Active</option>
                            <option>Inactive</option>
                            <option>Expired</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Coupon</th>
                                <th>Code</th>
                                <th>Type</th>
                                <th>Discount</th>
                                <th>Min Order</th>
                                <th>Usage</th>
                                <th>Validity</th>
                                <th>Status</th>
                                <th width="130">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>1</td>
                                <td><strong>Welcome Offer</strong></td>
                                <td><span class="badge bg-light text-dark">WELCOME10</span></td>
                                <td>Percentage</td>
                                <td>10%</td>
                                <td>₹500</td>
                                <td>25 / 100</td>
                                <td>01 Jun 2026 - 30 Jun 2026</td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td>
                                    <a href="{{ route('admin.sales.coupons.edit') }}" class="btn btn-sm btn-info" title="Edit Coupon">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger" title="Delete Coupon">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </td>
                            </tr>

                            <tr>
                                <td>2</td>
                                <td><strong>Flat Discount</strong></td>
                                <td><span class="badge bg-light text-dark">FLAT100</span></td>
                                <td>Fixed Amount</td>
                                <td>₹100</td>
                                <td>₹999</td>
                                <td>10 / 50</td>
                                <td>01 Jun 2026 - 15 Jul 2026</td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td>
                                    <a href="{{ route('admin.sales.coupons.edit') }}" class="btn btn-sm btn-info" title="Edit Coupon">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger" title="Delete Coupon">
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