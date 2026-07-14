<x-admin-layout title="Payment Settings">

<main class="pc-container-edit">

    <x-admin.page-header title="Payment Settings" subtitle="Configure payment gateways and transaction settings" />

    <x-admin.breadcrumb :items="[['label' => 'Settings'], ['label' => 'Payment']]" />

    @include('admin.partials.alerts')

    <form action="{{ route('admin.settings.payment.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card mb-4">
            <div class="card-header">
                <h5>General Settings</h5>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Default Currency</label>
                        <select name="default_currency" class="form-select @error('default_currency') is-invalid @enderror">
                            <option value="INR" @selected(old('default_currency', $settings['default_currency'] ?? 'INR') === 'INR')>INR (₹)</option>
                            <option value="USD" @selected(old('default_currency', $settings['default_currency'] ?? '') === 'USD')>USD ($)</option>
                            <option value="EUR" @selected(old('default_currency', $settings['default_currency'] ?? '') === 'EUR')>EUR (€)</option>
                        </select>
                        @error('default_currency') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Currency Symbol Position</label>
                        <select name="currency_symbol_position" class="form-select @error('currency_symbol_position') is-invalid @enderror">
                            <option value="before" @selected(old('currency_symbol_position', $settings['currency_symbol_position'] ?? 'before') === 'before')>Before Amount (₹100)</option>
                            <option value="after" @selected(old('currency_symbol_position', $settings['currency_symbol_position'] ?? '') === 'after')>After Amount (100₹)</option>
                        </select>
                        @error('currency_symbol_position') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5>Razorpay</h5>
            </div>

            <div class="card-body">
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" name="enable_razorpay" value="1" id="enable_razorpay" @checked(old('enable_razorpay', $settings['enable_razorpay'] ?? '1') == '1')>
                    <label class="form-check-label" for="enable_razorpay">Enable Razorpay</label>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Key ID</label>
                        <input type="text" name="razorpay_key_id" class="form-control @error('razorpay_key_id') is-invalid @enderror" value="{{ old('razorpay_key_id', $settings['razorpay_key_id'] ?? '') }}" placeholder="rzp_live_xxxxxxxxx">
                        @error('razorpay_key_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Secret Key</label>
                        <input type="password" name="razorpay_secret_key" class="form-control @error('razorpay_secret_key') is-invalid @enderror" placeholder="{{ ! empty($settings['razorpay_secret_key']) ? 'Currently set — leave blank to keep' : '********' }}">
                        @error('razorpay_secret_key') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5>Stripe</h5>
            </div>

            <div class="card-body">
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" name="enable_stripe" value="1" id="enable_stripe" @checked(old('enable_stripe', $settings['enable_stripe'] ?? '0') == '1')>
                    <label class="form-check-label" for="enable_stripe">Enable Stripe</label>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Publishable Key</label>
                        <input type="text" name="stripe_publishable_key" class="form-control @error('stripe_publishable_key') is-invalid @enderror" value="{{ old('stripe_publishable_key', $settings['stripe_publishable_key'] ?? '') }}">
                        @error('stripe_publishable_key') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Secret Key</label>
                        <input type="password" name="stripe_secret_key" class="form-control @error('stripe_secret_key') is-invalid @enderror" placeholder="{{ ! empty($settings['stripe_secret_key']) ? 'Currently set — leave blank to keep' : '' }}">
                        @error('stripe_secret_key') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5>Cash On Delivery</h5>
            </div>

            <div class="card-body">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="enable_cod" value="1" id="enable_cod" @checked(old('enable_cod', $settings['enable_cod'] ?? '1') == '1')>
                    <label class="form-check-label" for="enable_cod">Enable Cash On Delivery</label>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5>UPI Payments</h5>
            </div>

            <div class="card-body">
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" name="enable_upi" value="1" id="enable_upi" @checked(old('enable_upi', $settings['enable_upi'] ?? '1') == '1')>
                    <label class="form-check-label" for="enable_upi">Enable UPI</label>
                </div>

                <div class="mb-3">
                    <label class="form-label">UPI ID</label>
                    <input type="text" name="upi_id" class="form-control @error('upi_id') is-invalid @enderror" value="{{ old('upi_id', $settings['upi_id'] ?? '') }}" placeholder="payment@upi">
                    @error('upi_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-primary">
                <i class="ph ph-floppy-disk me-1"></i>
                Save Payment Settings
            </button>
        </div>

    </form>

</main>

</x-admin-layout>
