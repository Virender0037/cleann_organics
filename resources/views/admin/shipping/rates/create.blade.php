<x-admin-layout title="Add Shipping Rate">

<main class="pc-container-edit">

    <x-admin.page-header title="Add Shipping Rate" subtitle="Create shipping charge for a zone and weight range">
        <x-slot:actions>
            <a href="{{ route('admin.shipping.rates.index') }}" class="btn btn-light">
                <i class="ph ph-arrow-left me-1"></i>
                Back
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[
        ['label' => 'Shipping'],
        ['label' => 'Rates', 'url' => route('admin.shipping.rates.index')],
        ['label' => 'Add Rate'],
    ]" />

    @include('admin.partials.alerts')

    <x-admin.form-card title="Rate Information" action="{{ route('admin.shipping.rates.store') }}" method="POST">
        <x-slot:actions>
            <a href="{{ route('admin.shipping.rates.index') }}" class="btn btn-light">Cancel</a>

            <button type="submit" class="btn btn-primary">
                <i class="ph ph-floppy-disk me-1"></i>
                Create Rate
            </button>
        </x-slot:actions>

        <div class="row">

            <div class="col-md-6 mb-3">
                <label class="form-label">Shipping Zone <span class="text-danger">*</span></label>
                <select name="shipping_zone_id" class="form-select @error('shipping_zone_id') is-invalid @enderror">
                    <option value="">Select Zone</option>
                    @foreach ($zones as $zone)
                        <option value="{{ $zone->id }}" @selected((int) old('shipping_zone_id') === $zone->id)>
                            {{ $zone->name }}
                        </option>
                    @endforeach
                </select>
                @error('shipping_zone_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label">Min Weight</label>
                <input type="number" step="0.01" name="min_weight" class="form-control @error('min_weight') is-invalid @enderror" value="{{ old('min_weight', 0) }}">
                @error('min_weight') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label">Max Weight</label>
                <input type="number" step="0.01" name="max_weight" class="form-control @error('max_weight') is-invalid @enderror" value="{{ old('max_weight') }}" placeholder="2.00">
                @error('max_weight') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Shipping Charge <span class="text-danger">*</span></label>
                <input type="number" step="0.01" name="shipping_charge" class="form-control @error('shipping_charge') is-invalid @enderror" value="{{ old('shipping_charge') }}" placeholder="50.00">
                @error('shipping_charge') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Free Shipping Above</label>
                <input type="number" step="0.01" name="free_shipping_above" class="form-control @error('free_shipping_above') is-invalid @enderror" value="{{ old('free_shipping_above') }}" placeholder="999.00">
                @error('free_shipping_above') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select @error('status') is-invalid @enderror">
                    <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                </select>
                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

        </div>
    </x-admin.form-card>

</main>

</x-admin-layout>
