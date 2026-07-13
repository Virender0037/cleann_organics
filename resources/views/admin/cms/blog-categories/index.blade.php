<x-admin-layout title="Blog Categories">

<main class="pc-container-edit">

    <x-admin.page-header title="Blog Categories" subtitle="Manage blog categories">
        <x-slot:actions>
            <a href="{{ route('admin.cms.blog-categories.create') }}" class="btn btn-primary">
                <i class="ph ph-plus me-1"></i>
                Add Category
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[['label' => 'CMS'], ['label' => 'Blog Categories']]" />

    @include('admin.partials.alerts')

    <x-admin.table-card title="Category List">
        <x-slot:toolbar>
            <x-admin.filter-toolbar action="{{ route('admin.cms.blog-categories.index') }}">
                <div class="col-md-4">
                    <input type="text"
                           name="search"
                           class="form-control"
                           value="{{ request('search') }}"
                           placeholder="Search category"
                           onchange="this.form.submit()">
                </div>

                <div class="col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                    </select>
                </div>
            </x-admin.filter-toolbar>
        </x-slot:toolbar>

        <x-slot:head>
            <th>#</th>
            <th>Category</th>
            <th>Slug</th>
            <th>Blogs</th>
            <th>Status</th>
            <th width="130">Action</th>
        </x-slot:head>

        @forelse ($blogCategories as $blogCategory)
            <tr>
                <td>{{ $blogCategory->id }}</td>

                <td>
                    <strong>{{ $blogCategory->name }}</strong>
                </td>

                <td>{{ $blogCategory->slug }}</td>

                <td>{{ $blogCategory->blogs_count }} Blogs</td>

                <td>
                    <x-admin.status-badge :status="$blogCategory->status" />
                </td>

                <td>

                    <a href="{{ route('admin.cms.blog-categories.edit', $blogCategory) }}"
                       class="btn btn-sm btn-warning" title="Edit Category">
                        <i class="ph ph-pencil-simple"></i>
                    </a>

                    <form action="{{ route('admin.cms.blog-categories.destroy', $blogCategory) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this category?');">
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
                <td colspan="6">
                    <x-admin.empty-state>No blog categories found.</x-admin.empty-state>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $blogCategories->links() }}
        </x-slot:pagination>
    </x-admin.table-card>

</main>

</x-admin-layout>
