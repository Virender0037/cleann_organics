<x-admin-layout title="Return Details">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Return #RET-1001</h4>
                <p class="text-muted mb-0">Review return request, refund details and activity</p>
            </div>

            <div>
                <button class="btn btn-success me-2">
                    <i class="ph ph-check me-1"></i>
                    Approve
                </button>

                <button class="btn btn-danger me-2">
                    <i class="ph ph-x me-1"></i>
                    Reject
                </button>

                <a href="{{ route('admin.sales.returns.index') }}" class="btn btn-light">
                    <i class="ph ph-arrow-left me-1"></i>
                    Back
                </a>
            </div>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>Sales</span>
            <span class="mx-2">›</span>
            <a href="{{ route('admin.sales.returns.index') }}">Returns</a>
            <span class="mx-2">›</span>
            <span>Return Details</span>
        </div>

        <div class="row">

            <div class="col-lg-8">

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Returned Products</h5>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Variant</th>
                                        <th>SKU</th>
                                        <th>Qty</th>
                                        <th>Refund Amount</th>
                                        <th>Condition</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr>
                                        <td><strong>Cold Pressed Mustard Oil</strong></td>
                                        <td>1 Litre</td>
                                        <td>OIL-1L</td>
                                        <td>1</td>
                                        <td>₹650.00</td>
                                        <td>
                                            <span class="badge bg-warning text-dark">Damaged</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Return Reason</h5>
                    </div>

                    <div class="card-body">
                        <p class="mb-2"><strong>Reason:</strong> Damaged Product</p>
                        <p class="mb-0">
                            Customer reported that the bottle was leaked and packaging was damaged during delivery.
                        </p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Uploaded Proof Images</h5>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <img src="https://placehold.co/180x140"
                                     class="img-fluid rounded border"
                                     alt="Proof Image">
                            </div>

                            <div class="col-md-3 mb-3">
                                <img src="https://placehold.co/180x140"
                                     class="img-fluid rounded border"
                                     alt="Proof Image">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Return Timeline</h5>
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content-between text-center">
                            <div>
                                <span class="badge bg-success rounded-pill mb-2">✓</span>
                                <p class="mb-0 fw-bold">Requested</p>
                                <small>26 Jun 2026</small>
                            </div>

                            <div>
                                <span class="badge bg-primary rounded-pill mb-2">●</span>
                                <p class="mb-0 fw-bold">Under Review</p>
                                <small>Pending</small>
                            </div>

                            <div>
                                <span class="badge bg-light text-dark rounded-pill mb-2">○</span>
                                <p class="mb-0 fw-bold">Approved</p>
                                <small>Pending</small>
                            </div>

                            <div>
                                <span class="badge bg-light text-dark rounded-pill mb-2">○</span>
                                <p class="mb-0 fw-bold">Refunded</p>
                                <small>Pending</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Internal Notes</h5>
                    </div>

                    <div class="card-body">
                        <textarea class="form-control mb-3" rows="4" placeholder="Add internal note"></textarea>

                        <button class="btn btn-primary">
                            <i class="ph ph-plus me-1"></i>
                            Add Note
                        </button>

                        <hr>

                        <p class="mb-1"><strong>Admin:</strong> Need to verify product damage images before approval.</p>
                        <small class="text-muted">26 Jun 2026, 11:00 AM</small>
                    </div>
                </div>

            </div>

            <div class="col-lg-4">

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Return Summary</h5>
                    </div>

                    <div class="card-body">
                        <p class="mb-2"><strong>Return ID:</strong> #RET-1001</p>
                        <p class="mb-2"><strong>Order ID:</strong> #ORD-1001</p>
                        <p class="mb-2"><strong>Status:</strong> <span class="badge bg-warning text-dark">Pending</span></p>
                        <p class="mb-2"><strong>Requested Date:</strong> 26 Jun 2026</p>
                        <p class="mb-0"><strong>Refund Amount:</strong> ₹650.00</p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Customer</h5>
                    </div>

                    <div class="card-body">
                        <p class="mb-1"><strong>Rahul Sharma</strong></p>
                        <p class="mb-1">rahul@email.com</p>
                        <p class="mb-0">+91 9876543210</p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Refund Details</h5>
                    </div>

                    <div class="card-body">
                        <p class="mb-2"><strong>Refund Method:</strong> Original Payment Method</p>
                        <p class="mb-2"><strong>Refund Amount:</strong> ₹650.00</p>
                        <p class="mb-2"><strong>Refund Status:</strong> <span class="badge bg-secondary">Not Initiated</span></p>

                        <button class="btn btn-light-danger w-100 mt-2">
                            <i class="ph ph-arrow-counter-clockwise me-1"></i>
                            Initiate Refund
                        </button>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Pickup Details</h5>
                    </div>

                    <div class="card-body">
                        <p class="mb-2"><strong>Pickup Status:</strong> Not Scheduled</p>
                        <p class="mb-2"><strong>Courier:</strong> --</p>
                        <p class="mb-0"><strong>Tracking No:</strong> --</p>

                        <button class="btn btn-light-primary w-100 mt-3">
                            <i class="ph ph-truck me-1"></i>
                            Schedule Pickup
                        </button>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Activity Log</h5>
                    </div>

                    <div class="card-body">
                        <p class="mb-1"><strong>Return Requested</strong></p>
                        <small class="text-muted">26 Jun 2026, 10:45 AM</small>

                        <hr>

                        <p class="mb-1"><strong>Proof images uploaded</strong></p>
                        <small class="text-muted">26 Jun 2026, 10:50 AM</small>
                    </div>
                </div>

            </div>

        </div>

    </main>

</x-admin-layout>