<x-admin-layout title="General Settings">
<main class="pc-container-edit">

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">General Settings</h4>
        <p class="text-muted mb-0">Manage basic website and company information</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5>General Information</h5>
    </div>

    <div class="card-body">
        <form action="#" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Site Name</label>
                    <input type="text" class="form-control" value="Cleann Organics">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Company Name</label>
                    <input type="text" class="form-control" value="Cleann Organics Pvt Ltd">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Company Email</label>
                    <input type="email" class="form-control" value="info@cleannorganics.com">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Company Phone</label>
                    <input type="text" class="form-control" value="+91 9876543210">
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Company Address</label>
                    <textarea class="form-control" rows="3">Ahmedabad, Gujarat, India</textarea>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Timezone</label>
                    <select class="form-select">
                        <option selected>Asia/Kolkata</option>
                        <option>UTC</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Currency</label>
                    <select class="form-select">
                        <option selected>INR ₹</option>
                        <option>USD $</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Language</label>
                    <select class="form-select">
                        <option selected>English</option>
                        <option>Hindi</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Logo</label>
                    <input type="file" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Favicon</label>
                    <input type="file" class="form-control">
                </div>
            </div>

            <div class="text-end mt-4">
                <button class="btn btn-primary">
                    <i class="ph ph-floppy-disk me-1"></i>
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

</main>
</x-admin-layout>