<x-admin-layout title="FAQs">
    <main class="pc-container-edit">

        <x-admin.page-header title="FAQs" subtitle="Manage frequently asked questions">
            <x-slot:actions>
                <a href="{{ route('admin.cms.faqs.create') }}" class="btn btn-primary">
                    <i class="ph ph-plus me-1"></i>
                    Add FAQ
                </a>
            </x-slot:actions>
        </x-admin.page-header>

        <x-admin.breadcrumb :items="[['label' => 'CMS'], ['label' => 'FAQs']]" />

        @include('admin.partials.alerts')

        <x-admin.table-card title="FAQ List">
            <x-slot:toolbar>
                <x-admin.filter-toolbar action="{{ route('admin.cms.faqs.index') }}">
                    <div class="col-md-4">
                        <input type="text"
                               name="search"
                               class="form-control"
                               value="{{ request('search') }}"
                               placeholder="Search question"
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
                <th>Question</th>
                <th>Sort Order</th>
                <th>Status</th>
                <th width="130">Action</th>
            </x-slot:head>

            @forelse ($faqs as $faq)
                <tr>
                    <td>{{ $faq->id }}</td>
                    <td><strong>{{ $faq->question }}</strong></td>
                    <td>{{ $faq->sort_order }}</td>
                    <td>
                        <x-admin.status-badge :status="$faq->status" />
                    </td>
                    <td>
                        <a href="{{ route('admin.cms.faqs.edit', $faq) }}" class="btn btn-sm btn-warning" title="Edit FAQ">
                            <i class="ph ph-pencil-simple"></i>
                        </a>

                        <form action="{{ route('admin.cms.faqs.destroy', $faq) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this FAQ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Delete FAQ">
                                <i class="ph ph-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <x-admin.empty-state>No FAQs found.</x-admin.empty-state>
                    </td>
                </tr>
            @endforelse

            <x-slot:pagination>
                {{ $faqs->links() }}
            </x-slot:pagination>
        </x-admin.table-card>

    </main>
</x-admin-layout>
