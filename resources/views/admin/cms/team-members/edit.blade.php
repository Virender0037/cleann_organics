<x-admin-layout title="Edit Team Member">
    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Edit Team Member</h4>
                <p class="text-muted mb-0">Update team member profile information</p>
            </div>

            <a href="{{ route('admin.cms.team-members.index') }}" class="btn btn-light">
                <i class="ph ph-arrow-left me-1"></i>
                Back
            </a>
        </div>

        <form action="#" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card mb-4">
                <div class="card-header">
                    <h5>Profile Information</h5>
                </div>

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="Rahul Sharma">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Designation <span class="text-danger">*</span></label>
                            <input type="text" name="designation" class="form-control" value="Operations Manager">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department</label>
                            <input type="text" name="department" class="form-control" value="Operations Team">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Experience</label>
                            <input type="text" name="experience" class="form-control" value="5+ Years">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="rahul@example.com">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="+91 9876543210">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Photo</label>
                            <input type="file" name="photo" class="form-control">

                            <div class="mt-2">
                                <img src="https://placehold.co/90x90"
                                     class="rounded-circle border"
                                     width="90"
                                     height="90"
                                     alt="Team Member">
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="1">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Short Bio</label>
                            <textarea name="bio" class="form-control" rows="4">Experienced operations manager with strong ecommerce background.</textarea>
                        </div>

                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5>Social Links</h5>
                </div>

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">LinkedIn URL</label>
                            <input type="url" name="linkedin_url" class="form-control" value="https://linkedin.com/in/rahul">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Instagram URL</label>
                            <input type="url" name="instagram_url" class="form-control" value="">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Facebook URL</label>
                            <input type="url" name="facebook_url" class="form-control" value="">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Twitter / X URL</label>
                            <input type="url" name="twitter_url" class="form-control" value="">
                        </div>

                    </div>
                </div>
            </div>

            <div class="text-end mb-4">
                <a href="{{ route('admin.cms.team-members.index') }}" class="btn btn-light">Cancel</a>

                <button type="submit" class="btn btn-primary">
                    <i class="ph ph-floppy-disk me-1"></i>
                    Update Team Member
                </button>
            </div>

        </form>

    </main>
</x-admin-layout>