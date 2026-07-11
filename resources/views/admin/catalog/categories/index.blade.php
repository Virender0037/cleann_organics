<x-admin-layout title="Categories">
<main class="pc-container">    
<div class="pc-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1">Categories</h4>
                        <p class="text-muted mb-0">
                            Manage product categories
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('admin.catalog.categories.import') }}" class="btn btn-light-primary me-2">
                            <i class="ph ph-upload-simple"></i>
                            Import
                        </a>
                        <a href="{{ route('admin.catalog.categories.export', request()->query()) }}" class="btn btn-light-secondary me-2">
                            <i class="ph ph-download-simple"></i>
                            Export
                        </a>
                        <a href="{{ route('admin.catalog.categories.create') }}"
                        class="btn btn-primary">
                            <i class="ph ph-plus"></i>
                            Add Category
                        </a>
                    </div>
                </div>
                </div>
            </div>
        </div>

        <!-- Breadcrumb -->
        <div class="row">
            <div class="col-sm-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">Catalog</li>
                    <li class="breadcrumb-item active">Categories</li>
                </ul>
            </div>
        </div>

        <!-- Category Table -->
        <div class="card">
            <div class="card-header">
                <h5>Category List</h5>
            </div>

            <div class="card-body">

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="row mb-3">
                    <div class="col-md-4">
                        <form method="GET" action="{{ route('admin.catalog.categories.index') }}">
                            <input type="text"
                                   name="search"
                                   class="form-control"
                                   value="{{ request('search') }}"
                                   placeholder="Search Category"
                                   onchange="this.form.submit()">
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">

                        <thead>
                            <tr>
                                <th width="80">#</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Parent Category</th>
                                <th>Sort Order</th>
                                <th>Status</th>
                                <th>Products</th>
                                <th width="150">Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse ($categories as $category)
                                <tr>
                                    <td>{{ $category->id }}</td>

                                    <td>
                                        <img src="{{ $category->image ? \Illuminate\Support\Facades\Storage::url($category->image) : asset('assets/images/placeholder.png') }}" width="40" height="40" class="rounded" alt="Category">
                                    </td>

                                    <td>{{ $category->name }}</td>

                                    <td>
                                        {{ $category->slug }}
                                    </td>

                                    <td>{{ $category->parent->name ?? '—' }}</td>

                                    <td>{{ $category->sort_order }}</td>

                                    <td>
                                        <span class="badge {{ $category->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ucfirst($category->status) }}
                                        </span>
                                    </td>

                                    <td>{{ $category->products_count }}</td>

                                    <td>
                                        <a href="{{ route('admin.catalog.categories.edit', $category) }}" class="btn btn-sm btn-info" title="Edit Category">
                                            <i class="ph ph-pencil-simple"></i>
                                        </a>

                                        <form action="{{ route('admin.catalog.categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this category?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete Category">
                                                <i class="ph ph-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No categories found.</td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>
                </div>
                <!-- Pagination -->
                <div class="d-flex justify-content-end">
                    {{ $categories->links() }}
                </div>

            </div>
        </div>
    </div>
</main>
</x-admin-layout>