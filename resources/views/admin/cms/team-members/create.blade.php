<x-admin-layout title="Add Team Member">
    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Add Team Member</h4>
                <p class="text-muted mb-0">Create a new team profile for the website</p>
            </div>

            <a href="{{ route('admin.cms.team-members.index') }}" class="btn btn-light">
                <i class="ph ph-arrow-left me-1"></i>
                Back
            </a>
        </div>

        <form action="#" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="card mb-4">
                <div class="card-header">
                    <h5>Profile Information</h5>
                </div>

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Rahul Sharma">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Designation <span class="text-danger">*</span></label>
                            <input type="text" name="designation" class="form-control" placeholder="Operations Manager">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department</label>
                            <input type="text" name="department" class="form-control" placeholder="Operations Team">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Experience</label>
                            <input type="text" name="experience" class="form-control" placeholder="5+ Years">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="rahul@example.com">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="+91 9876543210">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Photo</label>
                            <input type="file" name="photo" class="form-control">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Short Bio</label>
                            <textarea name="bio" class="form-control" rows="4" placeholder="Write short team member bio..."></textarea>
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
                            <input type="url" name="linkedin_url" class="form-control" placeholder="https://linkedin.com/in/username">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Instagram URL</label>
                            <input type="url" name="instagram_url" class="form-control" placeholder="https://instagram.com/username">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Facebook URL</label>
                            <input type="url" name="facebook_url" class="form-control" placeholder="https://facebook.com/username">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Twitter / X URL</label>
                            <input type="url" name="twitter_url" class="form-control" placeholder="https://x.com/username">
                        </div>

                    </div>
                </div>
            </div>

            <div class="text-end mb-4">
                <a href="{{ route('admin.cms.team-members.index') }}" class="btn btn-light">Cancel</a>

                <button type="submit" class="btn btn-primary">
                    <i class="ph ph-floppy-disk me-1"></i>
                    Create Team Member
                </button>
            </div>

        </form>

    </main>
</x-admin-layout>