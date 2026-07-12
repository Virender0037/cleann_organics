<x-admin-layout title="Add Category">
    <main class="pc-container-edit">
    <x-admin.page-header title="Add Category" subtitle="Create a new product category">
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
        ['label' => 'Add Category'],
    ]" />

    @include('admin.partials.alerts')

    <x-admin.form-card title="Add New Category" action="{{ route('admin.catalog.categories.store') }}" enctype="multipart/form-data">
        <div class="row">

            <div class="col-md-6 mb-3">
                <label class="form-label">
                    Category Name <span class="text-danger">*</span>
                </label>

                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Enter category name">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">
                    Slug
                </label>

                <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="Leave blank to auto-generate from name">
                @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Parent Category</label>

                <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                    <option value="">Select Parent Category</option>
                    @foreach ($parentCategories as $parentCategory)
                        <option value="{{ $parentCategory->id }}" @selected((int) old('parent_id') === $parentCategory->id)>
                            {{ $parentCategory->name }}
                        </option>
                    @endforeach
                </select>
                @error('parent_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Status</label>

                <select name="status" class="form-select @error('status') is-invalid @enderror">
                    <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                </select>
                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Sort Order</label>

                <input
                    type="number"
                    name="sort_order"
                    class="form-control @error('sort_order') is-invalid @enderror"
                    value="{{ old('sort_order', 0) }}">
                @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Category Image</label>

                <input
                    type="file"
                    name="image"
                    class="form-control @error('image') is-invalid @enderror">
                @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-12 mb-3">
                <label class="form-label">Description</label>

                <textarea
                    name="description"
                    class="form-control @error('description') is-invalid @enderror"
                    rows="4"
                    placeholder="Enter category description">{{ old('description') }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                    class="form-control @error('meta_title') is-invalid @enderror"
                    value="{{ old('meta_title') }}"
                    placeholder="Meta title">
                @error('meta_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Meta Keywords</label>

                <input
                    type="text"
                    name="meta_keywords"
                    class="form-control @error('meta_keywords') is-invalid @enderror"
                    value="{{ old('meta_keywords') }}"
                    placeholder="keyword1, keyword2">
                @error('meta_keywords') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-12 mb-3">
                <label class="form-label">Meta Description</label>

                <textarea
                    name="meta_description"
                    class="form-control @error('meta_description') is-invalid @enderror"
                    rows="3"
                    placeholder="Meta description">{{ old('meta_description') }}</textarea>
                @error('meta_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

        </div>

        <x-slot:actions>
            <a href="{{ route('admin.catalog.categories.index') }}"
               class="btn btn-light">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary">
                <i class="ph ph-plus-circle me-1"></i>
                Create Category
            </button>
        </x-slot:actions>
    </x-admin.form-card>
</main>
</x-admin-layout>
