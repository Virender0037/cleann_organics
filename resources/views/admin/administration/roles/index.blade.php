<x-admin-layout title="Roles">
<main class="pc-container-edit">

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Roles</h4>
        <p class="text-muted mb-0">Manage admin roles and access levels</p>
    </div>

    <a href="{{ route('admin.administration.roles.create') }}" class="btn btn-primary">
        <i class="ph ph-plus me-1"></i> Add Role
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5>Role List</h5>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Role Name</th>
                        <th>Description</th>
                        <th>Users</th>
                        <th>Status</th>
                        <th width="130">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>1</td>
                        <td><strong>Super Admin</strong></td>
                        <td>Full access to all modules</td>
                        <td>2</td>
                        <td><span class="badge bg-success">Active</span></td>
                        <td>
                            <a href="{{ route('admin.administration.roles.edit') }}" class="btn btn-sm btn-warning" title="Edit Role">
                                <i class="ph ph-pencil-simple"></i>
                            </a>

                            <button class="btn btn-sm btn-danger" title="Delete Role">
                                <i class="ph ph-trash"></i>
                            </button>
                        </td>
                    </tr>

                    <tr>
                        <td>2</td>
                        <td><strong>Store Manager</strong></td>
                        <td>Can manage catalog, orders and customers</td>
                        <td>5</td>
                        <td><span class="badge bg-success">Active</span></td>
                        <td>
                            <a href="{{ route('admin.administration.roles.edit') }}" class="btn btn-sm btn-warning" title="Edit Role">
                                <i class="ph ph-pencil-simple"></i>
                            </a>

                            <button class="btn btn-sm btn-danger" title="Delete Role">
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