<x-admin-layout title="Testimonials">
    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Testimonials</h4>
                <p class="text-muted mb-0">Manage customer testimonials shown on website</p>
            </div>

            <a href="{{ route('admin.cms.testimonials.create') }}" class="btn btn-primary">
                <i class="ph ph-plus me-1"></i>
                Add Testimonial
            </a>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>CMS</span>
            <span class="mx-2">›</span>
            <span>Testimonials</span>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Testimonial List</h5>
            </div>

            <div class="card-body">

                <div class="row mb-4">
                    <div class="col-md-4">
                        <input type="text" class="form-control" placeholder="Search customer name">
                    </div>

                    <div class="col-md-3">
                        <select class="form-select">
                            <option>All Status</option>
                            <option>Active</option>
                            <option>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Photo</th>
                                <th>Customer</th>
                                <th>Rating</th>
                                <th>Message</th>
                                <th>Sort Order</th>
                                <th>Status</th>
                                <th width="130">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>1</td>

                                <td>
                                    <img src="https://placehold.co/60x60"
                                         class="rounded-circle border"
                                         width="60"
                                         height="60"
                                         alt="Customer">
                                </td>

                                <td>
                                    <strong>Rahul Sharma</strong>
                                    <br>
                                    <small class="text-muted">Ahmedabad</small>
                                </td>

                                <td>⭐⭐⭐⭐⭐</td>

                                <td style="max-width: 300px;">
                                    Very good quality organic products and fast delivery.
                                </td>

                                <td>1</td>

                                <td>
                                    <span class="badge bg-success">Active</span>
                                </td>

                                <td>
                                    <a href="{{ route('admin.cms.testimonials.edit') }}"
                                       class="btn btn-sm btn-warning"
                                       title="Edit Testimonial">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger" title="Delete Testimonial">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </td>
                            </tr>

                            <tr>
                                <td>2</td>

                                <td>
                                    <img src="https://placehold.co/60x60"
                                         class="rounded-circle border"
                                         width="60"
                                         height="60"
                                         alt="Customer">
                                </td>

                                <td>
                                    <strong>Priya Patel</strong>
                                    <br>
                                    <small class="text-muted">Mumbai</small>
                                </td>

                                <td>⭐⭐⭐⭐</td>

                                <td style="max-width: 300px;">
                                    Fresh products and nice packaging. Highly recommended.
                                </td>

                                <td>2</td>

                                <td>
                                    <span class="badge bg-success">Active</span>
                                </td>

                                <td>
                                    <a href="{{ route('admin.cms.testimonials.edit') }}"
                                       class="btn btn-sm btn-warning"
                                       title="Edit Testimonial">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger" title="Delete Testimonial">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>

                    </table>
                </div>

            </div>
        </div>

    </main>
</x-admin-layout>