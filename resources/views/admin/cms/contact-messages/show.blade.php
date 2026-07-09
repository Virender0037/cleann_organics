<x-admin-layout title="Contact Message Details">
    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Contact Message Details</h4>
                <p class="text-muted mb-0">Review customer enquiry and update status</p>
            </div>

            <a href="{{ route('admin.cms.contact-messages.index') }}" class="btn btn-light">
                <i class="ph ph-arrow-left me-1"></i>
                Back
            </a>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>CMS</span>
            <span class="mx-2">›</span>
            <a href="{{ route('admin.cms.contact-messages.index') }}">Contact Messages</a>
            <span class="mx-2">›</span>
            <span>Message Details</span>
        </div>

        <div class="row">

            <div class="col-lg-8">

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Message</h5>
                    </div>

                    <div class="card-body">
                        <p class="mb-2"><strong>Subject:</strong> Product availability enquiry</p>

                        <hr>

                        <p class="mb-0">
                            Hello Team,<br><br>
                            I want to know whether Organic Honey 500gm is available for bulk purchase.
                            Please share pricing and delivery details for Ahmedabad.
                        </p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Admin Notes</h5>
                    </div>

                    <div class="card-body">
                        <textarea class="form-control mb-3" rows="4" placeholder="Add internal note"></textarea>

                        <button class="btn btn-primary">
                            <i class="ph ph-plus me-1"></i>
                            Add Note
                        </button>

                        <hr>

                        <p class="mb-1"><strong>Admin:</strong> Need to call customer and confirm bulk quantity.</p>
                        <small class="text-muted">09 Jul 2026, 11:00 AM</small>
                    </div>
                </div>

            </div>

            <div class="col-lg-4">

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Sender Information</h5>
                    </div>

                    <div class="card-body">
                        <p class="mb-2"><strong>Name:</strong> Rahul Sharma</p>
                        <p class="mb-2"><strong>Email:</strong> rahul@email.com</p>
                        <p class="mb-2"><strong>Phone:</strong> +91 9876543210</p>
                        <p class="mb-0"><strong>Received:</strong> 09 Jul 2026, 10:30 AM</p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Status</h5>
                    </div>

                    <div class="card-body">
                        <select class="form-select mb-3">
                            <option selected>New</option>
                            <option>Read</option>
                            <option>Replied</option>
                            <option>Closed</option>
                        </select>

                        <button class="btn btn-primary w-100">
                            <i class="ph ph-floppy-disk me-1"></i>
                            Update Status
                        </button>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Quick Actions</h5>
                    </div>

                    <div class="card-body d-grid gap-2">
                        <button class="btn btn-light-primary">
                            <i class="ph ph-envelope-simple me-1"></i>
                            Reply by Email
                        </button>

                        <button class="btn btn-light-secondary">
                            <i class="ph ph-check-circle me-1"></i>
                            Mark as Closed
                        </button>

                        <button class="btn btn-light-danger">
                            <i class="ph ph-trash me-1"></i>
                            Delete Message
                        </button>
                    </div>
                </div>

            </div>

        </div>

    </main>
</x-admin-layout>