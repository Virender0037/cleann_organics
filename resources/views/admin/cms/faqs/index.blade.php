<x-admin-layout title="FAQs">
    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">FAQs</h4>
                <p class="text-muted mb-0">Manage frequently asked questions</p>
            </div>

            <a href="{{ route('admin.cms.faqs.create') }}" class="btn btn-primary">
                <i class="ph ph-plus me-1"></i>
                Add FAQ
            </a>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>CMS</span>
            <span class="mx-2">›</span>
            <span>FAQs</span>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>FAQ List</h5>
            </div>

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <input type="text" class="form-control" placeholder="Search question">
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
                                <th>Question</th>
                                <th>Category</th>
                                <th>Sort Order</th>
                                <th>Status</th>
                                <th width="130">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>1</td>
                                <td><strong>How can I track my order?</strong></td>
                                <td>Orders</td>
                                <td>1</td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td>
                                    <a href="{{ route('admin.cms.faqs.edit') }}" class="btn btn-sm btn-warning">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </td>
                            </tr>

                            <tr>
                                <td>2</td>
                                <td><strong>What is your return policy?</strong></td>
                                <td>Returns</td>
                                <td>2</td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td>
                                    <a href="{{ route('admin.cms.faqs.edit') }}" class="btn btn-sm btn-warning">
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