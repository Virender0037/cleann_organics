<x-admin-layout title="Edit Customer">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Edit Customer</h4>
                <p class="text-muted mb-0">Update customer account information</p>
            </div>

            <a href="{{ route('admin.customers.index') }}" class="btn btn-light">
                <i class="ph ph-arrow-left me-1"></i>
                Back
            </a>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <a href="{{ route('admin.customers.index') }}">Customers</a>
            <span class="mx-2">›</span>
            <span>Edit Customer</span>
        </div>

        <form action="#" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card">
                <div class="card-header">
                    <h5>Customer Information</h5>
                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer Name</label>
                            <input type="text" name="name" class="form-control" value="Rahul Sharma">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="rahul@email.com">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="+91 9876543210">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="blocked">Blocked</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Profile Image</label>
                            <input type="file" name="avatar" class="form-control">

                            <div class="mt-2">
                                <img src="https://placehold.co/90x90"
                                     class="rounded border"
                                     width="90"
                                     height="90"
                                     alt="Customer">
                            </div>
                        </div>

                    </div>

                    <div class="text-end mt-4">
                        <a href="{{ route('admin.customers.index') }}" class="btn btn-light">
                            Cancel
                        </a>

                        <button type="submit" class="btn btn-primary">
                            <i class="ph ph-floppy-disk me-1"></i>
                            Update Customer
                        </button>
                    </div>

                </div>
            </div>

        </form>

    </main>

</x-admin-layout>