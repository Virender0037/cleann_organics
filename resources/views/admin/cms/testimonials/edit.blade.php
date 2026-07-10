<x-admin-layout title="Edit Testimonial">
    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Edit Testimonial</h4>
                <p class="text-muted mb-0">Update customer testimonial</p>
            </div>

            <a href="{{ route('admin.cms.testimonials.index') }}" class="btn btn-light">
                <i class="ph ph-arrow-left me-1"></i>
                Back
            </a>
        </div>

        <form action="{{ route('admin.cms.testimonials.update', $testimonial) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card">
                <div class="card-header">
                    <h5>Testimonial Information</h5>
                </div>

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $testimonial->name) }}">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rating <span class="text-danger">*</span></label>
                            <select name="rating" class="form-select @error('rating') is-invalid @enderror">
                                <option value="5" @selected(old('rating', $testimonial->rating) == 5)>5 Stars</option>
                                <option value="4" @selected(old('rating', $testimonial->rating) == 4)>4 Stars</option>
                                <option value="3" @selected(old('rating', $testimonial->rating) == 3)>3 Stars</option>
                                <option value="2" @selected(old('rating', $testimonial->rating) == 2)>2 Stars</option>
                                <option value="1" @selected(old('rating', $testimonial->rating) == 1)>1 Star</option>
                            </select>
                            @error('rating') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Designation</label>
                            <input type="text" name="designation" class="form-control @error('designation') is-invalid @enderror" value="{{ old('designation', $testimonial->designation) }}">
                            @error('designation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Company</label>
                            <input type="text" name="company" class="form-control @error('company') is-invalid @enderror" value="{{ old('company', $testimonial->company) }}">
                            @error('company') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer Photo</label>
                            <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                            @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror

                            @if ($testimonial->image)
                                <div class="mt-2">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($testimonial->image) }}"
                                         class="rounded-circle border"
                                         width="90"
                                         height="90"
                                         alt="Customer">
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $testimonial->sort_order) }}">
                            @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active" @selected(old('status', $testimonial->status) === 'active')>Active</option>
                                <option value="inactive" @selected(old('status', $testimonial->status) === 'inactive')>Inactive</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3 d-flex align-items-end">
                            <div class="form-check">
                                <input type="checkbox" name="is_featured" value="1" class="form-check-input" id="is_featured" @checked(old('is_featured', $testimonial->is_featured))>
                                <label class="form-check-label" for="is_featured">Featured Testimonial</label>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control @error('message') is-invalid @enderror" rows="5">{{ old('message', $testimonial->message) }}</textarea>
                            @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                    </div>

                    <div class="text-end mt-4">
                        <a href="{{ route('admin.cms.testimonials.index') }}" class="btn btn-light">Cancel</a>

                        <button type="submit" class="btn btn-primary">
                            <i class="ph ph-floppy-disk me-1"></i>
                            Update Testimonial
                        </button>
                    </div>
                </div>
            </div>

        </form>

    </main>
</x-admin-layout>