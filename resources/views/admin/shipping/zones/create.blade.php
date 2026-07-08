<x-admin-layout title="Add Shipping Zone">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Add Shipping Zone</h4>
                <p class="text-muted mb-0">Create a new delivery zone</p>
            </div>

            <a href="{{ route('admin.shipping.zones.index') }}" class="btn btn-light">
                <i class="ph ph-arrow-left me-1"></i>
                Back
            </a>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>Shipping</span>
            <span class="mx-2">›</span>
            <a href="{{ route('admin.shipping.zones.index') }}">Zones</a>
            <span class="mx-2">›</span>
            <span>Add Zone</span>
        </div>

        <form action="#" method="POST">
            @csrf

            <div class="card">
                <div class="card-header">
                    <h5>Zone Information</h5>
                </div>

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Zone Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Gujarat Zone">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" placeholder="Gujarat">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" placeholder="Ahmedabad">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="pincode" class="form-control" placeholder="380015">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                    </div>

                    <div class="text-end mt-4">
                        <a href="{{ route('admin.shipping.zones.index') }}" class="btn btn-light">
                            Cancel
                        </a>

                        <button type="submit" class="btn btn-primary">
                            <i class="ph ph-floppy-disk me-1"></i>
                            Create Zone
                        </button>
                    </div>
                </div>
            </div>

        </form>

    </main>

</x-admin-layout>