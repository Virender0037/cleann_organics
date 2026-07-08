<x-admin-layout title="Edit Blog">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Edit Blog</h4>
                <p class="text-muted mb-0">Update blog article details</p>
            </div>

            <a href="{{ route('admin.cms.blogs.index') }}" class="btn btn-light">
                <i class="ph ph-arrow-left me-1"></i>
                Back
            </a>
        </div>

        <form action="#" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card mb-4">
                <div class="card-header">
                    <h5>Blog Information</h5>
                </div>

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Blog Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="Benefits of Organic Food">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Slug <span class="text-danger">*</span></label>
                            <input type="text" name="slug" class="form-control" value="benefits-of-organic-food">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select name="blog_category_id" class="form-select">
                                <option value="">Select Category</option>
                                <option value="1" selected>Health</option>
                                <option value="2">Organic Lifestyle</option>
                                <option value="3">Recipes</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="published" selected>Published</option>
                                <option value="draft">Draft</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Featured Image</label>
                            <input type="file" name="image" class="form-control">

                            <div class="mt-2">
                                <img src="https://placehold.co/100x70"
                                     class="rounded border"
                                     width="100"
                                     height="70"
                                     alt="Blog">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Published Date</label>
                            <input type="date" name="published_at" class="form-control" value="2026-07-08">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Short Description</label>
                            <textarea name="short_description" class="form-control" rows="3">Learn the key benefits of eating organic food.</textarea>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Content</label>
                            <textarea name="content" class="form-control" rows="8">Organic food is grown without harmful chemicals and supports better health.</textarea>
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
                            <input type="text" name="meta_title" class="form-control" value="Benefits of Organic Food">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Meta Keywords</label>
                            <input type="text" name="meta_keywords" class="form-control" value="organic food, healthy food">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Meta Description</label>
                            <textarea name="meta_description" class="form-control" rows="3">Learn why organic food is better for your health.</textarea>
                        </div>

                    </div>
                </div>
            </div>

            <div class="text-end mb-4">
                <a href="{{ route('admin.cms.blogs.index') }}" class="btn btn-light">Cancel</a>

                <button type="submit" class="btn btn-primary">
                    <i class="ph ph-floppy-disk me-1"></i>
                    Update Blog
                </button>
            </div>

        </form>

    </main>

</x-admin-layout>