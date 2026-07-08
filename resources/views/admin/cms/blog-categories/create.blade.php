<x-admin-layout title="Add Blog Category">

<main class="pc-container-edit">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h4>Add Blog Category</h4>
<p class="text-muted">Create a new blog category</p>
</div>

<a href="{{ route('admin.cms.blog-categories.index') }}"
class="btn btn-light">
Back
</a>

</div>

<form>

<div class="card">

<div class="card-header">
<h5>Category Information</h5>
</div>

<div class="card-body">

<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label">
Category Name
</label>

<input
type="text"
class="form-control"
placeholder="Health">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">
Slug
</label>

<input
type="text"
class="form-control"
placeholder="health">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">
Sort Order
</label>

<input
type="number"
class="form-control"
value="0">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">
Status
</label>

<select class="form-select">

<option>Active</option>
<option>Inactive</option>

</select>

</div>

<div class="col-md-12">

<label class="form-label">
Description
</label>

<textarea
rows="4"
class="form-control"></textarea>

</div>

</div>

</div>

</div>

<div class="text-end mt-4">

<a href="{{ route('admin.cms.blog-categories.index') }}"
class="btn btn-light">
Cancel
</a>

<button class="btn btn-primary">
<i class="ph ph-floppy-disk me-1"></i>
Create Category
</button>

</div>

</form>

</main>

</x-admin-layout>