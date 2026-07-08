<x-admin-layout title="Add Coupon">
    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Add Coupon</h4>
                <p class="text-muted mb-0">Create a new discount coupon</p>
            </div>

            <a href="{{ route('admin.sales.coupons.index') }}" class="btn btn-light">
                <i class="ph ph-arrow-left me-1"></i>
                Back
            </a>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>Sales</span>
            <span class="mx-2">›</span>
            <a href="{{ route('admin.sales.coupons.index') }}">Coupons</a>
            <span class="mx-2">›</span>
            <span>Add Coupon</span>
        </div>

        <form action="#" method="POST">
            @csrf

            <div class="card">
                <div class="card-header">
                    <h5>Coupon Information</h5>
                </div>

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Coupon Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Welcome Offer">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Coupon Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" placeholder="WELCOME10">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Discount Type</label>
                            <select name="discount_type" class="form-select">
                                <option value="percentage">Percentage</option>
                                <option value="fixed">Fixed Amount</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Discount Value <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="discount_value" class="form-control" placeholder="10">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Minimum Order Amount</label>
                            <input type="number" step="0.01" name="minimum_order_amount" class="form-control" placeholder="500">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Maximum Discount Amount</label>
                            <input type="number" step="0.01" name="maximum_discount_amount" class="form-control" placeholder="100">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Usage Limit</label>
                            <input type="number" name="usage_limit" class="form-control" placeholder="100">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Usage Per Customer</label>
                            <input type="number" name="usage_per_customer" class="form-control" placeholder="1">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Expiry Date</label>
                            <input type="date" name="expiry_date" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Coupon description"></textarea>
                        </div>

                    </div>

                    <div class="text-end">
                        <a href="{{ route('admin.sales.coupons.index') }}" class="btn btn-light">
                            Cancel
                        </a>

                        <button type="submit" class="btn btn-primary">
                            <i class="ph ph-floppy-disk me-1"></i>
                            Create Coupon
                        </button>
                    </div>
                </div>
            </div>

        </form>

    </main>
</x-admin-layout>