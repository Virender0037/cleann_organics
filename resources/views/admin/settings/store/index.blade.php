<x-admin-layout title="Store Settings">
<main class="pc-container-edit">

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Store Settings</h4>
        <p class="text-muted mb-0">Manage store profile and business details</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5>Store Information</h5>
    </div>

    <div class="card-body">
        <form action="#" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">Store Name</label>
                    <input type="text" name="store_name" class="form-control" value="Cleann Organics">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Store Email</label>
                    <input type="email" name="store_email" class="form-control" value="info@cleannorganics.com">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Store Phone</label>
                    <input type="text" name="store_phone" class="form-control" value="+91 9876543210">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">GST Number</label>
                    <input type="text" name="gst_number" class="form-control" placeholder="Enter GST number">
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Store Address</label>
                    <textarea name="store_address" class="form-control" rows="3">Ahmedabad, Gujarat, India</textarea>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Store Logo</label>
                    <input type="file" name="store_logo" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Favicon</label>
                    <input type="file" name="favicon" class="form-control">
                </div>

            </div>

            <div class="text-end mt-4">
                <button class="btn btn-primary">
                    <i class="ph ph-floppy-disk me-1"></i>
                    Save Store Settings
                </button>
            </div>

        </form>
    </div>
</div>

</main>
</x-admin-layout>