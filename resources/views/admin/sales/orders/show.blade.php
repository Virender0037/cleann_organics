<x-admin-layout title="Order Details">
    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Order #ORD-1001</h4>
                <p class="text-muted mb-0">Complete order details and activity</p>
            </div>

            <div>
                <button class="btn btn-light-secondary me-2">
                    <i class="ph ph-download-simple me-1"></i>
                    Invoice
                </button>

                <button class="btn btn-success me-2">
                    <i class="ph ph-printer me-1"></i>
                    Print
                </button>

                <button class="btn btn-primary">
                    <i class="ph ph-arrows-clockwise me-1"></i>
                    Update Status
                </button>
            </div>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>Sales</span>
            <span class="mx-2">›</span>
            <a href="{{ route('admin.sales.orders.index') }}">Orders</a>
            <span class="mx-2">›</span>
            <span>Order Details</span>
        </div>

        <div class="row">

            <div class="col-lg-8">

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Order Timeline</h5>
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content-between text-center">
                            <div>
                                <span class="badge bg-success rounded-pill mb-2">✓</span>
                                <p class="mb-0 fw-bold">Placed</p>
                                <small>25 Jun 2026</small>
                            </div>

                            <div>
                                <span class="badge bg-success rounded-pill mb-2">✓</span>
                                <p class="mb-0 fw-bold">Confirmed</p>
                                <small>25 Jun 2026</small>
                            </div>

                            <div>
                                <span class="badge bg-success rounded-pill mb-2">✓</span>
                                <p class="mb-0 fw-bold">Packed</p>
                                <small>26 Jun 2026</small>
                            </div>

                            <div>
                                <span class="badge bg-primary rounded-pill mb-2">●</span>
                                <p class="mb-0 fw-bold">Shipped</p>
                                <small>26 Jun 2026</small>
                            </div>

                            <div>
                                <span class="badge bg-light text-dark rounded-pill mb-2">○</span>
                                <p class="mb-0 fw-bold">Delivered</p>
                                <small>Pending</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Ordered Products</h5>
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
                                        <th>Price</th>
                                        <th>Tax</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr>
                                        <td>
                                            <strong>Organic Honey</strong>
                                        </td>
                                        <td>500g</td>
                                        <td>HON-500</td>
                                        <td>2</td>
                                        <td>₹299.00</td>
                                        <td>₹30.00</td>
                                        <td>₹598.00</td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <strong>Cold Pressed Mustard Oil</strong>
                                        </td>
                                        <td>1 Litre</td>
                                        <td>OIL-1L</td>
                                        <td>1</td>
                                        <td>₹650.00</td>
                                        <td>₹32.00</td>
                                        <td>₹650.00</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row">

                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>Payment Details</h5>
                            </div>

                            <div class="card-body">
                                <p><strong>Payment Method:</strong> UPI</p>
                                <p><strong>Transaction ID:</strong> TXN123456789</p>
                                <p><strong>Amount:</strong> ₹1,260.00</p>
                                <p><strong>Status:</strong> <span class="badge bg-success">Paid</span></p>
                                <p class="mb-0"><strong>Paid At:</strong> 25 Jun 2026, 09:16 AM</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>Shipment Details</h5>
                            </div>

                            <div class="card-body">
                                <p><strong>Courier:</strong> Delhivery</p>
                                <p><strong>Tracking No:</strong> DL123456789IN</p>
                                <p><strong>AWB:</strong> AWB987654321</p>
                                <p><strong>Dispatch Date:</strong> 26 Jun 2026</p>
                                <p class="mb-0"><strong>Status:</strong> <span class="badge bg-primary">Shipped</span></p>
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

                        <p class="mb-1"><strong>Admin:</strong> Customer requested fast delivery.</p>
                        <small class="text-muted">26 Jun 2026, 10:30 AM</small>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Customer Communication</h5>
                    </div>

                    <div class="card-body">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td>25 Jun 2026</td>
                                    <td>Email</td>
                                    <td>Order Confirmation Sent</td>
                                    <td><span class="badge bg-success">Sent</span></td>
                                </tr>

                                <tr>
                                    <td>26 Jun 2026</td>
                                    <td>SMS</td>
                                    <td>Shipment Update Sent</td>
                                    <td><span class="badge bg-success">Sent</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <div class="col-lg-4">

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Order Summary</h5>
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <strong>₹1,248.00</strong>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Discount</span>
                            <strong class="text-success">-₹100.00</strong>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping</span>
                            <strong>₹50.00</strong>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax</span>
                            <strong>₹62.00</strong>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <h5>Total</h5>
                            <h5>₹1,260.00</h5>
                        </div>
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
                        <h5>Shipping Address</h5>
                    </div>

                    <div class="card-body">
                        <p class="mb-0">
                            Rahul Sharma<br>
                            101, ABC Apartment<br>
                            Ahmedabad, Gujarat<br>
                            India - 380015
                        </p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Billing Address</h5>
                    </div>

                    <div class="card-body">
                        <p class="mb-0">
                            Rahul Sharma<br>
                            101, ABC Apartment<br>
                            Ahmedabad, Gujarat<br>
                            India - 380015
                        </p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Documents</h5>
                    </div>

                    <div class="card-body d-grid gap-2">
                        <button class="btn btn-light-primary">
                            <i class="ph ph-file-pdf me-1"></i>
                            Download Invoice
                        </button>

                        <button class="btn btn-light-secondary">
                            <i class="ph ph-package me-1"></i>
                            Packing Slip
                        </button>

                        <button class="btn btn-light-secondary">
                            <i class="ph ph-truck me-1"></i>
                            Shipping Label
                        </button>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Return & Refund History</h5>
                    </div>

                    <div class="card-body">
                        <p class="text-muted mb-0">No return or refund request found.</p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Activity Log</h5>
                    </div>

                    <div class="card-body">
                        <p class="mb-1"><strong>Order Created</strong></p>
                        <small class="text-muted">25 Jun 2026, 09:15 AM</small>

                        <hr>

                        <p class="mb-1"><strong>Status changed to Confirmed</strong></p>
                        <small class="text-muted">25 Jun 2026, 10:02 AM</small>

                        <hr>

                        <p class="mb-1"><strong>Status changed to Shipped</strong></p>
                        <small class="text-muted">26 Jun 2026, 09:10 AM</small>
                    </div>
                </div>

            </div>

        </div>

    </main>
</x-admin-layout>