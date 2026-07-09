<x-admin-layout title="General Settings">
<main class="pc-container-edit">

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">General Settings</h4>
        <p class="text-muted mb-0">Manage basic application configuration</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5>General Information</h5>
    </div>

    <div class="card-body">
        <form action="#" method="POST">
            @csrf

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">Application Name</label>
                    <input type="text" name="app_name" class="form-control" value="Cleann Organics">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Support Email</label>
                    <input type="email" name="support_email" class="form-control" value="support@cleannorganics.com">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Support Phone</label>
                    <input type="text" name="support_phone" class="form-control" value="+91 9876543210">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Timezone</label>
                    <select name="timezone" class="form-select">
                        <option selected>Asia/Kolkata</option>
                        <option>UTC</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Date Format</label>
                    <select name="date_format" class="form-select">
                        <option selected>d M Y</option>
                        <option>Y-m-d</option>
                        <option>d/m/Y</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Default Language</label>
                    <select name="language" class="form-select">
                        <option selected>English</option>
                        <option>Hindi</option>
                    </select>
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