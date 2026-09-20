{{--
    Marketplace Pricing card (Admin → Catalog → Products → Edit).
    Deliberately OUTSIDE the product <form>: each action here is its own small request (add / edit / enable-disable /
    delete), saved immediately, so nested forms are never needed. Prices are typed in by hand — nothing is fetched.
--}}
@php
    $mpErrors = $errors->getBag('marketplace');
    $mpOptions = \App\Support\Marketplaces::options();
    $storeUrl = route('admin.catalog.products.marketplace-prices.store', $product);
    $reopen = $mpErrors->any();
    $formAction = old('mp_action', $storeUrl);
    $formMethod = old('mp_method', 'POST');
    $money = fn ($value) => '₹'.number_format((float) $value, 2);
    $variantLabel = fn ($variant) => $variant->displayLabel();
@endphp

<div class="card mb-4" id="marketplace-pricing">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-1">Marketplace Pricing</h5>
            <small class="text-muted">Manage external selling prices and links. Shown to customers as an optional price comparison.</small>
        </div>
        <button type="button" class="btn btn-sm btn-light-primary" data-mp-add>
            <i class="ph ph-plus me-1"></i>
            Add Marketplace
        </button>
    </div>

    <div class="card-body">
        @forelse ($product->marketplacePrices as $mp)
            @php
                $discount = $mp->discountPercent();
                $stale = $mp->isStale();
                $editData = [
                    'action' => route('admin.catalog.products.marketplace-prices.update', [$product, $mp]),
                    'marketplace' => $mp->marketplace,
                    'product_variant_id' => $mp->product_variant_id,
                    'marketplace_product_name' => $mp->marketplace_product_name,
                    'selling_price' => $mp->selling_price,
                    'mrp' => $mp->mrp,
                    'product_url' => $mp->product_url,
                    'affiliate_url' => $mp->affiliate_url,
                    'is_active' => $mp->is_active,
                    'display_order' => $mp->sort_order,
                    'last_checked_at' => $mp->last_checked_at?->format('Y-m-d'),
                ];
            @endphp
            <div class="marketplace-row {{ $mp->is_active ? '' : 'is-inactive' }}">
                <div class="marketplace-row__main">
                    <div class="marketplace-row__title">
                        <strong>{{ $mp->label() }}</strong>
                        <span class="badge {{ $mp->is_active ? 'bg-light-success text-success' : 'bg-light-secondary text-secondary' }}">{{ $mp->is_active ? 'Active' : 'Inactive' }}</span>
                        <span class="badge bg-light-secondary text-secondary">{{ $mp->product_variant_id ? 'Variant: '.($mp->variant ? $variantLabel($mp->variant).($mp->variant->trashed() ? ' (deleted)' : '') : 'unknown') : 'Product-level' }}</span>
                    </div>
                    <div class="marketplace-row__figures">
                        <span class="marketplace-row__price">{{ $mp->selling_price !== null ? $money($mp->selling_price) : 'No price' }}</span>
                        {!! $mp->mrp !== null ? '<span class="text-muted">MRP <del>'.e($money($mp->mrp)).'</del></span>' : '' !!}
                        {!! $discount ? '<span class="badge bg-light-success text-success">'.$discount.'% OFF</span>' : '' !!}
                        {!! $mp->sellingPriceExceedsMrp() ? '<span class="badge bg-light-warning text-warning" title="The selling price is above the MRP, so no discount is shown to customers">Price above MRP</span>' : '' !!}
                    </div>
                    <div class="marketplace-row__meta text-muted">
                        <span>Checked {{ $mp->last_checked_at ? $mp->last_checked_at->format('d M Y') : 'never' }}</span>
                        {!! $stale ? '<span class="text-warning" title="Not checked for over '.\App\Support\Marketplaces::staleAfterDays().' days"><i class="ph ph-warning me-1"></i>Price may be outdated</span>' : '' !!}
                        <span>{{ $mp->clicks_count }} {{ $mp->clicks_count === 1 ? 'click' : 'clicks' }}</span>
                        <span>Order {{ $mp->sort_order }}</span>
                    </div>
                </div>

                <div class="marketplace-row__actions">
                    <button type="button" class="btn btn-sm btn-warning" title="Edit {{ $mp->label() }} listing" aria-label="Edit {{ $mp->label() }} listing" data-mp-edit="{{ json_encode($editData) }}">
                        <i class="ph ph-pencil-simple"></i>
                    </button>
                    <form action="{{ route('admin.catalog.products.marketplace-prices.toggle', [$product, $mp]) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm {{ $mp->is_active ? 'btn-light-secondary' : 'btn-light-success' }}" title="{{ $mp->is_active ? 'Disable' : 'Enable' }} {{ $mp->label() }} listing" aria-label="{{ $mp->is_active ? 'Disable' : 'Enable' }} {{ $mp->label() }} listing">
                            <i class="ph {{ $mp->is_active ? 'ph-eye-slash' : 'ph-eye' }}"></i>
                        </button>
                    </form>
                    <form action="{{ route('admin.catalog.products.marketplace-prices.destroy', [$product, $mp]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete the {{ $mp->label() }} listing?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" title="Delete {{ $mp->label() }} listing" aria-label="Delete {{ $mp->label() }} listing">
                            <i class="ph ph-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-muted mb-0">No marketplace prices yet. Use <strong>Add Marketplace</strong> to compare this product against Amazon, Flipkart, Meesho and others. Products with no active listing show no comparison to customers.</p>
        @endforelse
    </div>
</div>

<dialog id="marketplace-dialog" class="admin-dialog" aria-labelledby="marketplace-dialog-title" data-reopen="{{ $reopen ? '1' : '0' }}">
    <form action="{{ $formAction }}" method="POST" id="marketplace-form" novalidate>
        @csrf
        <input type="hidden" name="_method" value="PUT" @disabled($formMethod !== 'PUT') data-mp-method />
        <input type="hidden" name="mp_action" value="{{ $formAction }}" data-mp-action />
        <input type="hidden" name="mp_method" value="{{ $formMethod }}" data-mp-methodname />

        <div class="admin-dialog__head">
            <h5 class="mb-0" id="marketplace-dialog-title" data-mp-title>{{ $formMethod === 'PUT' ? 'Edit Marketplace' : 'Add Marketplace' }}</h5>
            <button type="button" class="btn btn-sm btn-light" data-mp-close aria-label="Close"><i class="ph ph-x"></i></button>
        </div>

        <div class="admin-dialog__body">
            @if ($mpErrors->any())
                <div class="alert alert-danger py-2">
                    <ul class="mb-0 ps-3">
                        @foreach ($mpErrors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="mp-marketplace">Marketplace</label>
                    <select name="marketplace" id="mp-marketplace" class="form-select" required>
                        @foreach ($mpOptions as $key => $label)
                            <option value="{{ $key }}" @selected(old('marketplace') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="mp-variant">Variant</label>
                    <select name="product_variant_id" id="mp-variant" class="form-select">
                        <option value="">Default product (all variants)</option>
                        @foreach ($product->variants as $variant)
                            <option value="{{ $variant->id }}" @selected((string) old('product_variant_id') === (string) $variant->id)>{{ $variantLabel($variant) }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">A variant-specific listing wins for that variant; the product-level one is the fallback.</div>
                </div>
                <div class="col-12">
                    <label class="form-label" for="mp-name">Marketplace product name <span class="text-muted">(optional)</span></label>
                    <input type="text" name="marketplace_product_name" id="mp-name" class="form-control" maxlength="255" value="{{ old('marketplace_product_name') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="mp-selling">Selling price (₹)</label>
                    <input type="number" step="0.01" min="0" name="selling_price" id="mp-selling" class="form-control" value="{{ old('selling_price') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="mp-mrp">MRP (₹) <span class="text-muted">(optional)</span></label>
                    <input type="number" step="0.01" min="0" name="mrp" id="mp-mrp" class="form-control" value="{{ old('mrp') }}">
                    <div class="form-text">The discount % is calculated from MRP and selling price — never typed in.</div>
                </div>
                <div class="col-12">
                    <label class="form-label" for="mp-url">Product URL</label>
                    <input type="url" name="product_url" id="mp-url" class="form-control" maxlength="2048" placeholder="https://" value="{{ old('product_url') }}">
                </div>
                <div class="col-12">
                    <label class="form-label" for="mp-affiliate">Affiliate URL <span class="text-muted">(optional — used instead of the product URL when set)</span></label>
                    <input type="url" name="affiliate_url" id="mp-affiliate" class="form-control" maxlength="2048" placeholder="https://" value="{{ old('affiliate_url') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="mp-order">Sort order</label>
                    <input type="number" min="0" name="display_order" id="mp-order" class="form-control" value="{{ old('display_order', 0) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="mp-checked">Last checked</label>
                    <input type="date" name="last_checked_at" id="mp-checked" class="form-control" value="{{ old('last_checked_at') }}">
                    <div class="form-text">Left blank, it is set to today whenever a price changes.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label d-block">Status</label>
                    <input type="hidden" name="is_active" value="0">
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="mp-active" @checked(old('is_active', '1') == '1')>
                        <label class="form-check-label" for="mp-active">Active</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-dialog__foot">
            <button type="button" class="btn btn-light" data-mp-close>Cancel</button>
            <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk me-1"></i>Save listing</button>
        </div>
    </form>
</dialog>

<script>
    (function () {
        var dialog = document.getElementById('marketplace-dialog');
        if (!dialog || typeof dialog.showModal !== 'function') { return; }
        var form = document.getElementById('marketplace-form');
        var storeUrl = @json($storeUrl);
        var fields = ['marketplace', 'product_variant_id', 'marketplace_product_name', 'selling_price', 'mrp', 'product_url', 'affiliate_url', 'display_order', 'last_checked_at'];

        function setMode(action, isEdit, title) {
            form.action = action;
            form.querySelector('[data-mp-method]').disabled = !isEdit;
            form.querySelector('[data-mp-action]').value = action;
            form.querySelector('[data-mp-methodname]').value = isEdit ? 'PUT' : 'POST';
            dialog.querySelector('[data-mp-title]').textContent = title;
            var alertBox = dialog.querySelector('.alert-danger');
            if (alertBox) { alertBox.remove(); }
        }

        function fill(values) {
            fields.forEach(function (name) {
                var el = form.elements[name];
                if (el) { el.value = values[name] === null || values[name] === undefined ? '' : values[name]; }
            });
            form.querySelector('#mp-active').checked = !!values.is_active;
        }

        document.querySelectorAll('[data-mp-add]').forEach(function (button) {
            button.addEventListener('click', function () {
                setMode(storeUrl, false, 'Add Marketplace');
                fill({ marketplace: form.elements.marketplace.options[0].value, display_order: 0, is_active: true });
                dialog.showModal();
            });
        });

        document.querySelectorAll('[data-mp-edit]').forEach(function (button) {
            button.addEventListener('click', function () {
                var data = JSON.parse(button.getAttribute('data-mp-edit'));
                setMode(data.action, true, 'Edit Marketplace');
                fill(data);
                dialog.showModal();
            });
        });

        dialog.querySelectorAll('[data-mp-close]').forEach(function (button) {
            button.addEventListener('click', function () { dialog.close(); });
        });
        dialog.addEventListener('click', function (event) { if (event.target === dialog) { dialog.close(); } });

        // After a failed save, reopen with the submitted values (already rendered from old()) and the errors.
        if (dialog.getAttribute('data-reopen') === '1') {
            dialog.showModal();
        }
    })();
</script>
