<x-admin-layout title="Edit Blog Tag">

<main class="pc-container-edit">

    <x-admin.page-header title="Edit Blog Tag" subtitle="Update blog tag details">
        <x-slot:actions>
            <a href="{{ route('admin.cms.blog-tags.index') }}" class="btn btn-light">
                <i class="ph ph-arrow-left me-1"></i>
                Back
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[
        ['label' => 'CMS'],
        ['label' => 'Blog Tags', 'url' => route('admin.cms.blog-tags.index')],
        ['label' => 'Edit Tag'],
    ]" />

    @include('admin.partials.alerts')

    <x-admin.form-card title="Tag Information" action="{{ route('admin.cms.blog-tags.update', $blogTag) }}" method="PUT">
        <x-slot:actions>
            <a href="{{ route('admin.cms.blog-tags.index') }}" class="btn btn-light">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary">
                <i class="ph ph-floppy-disk me-1"></i>
                Update Tag
            </button>
        </x-slot:actions>

        <div class="row">

            <div class="col-md-6 mb-3">
                <label class="form-label">Tag Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $blogTag->name) }}">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $blogTag->slug) }}">
                @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select @error('status') is-invalid @enderror">
                    <option value="active" @selected(old('status', $blogTag->status) === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $blogTag->status) === 'inactive')>Inactive</option>
                </select>
                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

        </div>
    </x-admin.form-card>

</main>

</x-admin-layout>
