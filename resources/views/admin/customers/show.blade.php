<x-admin-layout title="Customer Details">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Customer Details</h4>
                <p class="text-muted mb-0">View customer profile, orders, wishlist and activity</p>
            </div>

            <a href="{{ route('admin.customers.index') }}" class="btn btn-light">
                <i class="ph ph-arrow-left me-1"></i>
                Back
            </a>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <a href="{{ route('admin.customers.index') }}">Customers</a>
            <span class="mx-2">›</span>
            <span>Customer Details</span>
        </div>

        <div class="row">

            <div class="col-lg-4">

                <div class="card mb-4">
                    <div class="card-body text-center">
                        <img src="https://placehold.co/120x120"
                             class="rounded-circle mb-3"
                             width="120"
                             height="120"
                             alt="Customer">

                        <h5 class="mb-1">Rahul Sharma</h5>
                        <p class="text-muted mb-2">rahul@email.com</p>

                        <span class="badge bg-success">Active</span>

                        <hr>

                        <p class="mb-1"><strong>Phone:</strong> +91 9876543210</p>
                        <p class="mb-1"><strong>Joined:</strong> 12 Jan 2026</p>
                        <p class="mb-0"><strong>Last Login:</strong> 26 Jun 2026</p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Customer Summary</h5>
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Orders</span>
                            <strong>18</strong>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Spent</span>
                            <strong>₹18,450</strong>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Wishlist Items</span>
                            <strong>6</strong>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span>Reviews</span>
                            <strong>4</strong>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Default Address</h5>
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

            </div>

            <div class="col-lg-8">

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Order History</h5>
                    </div>

                    <div class="card-body table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td>#ORD-1001</td>
                                    <td>26 Jun 2026</td>
                                    <td>₹1,250</td>
                                    <td><span class="badge bg-success">Paid</span></td>
                                    <td><span class="badge bg-primary">Shipped</span></td>
                                    <td>
                                        <a href="{{ route('admin.sales.orders.show') }}"
                                           class="btn btn-sm btn-info">
                                            <i class="ph ph-eye"></i>
                                        </a>
                                    </td>
                                </tr>

                                <tr>
                                    <td>#ORD-0998</td>
                                    <td>20 Jun 2026</td>
                                    <td>₹799</td>
                                    <td><span class="badge bg-success">Paid</span></td>
                                    <td><span class="badge bg-success">Delivered</span></td>
                                    <td>
                                        <a href="{{ route('admin.sales.orders.show') }}"
                                           class="btn btn-sm btn-info">
                                            <i class="ph ph-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Wishlist</h5>
                    </div>

                    <div class="card-body table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Added On</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td>Organic Honey</td>
                                    <td>Organic Foods</td>
                                    <td>22 Jun 2026</td>
                                </tr>

                                <tr>
                                    <td>Cold Pressed Oil</td>
                                    <td>Organic Oils</td>
                                    <td>20 Jun 2026</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Product Reviews</h5>
                    </div>

                    <div class="card-body">
                        <div class="border-bottom pb-3 mb-3">
                            <strong>Organic Honey</strong>
                            <p class="mb-1">⭐⭐⭐⭐⭐</p>
                            <p class="mb-0">Very good quality product.</p>
                        </div>

                        <div>
                            <strong>Cold Pressed Oil</strong>
                            <p class="mb-1">⭐⭐⭐⭐</p>
                            <p class="mb-0">Packaging was good and delivery was fast.</p>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Communication History</h5>
                    </div>

                    <div class="card-body table-responsive">
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
                                    <td>26 Jun 2026</td>
                                    <td>Email</td>
                                    <td>Order shipped notification</td>
                                    <td><span class="badge bg-success">Sent</span></td>
                                </tr>

                                <tr>
                                    <td>25 Jun 2026</td>
                                    <td>SMS</td>
                                    <td>Payment confirmation</td>
                                    <td><span class="badge bg-success">Sent</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>

    </main>

</x-admin-layout>