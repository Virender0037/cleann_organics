<x-admin-layout title="Edit Tax Rate">
 <main class="pc-container-edit">
    <x-admin.page-header title="Edit Tax Rate" subtitle="Update GST / tax rate details">
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
        ['label' => 'Edit Tax Rate'],
    ]" />

    @include('admin.partials.alerts')

    <x-admin.form-card title="Tax Rate Information" action="{{ route('admin.catalog.tax-rates.update', $taxRate) }}" method="PUT">
        <div class="row">

            <div class="col-md-6 mb-3">
                <label class="form-label">Tax Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $taxRate->name) }}">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Rate (%) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" max="100" name="percentage" class="form-control @error('percentage') is-invalid @enderror" value="{{ old('percentage', $taxRate->percentage) }}">
                @error('percentage') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select @error('status') is-invalid @enderror">
                    <option value="active" @selected(old('status', $taxRate->status) === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $taxRate->status) === 'inactive')>Inactive</option>
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
                Update Tax Rate
            </button>
        </x-slot:actions>
    </x-admin.form-card>
</main>
</x-admin-layout>
