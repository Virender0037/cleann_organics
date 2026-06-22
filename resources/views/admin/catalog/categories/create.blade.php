<x-admin-layout title="Add Category">
    <main class="pc-container-edit">  
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Add Category</h4>
            <p class="text-muted mb-0">Create a new product category</p>
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

        <span>Add Category</span>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Add New Category</h5>
        </div>

        <div class="card-body">
            <form action="#" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Category Name <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            placeholder="Enter category name">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Slug <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="slug"
                            class="form-control"
                            placeholder="category-slug">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Parent Category</label>

                        <select name="parent_id" class="form-select">
                            <option value="">Select Parent Category</option>
                            <option value="1">Organic Products</option>
                            <option value="2">Health Supplements</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>

                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Sort Order</label>

                        <input
                            type="number"
                            name="sort_order"
                            class="form-control"
                            value="0">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category Image</label>

                        <input
                            type="file"
                            name="image"
                            class="form-control">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Description</label>

                        <textarea
                            name="description"
                            class="form-control"
                            rows="4"
                            placeholder="Enter category description"></textarea>
                    </div>

                </div>

                <hr>

                <h5 class="mb-3">SEO Information</h5>

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Meta Title</label>

                        <input
                            type="text"
                            name="meta_title"
                            class="form-control"
                            placeholder="Meta title">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Meta Keywords</label>

                        <input
                            type="text"
                            name="meta_keywords"
                            class="form-control"
                            placeholder="keyword1, keyword2">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Meta Description</label>

                        <textarea
                            name="meta_description"
                            class="form-control"
                            rows="3"
                            placeholder="Meta description"></textarea>
                    </div>

                </div>

                <div class="text-end">
                    <a href="{{ route('admin.catalog.categories.index') }}"
                       class="btn btn-light">
                        Cancel
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="ph ph-plus-circle me-1"></i>
                        Create Category
                    </button>
                </div>

            </form>
        </div>
    </div>
</main>  
</x-admin-layout>