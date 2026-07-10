<x-admin-layout title="Edit Coupon">
    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Edit Coupon</h4>
                <p class="text-muted mb-0">Update discount coupon details</p>
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
            <span>Edit Coupon</span>
        </div>

        <form action="{{ route('admin.sales.coupons.update', $coupon) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card">
                <div class="card-header">
                    <h5>Coupon Information</h5>
                </div>

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Coupon Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $coupon->code) }}">
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Discount Type</label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror">
                                <option value="percentage" @selected(old('type', $coupon->type) === 'percentage')>Percentage</option>
                                <option value="fixed" @selected(old('type', $coupon->type) === 'fixed')>Fixed Amount</option>
                            </select>
                            @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Discount Value <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="value" class="form-control @error('value') is-invalid @enderror" value="{{ old('value', $coupon->value) }}">
                            @error('value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Minimum Order Amount</label>
                            <input type="number" step="0.01" name="minimum_order_amount" class="form-control @error('minimum_order_amount') is-invalid @enderror" value="{{ old('minimum_order_amount', $coupon->minimum_order_amount) }}">
                            @error('minimum_order_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Maximum Discount Amount</label>
                            <input type="number" step="0.01" name="maximum_discount_amount" class="form-control @error('maximum_discount_amount') is-invalid @enderror" value="{{ old('maximum_discount_amount', $coupon->maximum_discount_amount) }}">
                            @error('maximum_discount_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Usage Limit</label>
                            <input type="number" name="usage_limit" class="form-control @error('usage_limit') is-invalid @enderror" value="{{ old('usage_limit', $coupon->usage_limit) }}" placeholder="Leave blank for unlimited">
                            @error('usage_limit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $coupon->start_date?->format('Y-m-d')) }}">
                            @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', $coupon->end_date?->format('Y-m-d')) }}">
                            @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active" @selected(old('status', $coupon->status) === 'active')>Active</option>
                                <option value="inactive" @selected(old('status', $coupon->status) === 'inactive')>Inactive</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Used Count</label>
                            <input type="text" class="form-control" value="{{ $coupon->used_count }}" disabled>
                        </div>

                    </div>

                    <div class="text-end">
                        <a href="{{ route('admin.sales.coupons.index') }}" class="btn btn-light">
                            Cancel
                        </a>

                        <button type="submit" class="btn btn-primary">
                            <i class="ph ph-floppy-disk me-1"></i>
                            Update Coupon
                        </button>
                    </div>
                </div>
            </div>

        </form>

    </main>
</x-admin-layout>