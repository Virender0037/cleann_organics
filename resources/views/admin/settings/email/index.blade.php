<x-admin-layout title="Email Settings">
<main class="pc-container-edit">

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Email Settings</h4>
        <p class="text-muted mb-0">Configure SMTP and system email settings</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5>SMTP Configuration</h5>
    </div>

    <div class="card-body">
        <form action="#" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Mail Driver</label>
                    <select class="form-select">
                        <option selected>SMTP</option>
                        <option>Sendmail</option>
                        <option>Mailgun</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">SMTP Host</label>
                    <input type="text" class="form-control" placeholder="smtp.mailtrap.io">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">SMTP Port</label>
                    <input type="text" class="form-control" placeholder="587">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Encryption</label>
                    <select class="form-select">
                        <option selected>TLS</option>
                        <option>SSL</option>
                        <option>None</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">From Email</label>
                    <input type="email" class="form-control" value="noreply@cleannorganics.com">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">SMTP Username</label>
                    <input type="text" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">SMTP Password</label>
                    <input type="password" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">From Name</label>
                    <input type="text" class="form-control" value="Cleann Organics">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Reply To Email</label>
                    <input type="email" class="form-control" value="support@cleannorganics.com">
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-light-primary">
                    <i class="ph ph-paper-plane-tilt me-1"></i>
                    Send Test Email
                </button>

                <button class="btn btn-primary">
                    <i class="ph ph-floppy-disk me-1"></i>
                    Save Email Settings
                </button>
            </div>
        </form>
    </div>
</div>

</main>
</x-admin-layout>