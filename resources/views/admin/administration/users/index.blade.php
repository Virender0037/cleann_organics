<x-admin-layout title="Users">
<main class="pc-container-edit">

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Users</h4>
        <p class="text-muted mb-0">Manage admin users</p>
    </div>

    <a href="{{ route('admin.administration.users.create') }}" class="btn btn-primary">
        <i class="ph ph-plus me-1"></i> Add User
    </a>
</div>

<div class="card">
    <div class="card-header"><h5>User List</h5></div>

    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-4">
                <input type="text" class="form-control" placeholder="Search user">
            </div>
            <div class="col-md-3">
                <select class="form-select">
                    <option>All Roles</option>
                    <option>Super Admin</option>
                    <option>Manager</option>
                    <option>Staff</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select">
                    <option>All Status</option>
                    <option>Active</option>
                    <option>Inactive</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Last Login</th>
                        <th>Status</th>
                        <th width="130">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>1</td>
                        <td><strong>Admin User</strong></td>
                        <td>admin@example.com</td>
                        <td>Super Admin</td>
                        <td>09 Jul 2026</td>
                        <td><span class="badge bg-success">Active</span></td>
                        <td>
                            <a href="{{ route('admin.administration.users.edit') }}" class="btn btn-sm btn-warning">
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