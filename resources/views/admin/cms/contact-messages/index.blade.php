<x-admin-layout title="Contact Messages">
    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Contact Messages</h4>
                <p class="text-muted mb-0">View customer enquiries submitted from the website</p>
            </div>

            <button class="btn btn-light-secondary">
                <i class="ph ph-download-simple me-1"></i>
                Export
            </button>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>CMS</span>
            <span class="mx-2">›</span>
            <span>Contact Messages</span>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Message List</h5>
            </div>

            <div class="card-body">

                <div class="row mb-4">
                    <div class="col-md-4">
                        <input type="text" class="form-control" placeholder="Search name, email or subject">
                    </div>

                    <div class="col-md-3">
                        <select class="form-select">
                            <option>All Status</option>
                            <option>New</option>
                            <option>Read</option>
                            <option>Replied</option>
                            <option>Closed</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Sender</th>
                                <th>Phone</th>
                                <th>Subject</th>
                                <th>Received On</th>
                                <th>Status</th>
                                <th width="130">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>1</td>

                                <td>
                                    <strong>Rahul Sharma</strong>
                                    <br>
                                    <small class="text-muted">rahul@email.com</small>
                                </td>

                                <td>+91 9876543210</td>

                                <td>Product availability enquiry</td>

                                <td>
                                    09 Jul 2026
                                    <br>
                                    <small class="text-muted">10:30 AM</small>
                                </td>

                                <td>
                                    <span class="badge bg-warning text-dark">New</span>
                                </td>

                                <td>
                                    <a href="{{ route('admin.cms.contact-messages.show') }}"
                                       class="btn btn-sm btn-info"
                                       title="View Message">
                                        <i class="ph ph-eye"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger" title="Delete Message">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </td>
                            </tr>

                            <tr>
                                <td>2</td>

                                <td>
                                    <strong>Priya Patel</strong>
                                    <br>
                                    <small class="text-muted">priya@email.com</small>
                                </td>

                                <td>+91 9988776655</td>

                                <td>Bulk order enquiry</td>

                                <td>
                                    08 Jul 2026
                                    <br>
                                    <small class="text-muted">04:15 PM</small>
                                </td>

                                <td>
                                    <span class="badge bg-success">Replied</span>
                                </td>

                                <td>
                                    <a href="{{ route('admin.cms.contact-messages.show') }}"
                                       class="btn btn-sm btn-info"
                                       title="View Message">
                                        <i class="ph ph-eye"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger" title="Delete Message">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>

                    </table>
                </div>

            </div>
        </div>

    </main>
</x-admin-layout>