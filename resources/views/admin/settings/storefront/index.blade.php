<x-admin-layout title="Storefront & Offers">

<main class="pc-container-edit">

    <x-admin.page-header title="Storefront & Offers" subtitle="Free-shipping rule, cart offers, earned vouchers and social links" />

    <x-admin.breadcrumb :items="[['label' => 'Settings'], ['label' => 'Storefront & Offers']]" />

    @include('admin.partials.alerts')

    <form action="{{ route('admin.settings.storefront.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card mb-4">
            <div class="card-header"><h5>Shipping</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Free shipping threshold (₹) *</label>
                        <input type="number" step="0.01" min="0" name="free_shipping_threshold" class="form-control @error('free_shipping_threshold') is-invalid @enderror" value="{{ old('free_shipping_threshold', $settings['free_shipping_threshold'] ?? '399') }}" required>
                        <div class="form-text">Applies to merchandise value after coupon discount, before shipping. Prices already include tax.</div>
                        @error('free_shipping_threshold') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Flat shipping charge below threshold (₹)</label>
                        <input type="number" step="0.01" min="0" name="flat_shipping_charge" class="form-control @error('flat_shipping_charge') is-invalid @enderror" value="{{ old('flat_shipping_charge', $settings['flat_shipping_charge'] ?? '') }}" placeholder="Not configured">
                        <div class="form-text">Used when no Shipping Zone rate matches the delivery address. Leave empty and orders below the threshold ship at ₹0 — <strong>set this before go-live</strong>. Zone rates configured under Shipping still take precedence.</div>
                        @error('flat_shipping_charge') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5>Second offer & earned voucher</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Gift + voucher threshold (₹) *</label>
                        <input type="number" step="0.01" min="0" name="gift_voucher_threshold" class="form-control @error('gift_voucher_threshold') is-invalid @enderror" value="{{ old('gift_voucher_threshold', $settings['gift_voucher_threshold'] ?? '999') }}" required>
                        @error('gift_voucher_threshold') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Voucher value (₹) *</label>
                        <input type="number" step="0.01" min="1" name="voucher_value" class="form-control @error('voucher_value') is-invalid @enderror" value="{{ old('voucher_value', $settings['voucher_value'] ?? '150') }}" required>
                        @error('voucher_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Voucher minimum order (₹)</label>
                        <input type="number" step="0.01" min="0" name="voucher_min_order" class="form-control @error('voucher_min_order') is-invalid @enderror" value="{{ old('voucher_min_order', $settings['voucher_min_order'] ?? '') }}" placeholder="0 (no minimum)">
                        @error('voucher_min_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Voucher validity (days)</label>
                        <input type="number" min="1" name="voucher_validity_days" class="form-control @error('voucher_validity_days') is-invalid @enderror" value="{{ old('voucher_validity_days', $settings['voucher_validity_days'] ?? '') }}" placeholder="365 (default)">
                        @error('voucher_validity_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <p class="text-muted small mb-0">A single-use voucher is issued to the customer automatically when a qualifying order is marked Delivered. Changes affect vouchers issued from now on.</p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5>Social</h5></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Instagram profile URL</label>
                    <input type="url" name="instagram_url" class="form-control @error('instagram_url') is-invalid @enderror" value="{{ old('instagram_url', $settings['instagram_url'] ?? '') }}" placeholder="https://www.instagram.com/your-handle/">
                    <div class="form-text">Shown on the homepage Instagram section. Leave empty to hide the "Follow us" button.</div>
                    @error('instagram_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>

</main>

</x-admin-layout>
