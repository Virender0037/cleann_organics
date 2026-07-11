<x-admin-layout title="Product Tags">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Product Tags</h4>
                <p class="text-muted mb-0">Manage tags used for products</p>
            </div>

            <a href="{{ route('admin.catalog.tags.create') }}" class="btn btn-primary">
                <i class="ph ph-plus me-1"></i>
                Add Tag
            </a>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>Catalog</span>
            <span class="mx-2">›</span>
            <span>Tags</span>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Tag List</h5>
            </div>

            <div class="card-body">

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form method="GET" action="{{ route('admin.catalog.tags.index') }}">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <input type="text"
                                   name="search"
                                   class="form-control"
                                   value="{{ request('search') }}"
                                   placeholder="Search tag"
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
                                <th>Tag Name</th>
                                <th>Slug</th>
                                <th>Products</th>
                                <th>Status</th>
                                <th width="130">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($tags as $tag)
                                <tr>
                                    <td>{{ $tag->id }}</td>
                                    <td><strong>{{ $tag->name }}</strong></td>
                                    <td>{{ $tag->slug }}</td>
                                    <td>{{ $tag->products_count }} Products</td>
                                    <td>
                                        <span class="badge {{ $tag->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ucfirst($tag->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.catalog.tags.edit', $tag) }}"
                                           class="btn btn-sm btn-warning"
                                           title="Edit Tag">
                                            <i class="ph ph-pencil-simple"></i>
                                        </a>

                                        <form action="{{ route('admin.catalog.tags.destroy', $tag) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this tag?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete Tag">
                                                <i class="ph ph-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No tags found.</td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                <div class="d-flex justify-content-end">
                    {{ $tags->links() }}
                </div>

            </div>
        </div>

    </main>

</x-admin-layout>