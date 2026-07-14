<x-admin-layout title="Add Shipping Zone">

<main class="pc-container-edit">

    <x-admin.page-header title="Add Shipping Zone" subtitle="Create a new delivery zone">
        <x-slot:actions>
            <a href="{{ route('admin.shipping.zones.index') }}" class="btn btn-light">
                <i class="ph ph-arrow-left me-1"></i>
                Back
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[
        ['label' => 'Shipping'],
        ['label' => 'Zones', 'url' => route('admin.shipping.zones.index')],
        ['label' => 'Add Zone'],
    ]" />

    @include('admin.partials.alerts')

    <x-admin.form-card title="Zone Information" action="{{ route('admin.shipping.zones.store') }}" method="POST">
        <x-slot:actions>
            <a href="{{ route('admin.shipping.zones.index') }}" class="btn btn-light">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary">
                <i class="ph ph-floppy-disk me-1"></i>
                Create Zone
            </button>
        </x-slot:actions>

        <div class="row">

            <div class="col-md-6 mb-3">
                <label class="form-label">Zone Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Gujarat Zone">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">State</label>
                <input type="text" name="state" class="form-control @error('state') is-invalid @enderror" value="{{ old('state') }}" placeholder="Gujarat">
                @error('state') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">City</label>
                <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city') }}" placeholder="Ahmedabad">
                @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Pincode</label>
                <input type="text" name="pincode" class="form-control @error('pincode') is-invalid @enderror" value="{{ old('pincode') }}" placeholder="380015">
                @error('pincode') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
