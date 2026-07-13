<x-admin-layout title="CMS Pages">

    <main class="pc-container-edit">

        <x-admin.page-header title="CMS Pages" subtitle="Manage website static pages like About, Privacy Policy and Terms">
            <x-slot:actions>
                <a href="{{ route('admin.cms.pages.create') }}" class="btn btn-primary">
                    <i class="ph ph-plus me-1"></i>
                    Add Page
                </a>
            </x-slot:actions>
        </x-admin.page-header>

        <x-admin.breadcrumb :items="[['label' => 'CMS'], ['label' => 'Pages']]" />

        @include('admin.partials.alerts')

        <x-admin.table-card title="Page List">
            <x-slot:toolbar>
                <x-admin.filter-toolbar action="{{ route('admin.cms.pages.index') }}">
                    <div class="col-md-4">
                        <input type="text"
                               name="search"
                               class="form-control"
                               value="{{ request('search') }}"
                               placeholder="Search page title or slug"
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
                <th>Page Title</th>
                <th>Slug</th>
                <th>Status</th>
                <th>Last Updated</th>
                <th width="130">Action</th>
            </x-slot:head>

            @forelse ($pages as $page)
                <tr>
                    <td>{{ $page->id }}</td>
                    <td><strong>{{ $page->title }}</strong></td>
                    <td>{{ $page->slug }}</td>
                    <td>
                        <x-admin.status-badge :status="$page->status" />
                    </td>
                    <td>{{ $page->updated_at->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('admin.cms.pages.edit', $page) }}" class="btn btn-sm btn-warning" title="Edit Page">
                            <i class="ph ph-pencil-simple"></i>
                        </a>

                        <form action="{{ route('admin.cms.pages.destroy', $page) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this page?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Delete Page">
                                <i class="ph ph-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <x-admin.empty-state>No pages found.</x-admin.empty-state>
                    </td>
                </tr>
            @endforelse

            <x-slot:pagination>
                {{ $pages->links() }}
            </x-slot:pagination>
        </x-admin.table-card>

    </main>

</x-admin-layout>
