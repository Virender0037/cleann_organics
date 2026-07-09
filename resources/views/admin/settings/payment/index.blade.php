<x-admin-layout title="Payment Settings">

<main class="pc-container-edit">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Payment Settings</h4>
            <p class="text-muted mb-0">
                Configure payment gateways and transaction settings
            </p>
        </div>
    </div>

    <form action="#" method="POST">
        @csrf

        <!-- General Payment Settings -->
        <div class="card mb-4">

            <div class="card-header">
                <h5>General Settings</h5>
            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Default Currency
                        </label>

                        <select class="form-select">
                            <option selected>INR (₹)</option>
                            <option>USD ($)</option>
                            <option>EUR (€)</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Currency Symbol Position
                        </label>

                        <select class="form-select">
                            <option selected>Before Amount (₹100)</option>
                            <option>After Amount (100₹)</option>
                        </select>
                    </div>

                </div>

            </div>

        </div>

        <!-- Razorpay -->

        <div class="card mb-4">

            <div class="card-header">
                <h5>Razorpay</h5>
            </div>

            <div class="card-body">

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input"
                           type="checkbox"
                           checked>

                    <label class="form-check-label">
                        Enable Razorpay
                    </label>
                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Key ID
                        </label>

                        <input type="text"
                               class="form-control"
                               placeholder="rzp_live_xxxxxxxxx">

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Secret Key
                        </label>

                        <input type="password"
                               class="form-control"
                               placeholder="********">

                    </div>

                </div>

            </div>

        </div>

        <!-- Stripe -->

        <div class="card mb-4">

            <div class="card-header">
                <h5>Stripe</h5>
            </div>

            <div class="card-body">

                <div class="form-check form-switch mb-4">

                    <input class="form-check-input"
                           type="checkbox">

                    <label class="form-check-label">
                        Enable Stripe
                    </label>

                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Publishable Key
                        </label>

                        <input type="text"
                               class="form-control">

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Secret Key
                        </label>

                        <input type="password"
                               class="form-control">

                    </div>

                </div>

            </div>

        </div>

        <!-- Cash On Delivery -->

        <div class="card mb-4">

            <div class="card-header">
                <h5>Cash On Delivery</h5>
            </div>

            <div class="card-body">

                <div class="form-check form-switch">

                    <input class="form-check-input"
                           type="checkbox"
                           checked>

                    <label class="form-check-label">
                        Enable Cash On Delivery
                    </label>

                </div>

            </div>

        </div>

        <!-- UPI -->

        <div class="card mb-4">

            <div class="card-header">
                <h5>UPI Payments</h5>
            </div>

            <div class="card-body">

                <div class="form-check form-switch mb-4">

                    <input class="form-check-input"
                           type="checkbox"
                           checked>

                    <label class="form-check-label">
                        Enable UPI
                    </label>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        UPI ID
                    </label>

                    <input type="text"
                           class="form-control"
                           placeholder="payment@upi">

                </div>

            </div>

        </div>

        <!-- Save -->

        <div class="text-end">

            <button class="btn btn-primary">

                <i class="ph ph-floppy-disk me-1"></i>

                Save Payment Settings

            </button>

        </div>

    </form>

</main>

</x-admin-layout>