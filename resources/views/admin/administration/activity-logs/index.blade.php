<x-admin-layout title="Activity Logs">
<main class="pc-container-edit">

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Activity Logs</h4>
        <p class="text-muted mb-0">Track admin user actions across the system</p>
    </div>

    <button class="btn btn-light-secondary">
        <i class="ph ph-download-simple me-1"></i>
        Export
    </button>
</div>

<div class="card">
    <div class="card-header">
        <h5>Activity Log List</h5>
    </div>

    <div class="card-body">

        <div class="row mb-4">
            <div class="col-md-4">
                <input type="text" class="form-control" placeholder="Search user, module or action">
            </div>

            <div class="col-md-3">
                <select class="form-select">
                    <option>All Modules</option>
                    <option>Catalog</option>
                    <option>Sales</option>
                    <option>Customers</option>
                    <option>CMS</option>
                    <option>Settings</option>
                </select>
            </div>

            <div class="col-md-3">
                <select class="form-select">
                    <option>All Actions</option>
                    <option>Created</option>
                    <option>Updated</option>
                    <option>Deleted</option>
                    <option>Login</option>
                    <option>Exported</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Module</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>IP Address</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>1</td>
                        <td>
                            <strong>Admin User</strong><br>
                            <small class="text-muted">admin@example.com</small>
                        </td>
                        <td>Products</td>
                        <td><span class="badge bg-primary">Updated</span></td>
                        <td>Updated product Organic Honey</td>
                        <td>127.0.0.1</td>
                        <td>09 Jul 2026, 11:30 AM</td>
                    </tr>

                    <tr>
                        <td>2</td>
                        <td>
                            <strong>Store Manager</strong><br>
                            <small class="text-muted">manager@example.com</small>
                        </td>
                        <td>Orders</td>
                        <td><span class="badge bg-success">Created</span></td>
                        <td>Created manual order #ORD-1005</td>
                        <td>127.0.0.1</td>
                        <td>09 Jul 2026, 10:45 AM</td>
                    </tr>

                    <tr>
                        <td>3</td>
                        <td>
                            <strong>Admin User</strong><br>
                            <small class="text-muted">admin@example.com</small>
                        </td>
                        <td>Authentication</td>
                        <td><span class="badge bg-info">Login</span></td>
                        <td>User logged into admin panel</td>
                        <td>127.0.0.1</td>
                        <td>09 Jul 2026, 09:15 AM</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</div>

</main>
</x-admin-layout>