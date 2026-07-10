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

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form method="GET" action="{{ route('admin.cms.testimonials.index') }}">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <input type="text"
                                   name="search"
                                   class="form-control"
                                   value="{{ request('search') }}"
                                   placeholder="Search customer name"
                                   onchange="this.form.submit()">
                        </div>

                        <div class="col-md-3">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="active" @selected(request('status') === 'active')>Active</option>
                                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                            </select>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Photo</th>
                                <th>Customer</th>
                                <th>Rating</th>
                                <th>Message</th>
                                <th>Featured</th>
                                <th>Sort Order</th>
                                <th>Status</th>
                                <th width="130">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($testimonials as $testimonial)
                                <tr>
                                    <td>{{ $testimonial->id }}</td>

                                    <td>
                                        <img src="{{ $testimonial->image ? \Illuminate\Support\Facades\Storage::url($testimonial->image) : 'https://placehold.co/60x60' }}"
                                             class="rounded-circle border"
                                             width="60"
                                             height="60"
                                             alt="Customer">
                                    </td>

                                    <td>
                                        <strong>{{ $testimonial->name }}</strong>
                                        @if ($testimonial->designation || $testimonial->company)
                                            <br>
                                            <small class="text-muted">{{ collect([$testimonial->designation, $testimonial->company])->filter()->implode(', ') }}</small>
                                        @endif
                                    </td>

                                    <td>{{ str_repeat('⭐', $testimonial->rating) }}</td>

                                    <td style="max-width: 300px;">
                                        {{ \Illuminate\Support\Str::limit($testimonial->message, 80) }}
                                    </td>

                                    <td>
                                        @if ($testimonial->is_featured)
                                            <span class="badge bg-primary">Featured</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    <td>{{ $testimonial->sort_order }}</td>

                                    <td>
                                        <span class="badge {{ $testimonial->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ucfirst($testimonial->status) }}
                                        </span>
                                    </td>

                                    <td>
                                        <a href="{{ route('admin.cms.testimonials.edit', $testimonial) }}"
                                           class="btn btn-sm btn-warning"
                                           title="Edit Testimonial">
                                            <i class="ph ph-pencil-simple"></i>
                                        </a>

                                        <form action="{{ route('admin.cms.testimonials.destroy', $testimonial) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this testimonial?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete Testimonial">
                                                <i class="ph ph-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No testimonials found.</td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                <div class="d-flex justify-content-end">
                    {{ $testimonials->links() }}
                </div>

            </div>
        </div>

    </main>
</x-admin-layout>