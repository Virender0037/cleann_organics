<x-admin-layout title="Blogs">

<main class="pc-container-edit">

    <x-admin.page-header title="Blogs" subtitle="Manage blog posts and articles">
        <x-slot:actions>
            <a href="{{ route('admin.cms.blogs.create') }}" class="btn btn-primary">
                <i class="ph ph-plus me-1"></i>
                Add Blog
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[['label' => 'CMS'], ['label' => 'Blogs']]" />

    @include('admin.partials.alerts')

    <x-admin.table-card title="Blog List">
        <x-slot:toolbar>
            <x-admin.filter-toolbar action="{{ route('admin.cms.blogs.index') }}">
                <div class="col-md-4">
                    <input type="text"
                           name="search"
                           class="form-control"
                           value="{{ request('search') }}"
                           placeholder="Search blog title or slug"
                           onchange="this.form.submit()">
                </div>

                <div class="col-md-3">
                    <select name="blog_category_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) request('blog_category_id') === $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="published" @selected(request('status') === 'published')>Published</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        <option value="archived" @selected(request('status') === 'archived')>Archived</option>
                    </select>
                </div>
            </x-admin.filter-toolbar>
        </x-slot:toolbar>

        <x-slot:head>
            <th>#</th>
            <th>Image</th>
            <th>Title</th>
            <th>Category</th>
            <th>Status</th>
            <th>Published On</th>
            <th width="130">Action</th>
        </x-slot:head>

        @forelse ($blogs as $blog)
            <tr>
                <td>{{ $blog->id }}</td>
                <td>
                    <img src="{{ $blog->featured_image ? \Illuminate\Support\Facades\Storage::url($blog->featured_image) : 'https://placehold.co/60x60' }}"
                         class="rounded border"
                         width="60"
                         height="60"
                         alt="Blog">
                </td>
                <td>
                    <strong>{{ $blog->title }}</strong>
                    <br>
                    <small class="text-muted">{{ $blog->slug }}</small>
                </td>
                <td>{{ $blog->category->name ?? '—' }}</td>
                <td>
                    <x-admin.status-badge :status="$blog->status" :map="['published' => 'success', 'archived' => 'secondary']" />
                </td>
                <td>{{ $blog->published_at?->format('d M Y') ?? '—' }}</td>
                <td>
                    <a href="{{ route('admin.cms.blogs.edit', $blog) }}" class="btn btn-sm btn-warning" title="Edit Blog">
                        <i class="ph ph-pencil-simple"></i>
                    </a>

                    <form action="{{ route('admin.cms.blogs.destroy', $blog) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this blog?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" title="Delete Blog">
                            <i class="ph ph-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7">
                    <x-admin.empty-state>No blogs found.</x-admin.empty-state>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $blogs->links() }}
        </x-slot:pagination>
    </x-admin.table-card>

</main>

</x-admin-layout>
