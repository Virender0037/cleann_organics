<x-admin-layout title="Testimonials">
    <main class="pc-container-edit">

        <x-admin.page-header title="Testimonials" subtitle="Manage customer testimonials shown on website">
            <x-slot:actions>
                <a href="{{ route('admin.cms.testimonials.create') }}" class="btn btn-primary">
                    <i class="ph ph-plus me-1"></i>
                    Add Testimonial
                </a>
            </x-slot:actions>
        </x-admin.page-header>

        <x-admin.breadcrumb :items="[['label' => 'CMS'], ['label' => 'Testimonials']]" />

        @include('admin.partials.alerts')

        <x-admin.table-card title="Testimonial List">
            <x-slot:toolbar>
                <x-admin.filter-toolbar action="{{ route('admin.cms.testimonials.index') }}">
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
                </x-admin.filter-toolbar>
            </x-slot:toolbar>

            <x-slot:head>
                <th>#</th>
                <th>Photo</th>
                <th>Customer</th>
                <th>Rating</th>
                <th>Message</th>
                <th>Featured</th>
                <th>Sort Order</th>
                <th>Status</th>
                <th width="130">Action</th>
            </x-slot:head>

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
                        <x-admin.status-badge :status="$testimonial->status" />
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
                    <td colspan="9">
                        <x-admin.empty-state>No testimonials found.</x-admin.empty-state>
                    </td>
                </tr>
            @endforelse

            <x-slot:pagination>
                {{ $testimonials->links() }}
            </x-slot:pagination>
        </x-admin.table-card>

    </main>
</x-admin-layout>
