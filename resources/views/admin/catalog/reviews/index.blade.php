<x-admin-layout title="Product Reviews">
    <main class="pc-container-edit">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Product Reviews</h4>
            <p class="text-muted mb-0">Manage customer product reviews and ratings</p>
        </div>

        <div>
            <a href="#" class="btn btn-light-secondary">
                <i class="ph ph-download-simple me-1"></i>
                Export
            </a>
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <span class="mx-2">›</span>
        <span>Catalog</span>
        <span class="mx-2">›</span>
        <span>Reviews</span>
    </div>

    <div class="card">

        <div class="card-header">
            <h5>Review List</h5>
        </div>

        <div class="card-body">

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form method="GET" action="{{ route('admin.catalog.reviews.index') }}">
                <div class="row mb-3">

                    <div class="col-md-4">
                        <input type="text"
                               name="search"
                               class="form-control"
                               value="{{ request('search') }}"
                               placeholder="Search Product or Customer"
                               onchange="this.form.submit()">
                    </div>

                    <div class="col-md-3">
                        <select name="rating" class="form-select" onchange="this.form.submit()">
                            <option value="">All Ratings</option>
                            <option value="5" @selected(request('rating') == 5)>★★★★★ (5)</option>
                            <option value="4" @selected(request('rating') == 4)>★★★★☆ (4)</option>
                            <option value="3" @selected(request('rating') == 3)>★★★☆☆ (3)</option>
                            <option value="2" @selected(request('rating') == 2)>★★☆☆☆ (2)</option>
                            <option value="1" @selected(request('rating') == 1)>★☆☆☆☆ (1)</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                            <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                            <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                        </select>
                    </div>

                </div>
            </form>

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Customer</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th width="160">Action</th>
                    </tr>
                    </thead>

                    <tbody>

                    @forelse ($reviews as $review)
                        <tr>

                            <td>{{ $review->id }}</td>

                            <td>
                                <strong>{{ $review->product->name ?? '—' }}</strong>
                            </td>

                            <td>
                                {{ $review->user->name ?? '—' }}
                                <br>
                                <small class="text-muted">
                                    {{ $review->user->email ?? '' }}
                                </small>
                            </td>

                            <td>
                                {{ str_repeat('⭐', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                            </td>

                            <td style="max-width:250px">
                                {{ $review->review }}
                            </td>

                            <td>
                                @php
                                    $statusBadge = match ($review->status) {
                                        'approved' => 'bg-success',
                                        'rejected' => 'bg-danger',
                                        default => 'bg-warning',
                                    };
                                @endphp
                                <span class="badge {{ $statusBadge }}">
                                    {{ ucfirst($review->status) }}
                                </span>
                            </td>

                            <td>
                                {{ $review->created_at->format('d M Y') }}
                            </td>

                            <td>

                                @if ($review->status !== 'approved')
                                    <form action="{{ route('admin.catalog.reviews.status', $review) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                            <i class="ph ph-check"></i>
                                        </button>
                                    </form>
                                @endif

                                @if ($review->status !== 'rejected')
                                    <form action="{{ route('admin.catalog.reviews.status', $review) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="btn btn-sm btn-warning" title="Reject">
                                            <i class="ph ph-x"></i>
                                        </button>
                                    </form>
                                @endif

                                <button type="button"
                                        class="btn btn-sm btn-info"
                                        title="View"
                                        data-bs-toggle="modal"
                                        data-bs-target="#reviewModal{{ $review->id }}">
                                    <i class="ph ph-eye"></i>
                                </button>

                                <form action="{{ route('admin.catalog.reviews.destroy', $review) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this review?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </form>

                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No reviews found.</td>
                        </tr>
                    @endforelse

                    </tbody>

                </table>

            </div>

            <div class="d-flex justify-content-end">
                {{ $reviews->links() }}
            </div>

        </div>

    </div>

    @foreach ($reviews as $review)
        <div class="modal fade" id="reviewModal{{ $review->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Review #{{ $review->id }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Product:</strong> {{ $review->product->name ?? '—' }}</p>
                        <p><strong>Customer:</strong> {{ $review->user->name ?? '—' }} ({{ $review->user->email ?? '—' }})</p>
                        <p><strong>Rating:</strong> {{ str_repeat('⭐', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</p>
                        @if ($review->title)
                            <p><strong>Title:</strong> {{ $review->title }}</p>
                        @endif
                        <p><strong>Review:</strong><br>{{ $review->review }}</p>
                        <p>
                            <strong>Status:</strong>
                            @php
                                $modalStatusBadge = match ($review->status) {
                                    'approved' => 'bg-success',
                                    'rejected' => 'bg-danger',
                                    default => 'bg-warning',
                                };
                            @endphp
                            <span class="badge {{ $modalStatusBadge }}">{{ ucfirst($review->status) }}</span>
                        </p>
                        <p class="mb-0"><strong>Date:</strong> {{ $review->created_at->format('d M Y') }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</main>
</x-admin-layout>