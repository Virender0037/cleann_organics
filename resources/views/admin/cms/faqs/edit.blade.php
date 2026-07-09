<x-admin-layout title="Edit FAQ">
    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Edit FAQ</h4>
                <p class="text-muted mb-0">Update frequently asked question</p>
            </div>

            <a href="{{ route('admin.cms.faqs.index') }}" class="btn btn-light">
                <i class="ph ph-arrow-left me-1"></i>
                Back
            </a>
        </div>

        <form action="#" method="POST">
            @csrf
            @method('PUT')

            <div class="card">
                <div class="card-header">
                    <h5>FAQ Information</h5>
                </div>

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Question <span class="text-danger">*</span></label>
                            <input type="text" name="question" class="form-control" value="How can I track my order?">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <input type="text" name="category" class="form-control" value="Orders">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="1">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Answer <span class="text-danger">*</span></label>
                            <textarea name="answer" class="form-control" rows="6">You can track your order from your account dashboard using the tracking link.</textarea>
                        </div>

                    </div>

                    <div class="text-end mt-4">
                        <a href="{{ route('admin.cms.faqs.index') }}" class="btn btn-light">Cancel</a>

                        <button type="submit" class="btn btn-primary">
                            <i class="ph ph-floppy-disk me-1"></i>
                            Update FAQ
                        </button>
                    </div>
                </div>
            </div>

        </form>

    </main>
</x-admin-layout>