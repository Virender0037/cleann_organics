<x-admin-layout title="Tax Rates">
 <main class="pc-container-edit">    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Tax Rates</h4>
            <p class="text-muted mb-0">Manage GST / tax rates for products</p>
        </div>

        <div>
            <a href="{{ route('admin.catalog.tax-rates.create') }}" class="btn btn-primary">
                <i class="ph ph-plus me-1"></i>
                Add Tax Rate
            </a>
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <span class="mx-2">›</span>
        <span>Catalog</span>
        <span class="mx-2">›</span>
        <span>Tax Rates</span>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Tax Rate List</h5>
        </div>

        <div class="card-body">

            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Search tax rate">
                </div>

                <div class="col-md-3">
                    <select class="form-select">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tax Name</th>
                            <th>Rate</th>
                            <th>Status</th>
                            <th>Products</th>
                            <th width="130">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>1</td>

                            <td>
                                <strong>GST 5%</strong>
                            </td>

                            <td>5.00%</td>

                            <td>
                                <span class="badge bg-success">Active</span>
                            </td>

                            <td>12</td>

                            <td>
                                <a href="{{ route('admin.catalog.tax-rates.edit') }}" class="btn btn-sm btn-info" title="Edit Tax Rate">
                                    <i class="ph ph-pencil-simple"></i>
                                </a>

                                <a href="#" class="btn btn-sm btn-danger" title="Delete Tax Rate">
                                    <i class="ph ph-trash"></i>
                                </a>
                            </td>
                        </tr>

                        <tr>
                            <td>2</td>

                            <td>
                                <strong>GST 12%</strong>
                            </td>

                            <td>12.00%</td>

                            <td>
                                <span class="badge bg-success">Active</span>
                            </td>

                            <td>8</td>

                            <td>
                                <a href="#" class="btn btn-sm btn-info" title="Edit Tax Rate">
                                    <i class="ph ph-pencil-simple"></i>
                                </a>

                                <a href="#" class="btn btn-sm btn-danger" title="Delete Tax Rate">
                                    <i class="ph ph-trash"></i>
                                </a>
                            </td>
                        </tr>

                        <tr>
                            <td>3</td>

                            <td>
                                <strong>GST 18%</strong>
                            </td>

                            <td>18.00%</td>

                            <td>
                                <span class="badge bg-success">Active</span>
                            </td>

                            <td>5</td>

                            <td>
                                <a href="#" class="btn btn-sm btn-info" title="Edit Tax Rate">
                                    <i class="ph ph-pencil-simple"></i>
                                </a>

                                <a href="#" class="btn btn-sm btn-danger" title="Delete Tax Rate">
                                    <i class="ph ph-trash"></i>
                                </a>
                            </td>
                        </tr>
                    </tbody>

                </table>
            </div>

        </div>
    </div>
</main>
</x-admin-layout>