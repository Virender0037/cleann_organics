<x-admin-layout title="Add Blog Tag">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Add Blog Tag</h4>
                <p class="text-muted mb-0">Create a new tag for blog articles</p>
            </div>

            <a href="{{ route('admin.cms.blog-tags.index') }}" class="btn btn-light">
                <i class="ph ph-arrow-left me-1"></i>
                Back
            </a>
        </div>

        <form action="#" method="POST">
            @csrf

            <div class="card">
                <div class="card-header">
                    <h5>Tag Information</h5>
                </div>

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tag Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Organic">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Slug <span class="text-danger">*</span></label>
                            <input type="text" name="slug" class="form-control" placeholder="organic">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                    </div>

                    <div class="text-end mt-4">
                        <a href="{{ route('admin.cms.blog-tags.index') }}" class="btn btn-light">
                            Cancel
                        </a>

                        <button type="submit" class="btn btn-primary">
                            <i class="ph ph-floppy-disk me-1"></i>
                            Create Tag
                        </button>
                    </div>
                </div>
            </div>

        </form>

    </main>

</x-admin-layout>