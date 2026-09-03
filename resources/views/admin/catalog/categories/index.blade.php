<x-admin-layout title="Categories">
<main class="pc-container-edit">

    <x-admin.page-header title="Categories" subtitle="Manage product categories">
        <x-slot:actions>
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
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[['label' => 'Catalog'], ['label' => 'Categories']]" />

    @include('admin.partials.alerts')

    <x-admin.table-card title="Category List">
        <x-slot:toolbar>
            <x-admin.filter-toolbar action="{{ route('admin.catalog.categories.index') }}">
                <div class="col-md-4">
                    <input type="text"
                           name="search"
                           class="form-control"
                           value="{{ request('search') }}"
                           placeholder="Search Category"
                           onchange="this.form.submit()">
                </div>
            </x-admin.filter-toolbar>
        </x-slot:toolbar>

        <x-slot:head>
            <th width="80">#</th>
            <th>Image</th>
            <th>Name</th>
            <th>Slug</th>
            <th>Parent Category</th>
            <th>Sort Order</th>
            <th>Status</th>
            <th>Products</th>
            <th width="150">Action</th>
        </x-slot:head>

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
                    <x-admin.status-badge :status="$category->status" />
                </td>

                <td>{{ $category->products_count }}</td>

                <td>
                    <a href="{{ route('admin.catalog.categories.edit', $category) }}" class="btn btn-sm btn-warning" title="Edit Category">
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
                <td colspan="9">
                    <x-admin.empty-state>No categories found.</x-admin.empty-state>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $categories->links() }}
        </x-slot:pagination>
    </x-admin.table-card>

</main>
</x-admin-layout>
