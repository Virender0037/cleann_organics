<x-admin-layout title="Add Tax Rate">
 <main class="pc-container-edit">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Add Tax Rate</h4>
            <p class="text-muted mb-0">Create GST / tax rate for products</p>
        </div>

        <a href="{{ route('admin.catalog.tax-rates.index') }}" class="btn btn-light">
            <i class="ph ph-arrow-left me-1"></i>
            Back
        </a>
    </div>

    <div class="mb-3">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <span class="mx-2">›</span>
        <span>Catalog</span>
        <span class="mx-2">›</span>
        <a href="{{ route('admin.catalog.tax-rates.index') }}">Tax Rates</a>
        <span class="mx-2">›</span>
        <span>Add Tax Rate</span>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Tax Rate Information</h5>
        </div>

        <div class="card-body">
            <form action="#" method="POST">
                @csrf

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tax Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="GST 5%">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Rate (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="rate" class="form-control" placeholder="5.00">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                </div>

                <div class="text-end mt-4">
                    <a href="{{ route('admin.catalog.tax-rates.index') }}" class="btn btn-light">
                        Cancel
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="ph ph-floppy-disk me-1"></i>
                        Create Tax Rate
                    </button>
                </div>

            </form>
        </div>
    </div>
</main>
</x-admin-layout>