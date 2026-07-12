<x-admin-layout title="Product Reviews">
    <main class="pc-container-edit">
    <x-admin.page-header title="Product Reviews" subtitle="Manage customer product reviews and ratings">
        <x-slot:actions>
            <a href="{{ route('admin.catalog.reviews.export', request()->query()) }}" class="btn btn-light-secondary">
                <i class="ph ph-download-simple me-1"></i>
                Export
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[['label' => 'Catalog'], ['label' => 'Reviews']]" />

    @include('admin.partials.alerts')

    <x-admin.table-card title="Review List">
        <x-slot:toolbar>
            <x-admin.filter-toolbar action="{{ route('admin.catalog.reviews.index') }}">
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
            </x-admin.filter-toolbar>
        </x-slot:toolbar>

        <x-slot:head>
            <th>#</th>
            <th>Product</th>
            <th>Customer</th>
            <th>Rating</th>
            <th>Review</th>
            <th>Status</th>
            <th>Date</th>
            <th width="160">Action</th>
        </x-slot:head>

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
                    <x-admin.status-badge :status="$review->status" />
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
                <td colspan="8">
                    <x-admin.empty-state>No reviews found.</x-admin.empty-state>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $reviews->links() }}
        </x-slot:pagination>
    </x-admin.table-card>

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
                            <x-admin.status-badge :status="$review->status" />
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
