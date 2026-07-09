<x-admin-layout title="Edit User">
<main class="pc-container-edit">

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Edit User</h4>
        <p class="text-muted mb-0">Update admin user</p>
    </div>

    <a href="{{ route('admin.administration.users.index') }}" class="btn btn-light">
        <i class="ph ph-arrow-left me-1"></i> Back
    </a>
</div>

<form action="#" method="POST">
@csrf
@method('PUT')

<div class="card">
    <div class="card-header"><h5>User Information</h5></div>

    <div class="card-body">
        <div class="row">

            <div class="col-md-6 mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="Admin User">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="admin@example.com">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select">
                    <option selected>Super Admin</option>
                    <option>Manager</option>
                    <option>Staff</option>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option selected>Active</option>
                    <option>Inactive</option>
                </select>
            </div>

        </div>

        <div class="text-end mt-4">
            <a href="{{ route('admin.administration.users.index') }}" class="btn btn-light">Cancel</a>
            <button class="btn btn-primary">
                <i class="ph ph-floppy-disk me-1"></i> Update User
            </button>
        </div>
    </div>
</div>

</form>

</main>
</x-admin-layout>