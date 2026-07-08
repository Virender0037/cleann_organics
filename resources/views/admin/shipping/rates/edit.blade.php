<x-admin-layout title="Edit Shipping Rate">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Edit Shipping Rate</h4>
                <p class="text-muted mb-0">Update shipping charge details</p>
            </div>

            <a href="{{ route('admin.shipping.rates.index') }}" class="btn btn-light">
                <i class="ph ph-arrow-left me-1"></i>
                Back
            </a>
        </div>

        <form action="#" method="POST">
            @csrf
            @method('PUT')

            <div class="card">
                <div class="card-header">
                    <h5>Rate Information</h5>
                </div>

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Shipping Zone <span class="text-danger">*</span></label>
                            <select name="shipping_zone_id" class="form-select">
                                <option value="">Select Zone</option>
                                <option value="1" selected>Gujarat Zone</option>
                                <option value="2">Maharashtra Zone</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Min Weight</label>
                            <input type="number" step="0.01" name="min_weight" class="form-control" value="0.00">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Max Weight</label>
                            <input type="number" step="0.01" name="max_weight" class="form-control" value="2.00">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Shipping Charge <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="shipping_charge" class="form-control" value="50.00">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Free Shipping Above</label>
                            <input type="number" step="0.01" name="free_shipping_above" class="form-control" value="999.00">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                    </div>

                    <div class="text-end mt-4">
                        <a href="{{ route('admin.shipping.rates.index') }}" class="btn btn-light">Cancel</a>

                        <button type="submit" class="btn btn-primary">
                            <i class="ph ph-floppy-disk me-1"></i>
                            Update Rate
                        </button>
                    </div>
                </div>
            </div>

        </form>

    </main>

</x-admin-layout>