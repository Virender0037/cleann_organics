<x-admin-layout title="Edit Shipping Rate">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Edit Shipping Rate</h4>
                <p class="text-muted mb-0">Update shipping charge details</p>
            </div>

            <a href="{{ route('admin.shipping.rates.index') }}" class="btn btn-light">
                <i class="ph ph-arrow-left me-1"></i>
                Back
            </a>
        </div>

        <form action="{{ route('admin.shipping.rates.update', $rate) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card">
                <div class="card-header">
                    <h5>Rate Information</h5>
                </div>

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Shipping Zone <span class="text-danger">*</span></label>
                            <select name="shipping_zone_id" class="form-select @error('shipping_zone_id') is-invalid @enderror">
                                <option value="">Select Zone</option>
                                @foreach ($zones as $zone)
                                    <option value="{{ $zone->id }}" @selected((int) old('shipping_zone_id', $rate->shipping_zone_id) === $zone->id)>
                                        {{ $zone->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('shipping_zone_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Min Weight</label>
                            <input type="number" step="0.01" name="min_weight" class="form-control @error('min_weight') is-invalid @enderror" value="{{ old('min_weight', $rate->min_weight) }}">
                            @error('min_weight') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Max Weight</label>
                            <input type="number" step="0.01" name="max_weight" class="form-control @error('max_weight') is-invalid @enderror" value="{{ old('max_weight', $rate->max_weight) }}">
                            @error('max_weight') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Shipping Charge <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="shipping_charge" class="form-control @error('shipping_charge') is-invalid @enderror" value="{{ old('shipping_charge', $rate->shipping_charge) }}">
                            @error('shipping_charge') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Free Shipping Above</label>
                            <input type="number" step="0.01" name="free_shipping_above" class="form-control @error('free_shipping_above') is-invalid @enderror" value="{{ old('free_shipping_above', $rate->free_shipping_above) }}">
                            @error('free_shipping_above') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active" @selected(old('status', $rate->status) === 'active')>Active</option>
                                <option value="inactive" @selected(old('status', $rate->status) === 'inactive')>Inactive</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                    </div>

                    <div class="text-end mt-4">
                        <a href="{{ route('admin.shipping.rates.index') }}" class="btn btn-light">Cancel</a>

                        <button type="submit" class="btn btn-primary">
                            <i class="ph ph-floppy-disk me-1"></i>
                            Update Rate
                        </button>
                    </div>
                </div>
            </div>

        </form>

    </main>

</x-admin-layout>