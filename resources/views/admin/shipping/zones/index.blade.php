<x-admin-layout title="Shipping Zones">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Shipping Zones</h4>
                <p class="text-muted mb-0">Manage delivery zones by state, city and pincode</p>
            </div>

            <a href="{{ route('admin.shipping.zones.create') }}" class="btn btn-primary">
                <i class="ph ph-plus me-1"></i>
                Add Zone
            </a>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>Shipping</span>
            <span class="mx-2">›</span>
            <span>Zones</span>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Zone List</h5>
            </div>

            <div class="card-body">

                <div class="row mb-4">
                    <div class="col-md-4">
                        <input type="text" class="form-control" placeholder="Search zone, state, city or pincode">
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
                                <th>Zone Name</th>
                                <th>State</th>
                                <th>City</th>
                                <th>Pincode</th>
                                <th>Status</th>
                                <th width="130">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>1</td>
                                <td><strong>Gujarat Zone</strong></td>
                                <td>Gujarat</td>
                                <td>Ahmedabad</td>
                                <td>380015</td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td>
                                    <a href="{{ route('admin.shipping.zones.edit') }}" class="btn btn-sm btn-warning" title="Edit Zone">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger" title="Delete Zone">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </td>
                            </tr>

                            <tr>
                                <td>2</td>
                                <td><strong>Maharashtra Zone</strong></td>
                                <td>Maharashtra</td>
                                <td>Mumbai</td>
                                <td>400001</td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td>
                                    <a href="{{ route('admin.shipping.zones.edit') }}" class="btn btn-sm btn-warning" title="Edit Zone">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger" title="Delete Zone">
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