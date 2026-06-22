<x-admin-layout title="Edit Category">
    <main class="pc-container-edit">    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Edit Category</h4>
            <p class="text-muted mb-0">Update product category details</p>
        </div>

        <a href="{{ route('admin.catalog.categories.index') }}" class="btn btn-light">
            <i class="ph ph-arrow-left me-1"></i>
            Back
        </a>
    </div>

    <div class="mb-3">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <span class="mx-2">›</span>
        <span>Catalog</span>
        <span class="mx-2">›</span>
        <a href="{{ route('admin.catalog.categories.index') }}">Categories</a>
        <span class="mx-2">›</span>
        <span>Edit Category</span>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Edit Category</h5>
        </div>

        <div class="card-body">
            <form action="#" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="Organic Oils" placeholder="Enter category name">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Slug <span class="text-danger">*</span></label>
                        <input type="text" name="slug" class="form-control" value="organic-oils" placeholder="category-slug">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Image</label>
                        <input type="file" name="image" class="form-control">

                        <div class="mt-2">
                            <img src="https://placehold.co/80x80" width="80" height="80" class="rounded border" alt="Category Image">
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Enter category description">Organic oil products category.</textarea>
                    </div>
                </div>

                <hr>

                <h5 class="mb-3">SEO Details</h5>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Meta Title</label>
                        <input type="text" name="meta_title" class="form-control" value="Organic Oils" placeholder="Meta title">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="form-control" value="organic oils, natural oils" placeholder="keyword1, keyword2">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="3" placeholder="Meta description">Buy natural organic oils online.</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.catalog.categories.index') }}" class="btn btn-light">
                        Cancel
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="ph ph-floppy-disk me-1"></i>
                        Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>
    </main>
</x-admin-layout>