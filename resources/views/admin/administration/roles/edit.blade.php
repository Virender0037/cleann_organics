<x-admin-layout title="Edit Role">
<main class="pc-container-edit">

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Edit Role</h4>
        <p class="text-muted mb-0">Update role and permissions</p>
    </div>

    <a href="{{ route('admin.administration.roles.index') }}" class="btn btn-light">
        <i class="ph ph-arrow-left me-1"></i> Back
    </a>
</div>

<form action="#" method="POST">
@csrf
@method('PUT')

<div class="card mb-4">
    <div class="card-header">
        <h5>Role Information</h5>
    </div>

    <div class="card-body">
        <div class="row">

            <div class="col-md-6 mb-3">
                <label class="form-label">Role Name</label>
                <input type="text" name="name" class="form-control" value="Store Manager">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option selected>Active</option>
                    <option>Inactive</option>
                </select>
            </div>

            <div class="col-md-12 mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">Can manage catalog, orders and customers.</textarea>
            </div>

        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5>Permissions</h5>
    </div>

    <div class="card-body">

        @foreach (['Catalog', 'Inventory', 'Sales', 'Customers', 'Shipping', 'CMS', 'Reports', 'Administration', 'Settings'] as $module)
            <div class="border rounded p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>{{ $module }}</strong>
                    <div>
                        <input type="checkbox" class="form-check-input me-1" checked>
                        <label class="form-check-label">Full Access</label>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <input type="checkbox" class="form-check-input me-1" checked>
                        <label class="form-check-label">View</label>
                    </div>

                    <div class="col-md-3">
                        <input type="checkbox" class="form-check-input me-1" checked>
                        <label class="form-check-label">Create</label>
                    </div>

                    <div class="col-md-3">
                        <input type="checkbox" class="form-check-input me-1" checked>
                        <label class="form-check-label">Edit</label>
                    </div>

                    <div class="col-md-3">
                        <input type="checkbox" class="form-check-input me-1">
                        <label class="form-check-label">Delete</label>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="text-end mt-4">
            <a href="{{ route('admin.administration.roles.index') }}" class="btn btn-light">
                Cancel
            </a>

            <button class="btn btn-primary">
                <i class="ph ph-floppy-disk me-1"></i>
                Update Role
            </button>
        </div>

    </div>
</div>

</form>

</main>
</x-admin-layout>