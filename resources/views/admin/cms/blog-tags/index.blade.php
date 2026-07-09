<x-admin-layout title="Blog Tags">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Blog Tags</h4>
                <p class="text-muted mb-0">Manage blog tags used for articles</p>
            </div>

            <a href="{{ route('admin.cms.blog-tags.create') }}" class="btn btn-primary">
                <i class="ph ph-plus me-1"></i>
                Add Tag
            </a>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>CMS</span>
            <span class="mx-2">›</span>
            <span>Blog Tags</span>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Tag List</h5>
            </div>

            <div class="card-body">

                <div class="row mb-4">
                    <div class="col-md-4">
                        <input type="text" class="form-control" placeholder="Search tag">
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
                                <th>Tag Name</th>
                                <th>Slug</th>
                                <th>Blogs</th>
                                <th>Status</th>
                                <th width="130">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>1</td>
                                <td><strong>Organic</strong></td>
                                <td>organic</td>
                                <td>15 Blogs</td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td>
                                    <a href="{{ route('admin.cms.blog-tags.edit') }}"
                                       class="btn btn-sm btn-warning"
                                       title="Edit Tag">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger" title="Delete Tag">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </td>
                            </tr>

                            <tr>
                                <td>2</td>
                                <td><strong>Healthy Food</strong></td>
                                <td>healthy-food</td>
                                <td>9 Blogs</td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td>
                                    <a href="{{ route('admin.cms.blog-tags.edit') }}"
                                       class="btn btn-sm btn-warning"
                                       title="Edit Tag">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger" title="Delete Tag">
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