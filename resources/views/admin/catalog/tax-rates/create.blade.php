<x-admin-layout title="Add Tax Rate">
 <main class="pc-container-edit">
    <x-admin.page-header title="Add Tax Rate" subtitle="Create GST / tax rate for products">
        <x-slot:actions>
            <a href="{{ route('admin.catalog.tax-rates.index') }}" class="btn btn-light">
                <i class="ph ph-arrow-left me-1"></i>
                Back
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[
        ['label' => 'Catalog'],
        ['label' => 'Tax Rates', 'url' => route('admin.catalog.tax-rates.index')],
        ['label' => 'Add Tax Rate'],
    ]" />

    @include('admin.partials.alerts')

    <x-admin.form-card title="Tax Rate Information" action="{{ route('admin.catalog.tax-rates.store') }}">
        <div class="row">

            <div class="col-md-6 mb-3">
                <label class="form-label">Tax Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="GST 5%">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Rate (%) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" max="100" name="percentage" class="form-control @error('percentage') is-invalid @enderror" value="{{ old('percentage') }}" placeholder="5.00">
                @error('percentage') <div class="invalid-feedback">{{ $message }}</div> @enderror
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

        <x-slot:actions>
            <a href="{{ route('admin.catalog.tax-rates.index') }}" class="btn btn-light">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary">
                <i class="ph ph-floppy-disk me-1"></i>
                Create Tax Rate
            </button>
        </x-slot:actions>
    </x-admin.form-card>
</main>
</x-admin-layout>
