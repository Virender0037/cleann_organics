<x-admin-layout title="Blog Tags">

<main class="pc-container-edit">

    <x-admin.page-header title="Blog Tags" subtitle="Manage blog tags used for articles">
        <x-slot:actions>
            <a href="{{ route('admin.cms.blog-tags.create') }}" class="btn btn-primary">
                <i class="ph ph-plus me-1"></i>
                Add Tag
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[['label' => 'CMS'], ['label' => 'Blog Tags']]" />

    @include('admin.partials.alerts')

    <x-admin.table-card title="Tag List">
        <x-slot:toolbar>
            <x-admin.filter-toolbar action="{{ route('admin.cms.blog-tags.index') }}">
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
            </x-admin.filter-toolbar>
        </x-slot:toolbar>

        <x-slot:head>
            <th>#</th>
            <th>Tag Name</th>
            <th>Slug</th>
            <th>Blogs</th>
            <th>Status</th>
            <th width="130">Action</th>
        </x-slot:head>

        @forelse ($blogTags as $blogTag)
            <tr>
                <td>{{ $blogTag->id }}</td>
                <td><strong>{{ $blogTag->name }}</strong></td>
                <td>{{ $blogTag->slug }}</td>
                <td>{{ $blogTag->blogs_count }} Blogs</td>
                <td>
                    <x-admin.status-badge :status="$blogTag->status" />
                </td>
                <td>
                    <a href="{{ route('admin.cms.blog-tags.edit', $blogTag) }}"
                       class="btn btn-sm btn-warning"
                       title="Edit Tag">
                        <i class="ph ph-pencil-simple"></i>
                    </a>

                    <form action="{{ route('admin.cms.blog-tags.destroy', $blogTag) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this tag?');">
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
                <td colspan="6">
                    <x-admin.empty-state>No blog tags found.</x-admin.empty-state>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $blogTags->links() }}
        </x-slot:pagination>
    </x-admin.table-card>

</main>

</x-admin-layout>
