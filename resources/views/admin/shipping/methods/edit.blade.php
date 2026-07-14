<x-admin-layout title="Edit Shipping Method">

<main class="pc-container-edit">

    <x-admin.page-header title="Edit Shipping Method" subtitle="Update delivery method details">
        <x-slot:actions>
            <a href="{{ route('admin.shipping.methods.index') }}" class="btn btn-light">
                <i class="ph ph-arrow-left me-1"></i>
                Back
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[
        ['label' => 'Shipping'],
        ['label' => 'Methods', 'url' => route('admin.shipping.methods.index')],
        ['label' => 'Edit Method'],
    ]" />

    @include('admin.partials.alerts')

    <x-admin.form-card title="Method Information" action="{{ route('admin.shipping.methods.update', $method) }}" method="PUT">
        <x-slot:actions>
            <a href="{{ route('admin.shipping.methods.index') }}" class="btn btn-light">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary">
                <i class="ph ph-floppy-disk me-1"></i>
                Update Method
            </button>
        </x-slot:actions>

        <div class="row">

            <div class="col-md-6 mb-3">
                <label class="form-label">Method Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $method->name) }}">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Method Code <span class="text-danger">*</span></label>
                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $method->code) }}">
                @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Estimated Delivery</label>
                <input type="text" name="estimated_delivery" class="form-control @error('estimated_delivery') is-invalid @enderror" value="{{ old('estimated_delivery', $method->estimated_delivery) }}">
                @error('estimated_delivery') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $method->sort_order) }}">
                @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select @error('status') is-invalid @enderror">
                    <option value="active" @selected(old('status', $method->status) === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $method->status) === 'inactive')>Inactive</option>
                </select>
                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-12 mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description', $method->description) }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

        </div>
    </x-admin.form-card>

</main>

</x-admin-layout>
