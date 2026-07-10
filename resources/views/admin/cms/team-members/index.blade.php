<x-admin-layout title="Team Members">
    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Team Members</h4>
                <p class="text-muted mb-0">Manage team profiles displayed on the website</p>
            </div>

            <a href="{{ route('admin.cms.team-members.create') }}" class="btn btn-primary">
                <i class="ph ph-plus me-1"></i>
                Add Team Member
            </a>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>CMS</span>
            <span class="mx-2">›</span>
            <span>Team Members</span>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Team Member List</h5>
            </div>

            <div class="card-body">

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form method="GET" action="{{ route('admin.cms.team-members.index') }}">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <input type="text"
                                   name="search"
                                   class="form-control"
                                   value="{{ request('search') }}"
                                   placeholder="Search name or designation"
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
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Designation</th>
                                <th>Email</th>
                                <th>Sort Order</th>
                                <th>Status</th>
                                <th width="130">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($teamMembers as $teamMember)
                                <tr>
                                    <td>{{ $teamMember->id }}</td>

                                    <td>
                                        <img src="{{ $teamMember->image ? \Illuminate\Support\Facades\Storage::url($teamMember->image) : 'https://placehold.co/60x60' }}"
                                             class="rounded-circle border"
                                             width="60"
                                             height="60"
                                             alt="Team Member">
                                    </td>

                                    <td>
                                        <strong>{{ $teamMember->name }}</strong>
                                    </td>

                                    <td>{{ $teamMember->designation }}</td>

                                    <td>{{ $teamMember->email ?? '—' }}</td>

                                    <td>{{ $teamMember->sort_order }}</td>

                                    <td>
                                        <span class="badge {{ $teamMember->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ucfirst($teamMember->status) }}
                                        </span>
                                    </td>

                                    <td>
                                        <a href="{{ route('admin.cms.team-members.edit', $teamMember) }}"
                                           class="btn btn-sm btn-warning"
                                           title="Edit Team Member">
                                            <i class="ph ph-pencil-simple"></i>
                                        </a>

                                        <form action="{{ route('admin.cms.team-members.destroy', $teamMember) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this team member?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete Team Member">
                                                <i class="ph ph-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No team members found.</td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                <div class="d-flex justify-content-end">
                    {{ $teamMembers->links() }}
                </div>

            </div>
        </div>

    </main>
</x-admin-layout>