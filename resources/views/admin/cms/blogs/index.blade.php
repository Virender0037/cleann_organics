<x-admin-layout title="Blogs">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Blogs</h4>
                <p class="text-muted mb-0">Manage blog posts and articles</p>
            </div>

            <a href="{{ route('admin.cms.blogs.create') }}" class="btn btn-primary">
                <i class="ph ph-plus me-1"></i>
                Add Blog
            </a>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>CMS</span>
            <span class="mx-2">›</span>
            <span>Blogs</span>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Blog List</h5>
            </div>

            <div class="card-body">

                <div class="row mb-4">
                    <div class="col-md-4">
                        <input type="text" class="form-control" placeholder="Search blog title or slug">
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
                                <th>Image</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Published On</th>
                                <th width="130">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>
                                    <img src="https://placehold.co/60x60"
                                         class="rounded border"
                                         width="60"
                                         height="60"
                                         alt="Blog">
                                </td>
                                <td>
                                    <strong>Benefits of Organic Food</strong>
                                    <br>
                                    <small class="text-muted">benefits-of-organic-food</small>
                                </td>
                                <td>Health</td>
                                <td><span class="badge bg-success">Published</span></td>
                                <td>08 Jul 2026</td>
                                <td>
                                    <a href="{{ route('admin.cms.blogs.edit') }}" class="btn btn-sm btn-warning" title="Edit Blog">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger" title="Delete Blog">
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