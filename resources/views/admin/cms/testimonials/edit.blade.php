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

        <form action="#" method="POST" enctype="multipart/form-data">
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
                            <input type="text" name="customer_name" class="form-control" value="Rahul Sharma">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" value="Ahmedabad">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rating</label>
                            <select name="rating" class="form-select">
                                <option value="5" selected>5 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="2">2 Stars</option>
                                <option value="1">1 Star</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer Photo</label>
                            <input type="file" name="photo" class="form-control">

                            <div class="mt-2">
                                <img src="https://placehold.co/90x90"
                                     class="rounded-circle border"
                                     width="90"
                                     height="90"
                                     alt="Customer">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="1">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control" rows="5">Very good quality organic products and fast delivery.</textarea>
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