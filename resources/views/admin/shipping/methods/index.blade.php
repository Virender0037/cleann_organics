<x-admin-layout title="Shipping Methods">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Shipping Methods</h4>
                <p class="text-muted mb-0">Manage available delivery methods</p>
            </div>

            <a href="{{ route('admin.shipping.methods.create') }}" class="btn btn-primary">
                <i class="ph ph-plus me-1"></i>
                Add Method
            </a>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>Shipping</span>
            <span class="mx-2">›</span>
            <span>Methods</span>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Method List</h5>
            </div>

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <input type="text" class="form-control" placeholder="Search method">
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
                                <th>Method Name</th>
                                <th>Code</th>
                                <th>Estimated Delivery</th>
                                <th>Sort Order</th>
                                <th>Status</th>
                                <th width="130">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>1</td>
                                <td><strong>Standard Shipping</strong></td>
                                <td>standard</td>
                                <td>3 - 5 Days</td>
                                <td>1</td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td>
                                    <a href="{{ route('admin.shipping.methods.edit') }}" class="btn btn-sm btn-warning" title="Edit Method">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger" title="Delete Method">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </td>
                            </tr>

                            <tr>
                                <td>2</td>
                                <td><strong>Express Shipping</strong></td>
                                <td>express</td>
                                <td>1 - 2 Days</td>
                                <td>2</td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td>
                                    <a href="{{ route('admin.shipping.methods.edit') }}" class="btn btn-sm btn-warning" title="Edit Method">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger" title="Delete Method">
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