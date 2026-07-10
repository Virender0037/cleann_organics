<x-admin-layout title="Edit Blog Category">

<main class="pc-container-edit">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h4>Edit Blog Category</h4>
<p class="text-muted">Update blog category</p>
</div>

<a href="{{ route('admin.cms.blog-categories.index') }}"
class="btn btn-light">
Back
</a>

</div>

<form action="{{ route('admin.cms.blog-categories.update', $blogCategory) }}" method="POST">
@csrf
@method('PUT')

<div class="card">

<div class="card-header">
<h5>Category Information</h5>
</div>

<div class="card-body">

<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label">
Category Name <span class="text-danger">*</span>
</label>

<input
type="text"
name="name"
class="form-control @error('name') is-invalid @enderror"
value="{{ old('name', $blogCategory->name) }}">
@error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror

</div>

<div class="col-md-6 mb-3">

<label class="form-label">
Slug
</label>

<input
type="text"
name="slug"
class="form-control @error('slug') is-invalid @enderror"
value="{{ old('slug', $blogCategory->slug) }}">
@error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror

</div>

<div class="col-md-6 mb-3">

<label class="form-label">
Sort Order
</label>

<input
type="number"
name="sort_order"
class="form-control @error('sort_order') is-invalid @enderror"
value="{{ old('sort_order', $blogCategory->sort_order) }}">
@error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror

</div>

<div class="col-md-6 mb-3">

<label class="form-label">
Status
</label>

<select name="status" class="form-select @error('status') is-invalid @enderror">

<option value="active" @selected(old('status', $blogCategory->status) === 'active')>Active</option>
<option value="inactive" @selected(old('status', $blogCategory->status) === 'inactive')>Inactive</option>

</select>
@error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror

</div>

<div class="col-md-12 mb-3">

<label class="form-label">
Description
</label>

<textarea
name="description"
rows="4"
class="form-control @error('description') is-invalid @enderror">{{ old('description', $blogCategory->description) }}</textarea>
@error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror

</div>

</div>

</div>

</div>

<div class="card mt-4">

<div class="card-header">
<h5>SEO Information</h5>
</div>

<div class="card-body">

<div class="row">

<div class="col-md-6 mb-3">
<label class="form-label">Meta Title</label>
<input type="text" name="meta_title" class="form-control @error('meta_title') is-invalid @enderror" value="{{ old('meta_title', $blogCategory->meta_title) }}">
@error('meta_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Canonical URL</label>
<input type="text" name="canonical_url" class="form-control @error('canonical_url') is-invalid @enderror" value="{{ old('canonical_url', $blogCategory->canonical_url) }}">
@error('canonical_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-12 mb-3">
<label class="form-label">Meta Description</label>
<textarea name="meta_description" class="form-control @error('meta_description') is-invalid @enderror" rows="3">{{ old('meta_description', $blogCategory->meta_description) }}</textarea>
@error('meta_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

</div>

</div>

</div>

<div class="text-end mt-4">

<a href="{{ route('admin.cms.blog-categories.index') }}"
class="btn btn-light">
Cancel
</a>

<button type="submit" class="btn btn-primary">
<i class="ph ph-floppy-disk me-1"></i>
Update Category
</button>

</div>

</form>

</main>

</x-admin-layout>
