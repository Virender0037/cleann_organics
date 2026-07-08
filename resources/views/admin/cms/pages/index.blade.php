<x-admin-layout title="CMS Pages">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">CMS Pages</h4>
                <p class="text-muted mb-0">Manage website static pages like About, Privacy Policy and Terms</p>
            </div>

            <a href="{{ route('admin.cms.pages.create') }}" class="btn btn-primary">
                <i class="ph ph-plus me-1"></i>
                Add Page
            </a>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>CMS</span>
            <span class="mx-2">›</span>
            <span>Pages</span>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Page List</h5>
            </div>

            <div class="card-body">

                <div class="row mb-4">
                    <div class="col-md-4">
                        <input type="text" class="form-control" placeholder="Search page title or slug">
                    </div>

                    <div class="col-md-3">
                        <select class="form-select">
                            <option>All Status</option>
                            <option>Published</option>
                            <option>Draft</option>
                            <option>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Page Title</th>
                                <th>Slug</th>
                                <th>Status</th>
                                <th>Last Updated</th>
                                <th width="130">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>1</td>
                                <td><strong>About Us</strong></td>
                                <td>about-us</td>
                                <td><span class="badge bg-success">Published</span></td>
                                <td>08 Jul 2026</td>
                                <td>
                                    <a href="{{ route('admin.cms.pages.edit') }}" class="btn btn-sm btn-warning" title="Edit Page">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger" title="Delete Page">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </td>
                            </tr>

                            <tr>
                                <td>2</td>
                                <td><strong>Privacy Policy</strong></td>
                                <td>privacy-policy</td>
                                <td><span class="badge bg-success">Published</span></td>
                                <td>08 Jul 2026</td>
                                <td>
                                    <a href="{{ route('admin.cms.pages.edit') }}" class="btn btn-sm btn-warning" title="Edit Page">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger" title="Delete Page">
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