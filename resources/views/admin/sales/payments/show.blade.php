<x-admin-layout title="Payment Details">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Payment Details</h4>
                <p class="text-muted mb-0">View complete transaction information</p>
            </div>

            <a href="{{ route('admin.sales.payments.index') }}" class="btn btn-light">
                <i class="ph ph-arrow-left me-1"></i>
                Back
            </a>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>Sales</span>
            <span class="mx-2">›</span>
            <a href="{{ route('admin.sales.payments.index') }}">Payments</a>
            <span class="mx-2">›</span>
            <span>Payment Details</span>
        </div>

        <div class="row">

            <div class="col-lg-8">

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Transaction Information</h5>
                    </div>

                    <div class="card-body">
                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label class="text-muted">Transaction ID</label>
                                <p class="fw-bold mb-0">TXN458965214</p>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="text-muted">Order Number</label>
                                <p class="fw-bold mb-0">#ORD-1001</p>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="text-muted">Payment Method</label>
                                <p class="mb-0">
                                    <span class="badge bg-primary">Razorpay</span>
                                </p>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="text-muted">Payment Status</label>
                                <p class="mb-0">
                                    <span class="badge bg-success">Paid</span>
                                </p>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="text-muted">Amount</label>
                                <p class="fw-bold mb-0">₹1,250.00</p>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="text-muted">Paid At</label>
                                <p class="mb-0">26 Jun 2026, 10:45 AM</p>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="text-muted">Gateway Reference</label>
                                <p class="mb-0">pay_NhT8bC1245</p>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="text-muted">Currency</label>
                                <p class="mb-0">INR</p>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Gateway Response</h5>
                    </div>

                    <div class="card-body">
                        <pre class="bg-light p-3 rounded mb-0">{
  "status": "captured",
  "method": "upi",
  "bank": "HDFC",
  "vpa": "rahul@upi",
  "response_code": "SUCCESS"
}</pre>
                    </div>
                </div>

            </div>

            <div class="col-lg-4">

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
                        <h5>Order Summary</h5>
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Order Total</span>
                            <strong>₹1,250.00</strong>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Payment Received</span>
                            <strong class="text-success">₹1,250.00</strong>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span>Balance</span>
                            <strong>₹0.00</strong>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Actions</h5>
                    </div>

                    <div class="card-body d-grid gap-2">
                        <button class="btn btn-light-primary">
                            <i class="ph ph-download-simple me-1"></i>
                            Download Receipt
                        </button>

                        <button class="btn btn-light-secondary">
                            <i class="ph ph-printer me-1"></i>
                            Print Receipt
                        </button>

                        <button class="btn btn-light-danger">
                            <i class="ph ph-arrow-counter-clockwise me-1"></i>
                            Refund Payment
                        </button>
                    </div>
                </div>

            </div>

        </div>

    </main>

</x-admin-layout>