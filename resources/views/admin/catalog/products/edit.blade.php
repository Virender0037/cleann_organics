<x-admin-layout title="Edit Product">
    <main class="pc-container-edit">  
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Edit Product</h4>
            <p class="text-muted mb-0">Update product basic information</p>
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
        <span>Edit Product</span>
    </div>

    <form action="{{ route('admin.catalog.products.update', $product) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card mb-4">
            <div class="card-header">
                <h5>Basic Information</h5>
            </div>

            <div class="card-body">
                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $product->slug) }}">
                        @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                            <option value="">Select Category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((int) old('category_id', $product->category_id) === $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tax Rate</label>
                        <select name="tax_rate_id" class="form-select @error('tax_rate_id') is-invalid @enderror">
                            <option value="">Select Tax Rate</option>
                            @foreach ($taxRates as $taxRate)
                                <option value="{{ $taxRate->id }}" @selected((int) old('tax_rate_id', $product->tax_rate_id) === $taxRate->id)>
                                    {{ $taxRate->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('tax_rate_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Brand</label>
                        <input type="text" name="brand" class="form-control @error('brand') is-invalid @enderror" value="{{ old('brand', $product->brand) }}">
                        @error('brand') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="draft" @selected(old('status', $product->status) === 'draft')>Draft</option>
                            <option value="active" @selected(old('status', $product->status) === 'active')>Active</option>
                            <option value="inactive" @selected(old('status', $product->status) === 'inactive')>Inactive</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                    <textarea name="short_description" class="form-control @error('short_description') is-invalid @enderror" rows="3">{{ old('short_description', $product->short_description) }}</textarea>
                    @error('short_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Full Description</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="6">{{ old('description', $product->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                        <select name="is_returnable" class="form-select @error('is_returnable') is-invalid @enderror">
                            <option value="0" @selected((string) old('is_returnable', (int) $product->is_returnable) === '0')>No</option>
                            <option value="1" @selected((string) old('is_returnable', (int) $product->is_returnable) === '1')>Yes</option>
                        </select>
                        @error('is_returnable') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Return Days</label>
                        <input type="number" name="return_days" class="form-control @error('return_days') is-invalid @enderror" value="{{ old('return_days', $product->return_days) }}">
                        @error('return_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $product->sort_order) }}">
                        @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Featured</label>
                        <select name="is_featured" class="form-select @error('is_featured') is-invalid @enderror">
                            <option value="0" @selected((string) old('is_featured', (int) $product->is_featured) === '0')>No</option>
                            <option value="1" @selected((string) old('is_featured', (int) $product->is_featured) === '1')>Yes</option>
                        </select>
                        @error('is_featured') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Latest</label>
                        <select name="is_latest" class="form-select @error('is_latest') is-invalid @enderror">
                            <option value="0" @selected((string) old('is_latest', (int) $product->is_latest) === '0')>No</option>
                            <option value="1" @selected((string) old('is_latest', (int) $product->is_latest) === '1')>Yes</option>
                        </select>
                        @error('is_latest') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Best Seller</label>
                        <select name="is_best_seller" class="form-select @error('is_best_seller') is-invalid @enderror">
                            <option value="0" @selected((string) old('is_best_seller', (int) $product->is_best_seller) === '0')>No</option>
                            <option value="1" @selected((string) old('is_best_seller', (int) $product->is_best_seller) === '1')>Yes</option>
                        </select>
                        @error('is_best_seller') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                        <input type="text" name="meta_title" class="form-control @error('meta_title') is-invalid @enderror" value="{{ old('meta_title', $product->meta_title) }}">
                        @error('meta_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Canonical URL</label>
                        <input type="text" name="canonical_url" class="form-control @error('canonical_url') is-invalid @enderror" value="{{ old('canonical_url', $product->canonical_url) }}">
                        @error('canonical_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Meta Keywords</label>
                        <textarea name="meta_keywords" class="form-control @error('meta_keywords') is-invalid @enderror" rows="2">{{ old('meta_keywords', $product->meta_keywords) }}</textarea>
                        @error('meta_keywords') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_description" class="form-control @error('meta_description') is-invalid @enderror" rows="3">{{ old('meta_description', $product->meta_description) }}</textarea>
                        @error('meta_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                Update Product
            </button>
        </div>

    </form>
</main>  
</x-admin-layout>