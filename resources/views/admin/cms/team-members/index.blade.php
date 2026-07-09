<x-admin-layout title="Team Members">
    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Team Members</h4>
                <p class="text-muted mb-0">Manage team profiles displayed on the website</p>
            </div>

            <a href="{{ route('admin.cms.team-members.create') }}" class="btn btn-primary">
                <i class="ph ph-plus me-1"></i>
                Add Team Member
            </a>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>CMS</span>
            <span class="mx-2">›</span>
            <span>Team Members</span>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Team Member List</h5>
            </div>

            <div class="card-body">

                <div class="row mb-4">
                    <div class="col-md-4">
                        <input type="text" class="form-control" placeholder="Search name or designation">
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
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Designation</th>
                                <th>Email</th>
                                <th>Sort Order</th>
                                <th>Status</th>
                                <th width="130">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>1</td>

                                <td>
                                    <img src="https://placehold.co/60x60"
                                         class="rounded-circle border"
                                         width="60"
                                         height="60"
                                         alt="Team Member">
                                </td>

                                <td>
                                    <strong>Rahul Sharma</strong>
                                    <br>
                                    <small class="text-muted">Operations Team</small>
                                </td>

                                <td>Operations Manager</td>

                                <td>rahul@example.com</td>

                                <td>1</td>

                                <td>
                                    <span class="badge bg-success">Active</span>
                                </td>

                                <td>
                                    <a href="{{ route('admin.cms.team-members.edit') }}"
                                       class="btn btn-sm btn-warning"
                                       title="Edit Team Member">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger" title="Delete Team Member">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </td>
                            </tr>

                            <tr>
                                <td>2</td>

                                <td>
                                    <img src="https://placehold.co/60x60"
                                         class="rounded-circle border"
                                         width="60"
                                         height="60"
                                         alt="Team Member">
                                </td>

                                <td>
                                    <strong>Priya Patel</strong>
                                    <br>
                                    <small class="text-muted">Marketing Team</small>
                                </td>

                                <td>Marketing Head</td>

                                <td>priya@example.com</td>

                                <td>2</td>

                                <td>
                                    <span class="badge bg-success">Active</span>
                                </td>

                                <td>
                                    <a href="{{ route('admin.cms.team-members.edit') }}"
                                       class="btn btn-sm btn-warning"
                                       title="Edit Team Member">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger" title="Delete Team Member">
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