<x-admin-layout title="FAQs">
    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">FAQs</h4>
                <p class="text-muted mb-0">Manage frequently asked questions</p>
            </div>

            <a href="{{ route('admin.cms.faqs.create') }}" class="btn btn-primary">
                <i class="ph ph-plus me-1"></i>
                Add FAQ
            </a>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>CMS</span>
            <span class="mx-2">›</span>
            <span>FAQs</span>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>FAQ List</h5>
            </div>

            <div class="card-body">

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form method="GET" action="{{ route('admin.cms.faqs.index') }}">
                    <div class="row mb-4">
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
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Question</th>
                                <th>Sort Order</th>
                                <th>Status</th>
                                <th width="130">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($faqs as $faq)
                                <tr>
                                    <td>{{ $faq->id }}</td>
                                    <td><strong>{{ $faq->question }}</strong></td>
                                    <td>{{ $faq->sort_order }}</td>
                                    <td>
                                        <span class="badge {{ $faq->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ucfirst($faq->status) }}
                                        </span>
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
                                    <td colspan="5" class="text-center text-muted">No FAQs found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end">
                    {{ $faqs->links() }}
                </div>

            </div>
        </div>

    </main>
</x-admin-layout>