<x-admin-layout title="Add Product">
    <main class="pc-container-edit">  
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Add Product</h4>
            <p class="text-muted mb-0">Create product basic information</p>
        </div>

        <a href="{{ route('admin.catalog.products.index') }}" class="btn btn-light">
            <i class="ph ph-arrow-left me-1"></i>
            Back
        </a>
    </div>

    <div class="mb-3">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <span class="mx-2">›</span>
        <span>Catalog</span>
        <span class="mx-2">›</span>
        <a href="{{ route('admin.catalog.products.index') }}">Products</a>
        <span class="mx-2">›</span>
        <span>Add Product</span>
    </div>

    <form action="#" method="POST">
        @csrf

        <div class="card mb-4">
            <div class="card-header">
                <h5>Basic Information</h5>
            </div>

            <div class="card-body">
                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Enter product name">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Slug <span class="text-danger">*</span></label>
                        <input type="text" name="slug" class="form-control" placeholder="product-slug">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select">
                            <option value="">Select Category</option>
                            <option value="1">Organic Oils</option>
                            <option value="2">Organic Foods</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tax Rate</label>
                        <select name="tax_rate_id" class="form-select">
                            <option value="">Select Tax Rate</option>
                            <option value="1">GST 5%</option>
                            <option value="2">GST 12%</option>
                            <option value="3">GST 18%</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Brand</label>
                        <input type="text" name="brand" class="form-control" placeholder="Enter brand name">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft">Draft</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5>Description</h5>
            </div>

            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Short Description</label>
                    <textarea name="short_description" class="form-control" rows="3" placeholder="Short product description"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Full Description</label>
                    <textarea name="description" class="form-control" rows="6" placeholder="Full product description"></textarea>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5>Return & Display Settings</h5>
            </div>

            <div class="card-body">
                <div class="row">

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Returnable</label>
                        <select name="is_returnable" class="form-select">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Return Days</label>
                        <input type="number" name="return_days" class="form-control" value="7">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Featured</label>
                        <select name="is_featured" class="form-select">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Latest</label>
                        <select name="is_latest" class="form-select">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Best Seller</label>
                        <select name="is_best_seller" class="form-select">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5>SEO Information</h5>
            </div>

            <div class="card-body">
                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Meta Title</label>
                        <input type="text" name="meta_title" class="form-control" placeholder="Meta title">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Canonical URL</label>
                        <input type="text" name="canonical_url" class="form-control" placeholder="Canonical URL">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Meta Keywords</label>
                        <textarea name="meta_keywords" class="form-control" rows="2" placeholder="keyword1, keyword2"></textarea>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="3" placeholder="Meta description"></textarea>
                    </div>

                </div>
            </div>
        </div>

        <div class="text-end mb-4">
            <a href="{{ route('admin.catalog.products.index') }}" class="btn btn-light">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary">
                <i class="ph ph-floppy-disk me-1"></i>
                Create Product
            </button>
        </div>

    </form>
</main>
</x-admin-layout>