<x-admin-layout title="Add Testimonial">
    <main class="pc-container-edit">

        <x-admin.page-header title="Add Testimonial" subtitle="Create a new customer testimonial">
            <x-slot:actions>
                <a href="{{ route('admin.cms.testimonials.index') }}" class="btn btn-light">
                    <i class="ph ph-arrow-left me-1"></i>
                    Back
                </a>
            </x-slot:actions>
        </x-admin.page-header>

        <x-admin.breadcrumb :items="[
            ['label' => 'CMS'],
            ['label' => 'Testimonials', 'url' => route('admin.cms.testimonials.index')],
            ['label' => 'Add Testimonial'],
        ]" />

        @include('admin.partials.alerts')

        <x-admin.form-card title="Testimonial Information" action="{{ route('admin.cms.testimonials.store') }}" enctype="multipart/form-data">
            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Rahul Sharma">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Rating <span class="text-danger">*</span></label>
                    <select name="rating" class="form-select @error('rating') is-invalid @enderror">
                        <option value="5" @selected(old('rating', 5) == 5)>5 Stars</option>
                        <option value="4" @selected(old('rating') == 4)>4 Stars</option>
                        <option value="3" @selected(old('rating') == 3)>3 Stars</option>
                        <option value="2" @selected(old('rating') == 2)>2 Stars</option>
                        <option value="1" @selected(old('rating') == 1)>1 Star</option>
                    </select>
                    @error('rating') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Designation</label>
                    <input type="text" name="designation" class="form-control @error('designation') is-invalid @enderror" value="{{ old('designation') }}" placeholder="Customer">
                    @error('designation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city') }}" placeholder="Mumbai">
                    @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Company</label>
                    <input type="text" name="company" class="form-control @error('company') is-invalid @enderror" value="{{ old('company') }}" placeholder="Acme Inc.">
                    @error('company') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Customer Photo</label>
                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                    @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', 0) }}">
                    @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                    </select>
                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="is_featured" value="1" class="form-check-input" id="is_featured" @checked(old('is_featured'))>
                        <label class="form-check-label" for="is_featured">Featured Testimonial</label>
                    </div>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Message <span class="text-danger">*</span></label>
                    <textarea name="message" class="form-control @error('message') is-invalid @enderror" rows="5" placeholder="Write testimonial message...">{{ old('message') }}</textarea>
                    @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

            </div>

            <x-slot:actions>
                <a href="{{ route('admin.cms.testimonials.index') }}" class="btn btn-light">Cancel</a>

                <button type="submit" class="btn btn-primary">
                    <i class="ph ph-floppy-disk me-1"></i>
                    Create Testimonial
                </button>
            </x-slot:actions>
        </x-admin.form-card>

    </main>
</x-admin-layout>
