<x-admin-layout title="Add Shipping Method">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Add Shipping Method</h4>
                <p class="text-muted mb-0">Create a new delivery method</p>
            </div>

            <a href="{{ route('admin.shipping.methods.index') }}" class="btn btn-light">
                <i class="ph ph-arrow-left me-1"></i>
                Back
            </a>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>Shipping</span>
            <span class="mx-2">›</span>
            <a href="{{ route('admin.shipping.methods.index') }}">Methods</a>
            <span class="mx-2">›</span>
            <span>Add Method</span>
        </div>

        <form action="#" method="POST">
            @csrf

            <div class="card">
                <div class="card-header">
                    <h5>Method Information</h5>
                </div>

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Method Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Standard Shipping">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Method Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" placeholder="standard">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estimated Delivery</label>
                            <input type="text" name="estimated_delivery" class="form-control" placeholder="3 - 5 Days">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
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
                            <textarea name="description" class="form-control" rows="4" placeholder="Shipping method description"></textarea>
                        </div>

                    </div>

                    <div class="text-end mt-4">
                        <a href="{{ route('admin.shipping.methods.index') }}" class="btn btn-light">
                            Cancel
                        </a>

                        <button type="submit" class="btn btn-primary">
                            <i class="ph ph-floppy-disk me-1"></i>
                            Create Method
                        </button>
                    </div>
                </div>
            </div>

        </form>

    </main>

</x-admin-layout>