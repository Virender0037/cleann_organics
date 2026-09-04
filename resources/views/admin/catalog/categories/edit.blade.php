<x-admin-layout title="Edit Category">
    <main class="pc-container-edit">
    <x-admin.page-header title="Edit Category" subtitle="Update product category details">
        <x-slot:actions>
            <a href="{{ route('admin.catalog.categories.index') }}" class="btn btn-light">
                <i class="ph ph-arrow-left me-1"></i>
                Back
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[
        ['label' => 'Catalog'],
        ['label' => 'Categories', 'url' => route('admin.catalog.categories.index')],
        ['label' => 'Edit Category'],
    ]" />

    @include('admin.partials.alerts')

    <x-admin.form-card title="Edit Category" action="{{ route('admin.catalog.categories.update', $category) }}" method="PUT" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Category Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $category->name) }}" placeholder="Enter category name">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $category->slug) }}" placeholder="category-slug">
                @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Parent Category</label>
                <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                    <option value="">Select Parent Category</option>
                    @foreach ($parentCategories as $parentCategory)
                        <option value="{{ $parentCategory->id }}" @selected((int) old('parent_id', $category->parent_id) === $parentCategory->id)>
                            {{ $parentCategory->name }}
                        </option>
                    @endforeach
                </select>
                @error('parent_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $category->sort_order) }}">
                @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Image</label>
                <input type="file" name="image" class="form-control @error('image') is-invalid @enderror">
                @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror

                @if ($category->image)
                    <div class="mt-2" style="position:relative; width:80px;">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($category->image) }}" width="80" height="80" class="rounded border" alt="Category Image">
                        <button
                            type="submit"
                            form="delete-category-image"
                            class="vmm-card-remove"
                            title="Delete image"
                            aria-label="Delete image"
                            onclick="return confirm('Are you sure you want to delete this category image?');"
                        >
                            <i class="ph ph-x"></i>
                        </button>
                    </div>
                @endif
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select @error('status') is-invalid @enderror">
                    <option value="active" @selected(old('status', $category->status) === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $category->status) === 'inactive')>Inactive</option>
                </select>
                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-12 mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4" placeholder="Enter category description">{{ old('description', $category->description) }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <hr>

        <h5 class="mb-3">SEO Details</h5>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Meta Title</label>
                <input type="text" name="meta_title" class="form-control @error('meta_title') is-invalid @enderror" value="{{ old('meta_title', $category->meta_title) }}" placeholder="Meta title">
                @error('meta_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Meta Keywords</label>
                <input type="text" name="meta_keywords" class="form-control @error('meta_keywords') is-invalid @enderror" value="{{ old('meta_keywords', $category->meta_keywords) }}" placeholder="keyword1, keyword2">
                @error('meta_keywords') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-12 mb-3">
                <label class="form-label">Meta Description</label>
                <textarea name="meta_description" class="form-control @error('meta_description') is-invalid @enderror" rows="3" placeholder="Meta description">{{ old('meta_description', $category->meta_description) }}</textarea>
                @error('meta_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <x-slot:actions>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.catalog.categories.index') }}" class="btn btn-light">
                    Cancel
                </a>

                <button type="submit" class="btn btn-primary">
                    <i class="ph ph-floppy-disk me-1"></i>
                    Update Category
                </button>
            </div>
        </x-slot:actions>
    </x-admin.form-card>

    @if ($category->image)
        <form id="delete-category-image" action="{{ route('admin.catalog.categories.image.destroy', $category) }}" method="POST">
            @csrf
            @method('DELETE')
        </form>
    @endif
    </main>
</x-admin-layout>
