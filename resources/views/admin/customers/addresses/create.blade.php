<x-admin-layout title="Add Address">
    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Add Address</h4>
                <p class="text-muted mb-0">Add a new customer address</p>
            </div>

            <a href="{{ route('admin.customers.addresses.index') }}" class="btn btn-light">
                <i class="ph ph-arrow-left me-1"></i>
                Back
            </a>
        </div>

        <form action="#" method="POST">
            @csrf

            <div class="card">
                <div class="card-header">
                    <h5>Address Information</h5>
                </div>

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address Type</label>
                            <select name="type" class="form-select">
                                <option value="shipping">Shipping</option>
                                <option value="billing">Billing</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Rahul Sharma">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="+91 9876543210">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="pincode" class="form-control" placeholder="380015">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Address Line 1</label>
                            <input type="text" name="address_line_1" class="form-control" placeholder="House / Flat / Building">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" name="address_line_2" class="form-control" placeholder="Area / Landmark">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" placeholder="Ahmedabad">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" placeholder="Gujarat">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-control" value="India">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Default Address</label>
                            <select name="is_default" class="form-select">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>

                    </div>

                    <div class="text-end">
                        <a href="{{ route('admin.customers.addresses.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ph ph-floppy-disk me-1"></i>
                            Save Address
                        </button>
                    </div>
                </div>
            </div>

        </form>

    </main>
</x-admin-layout>