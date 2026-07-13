<x-admin-layout title="Team Members">
    <main class="pc-container-edit">

        <x-admin.page-header title="Team Members" subtitle="Manage team profiles displayed on the website">
            <x-slot:actions>
                <a href="{{ route('admin.cms.team-members.create') }}" class="btn btn-primary">
                    <i class="ph ph-plus me-1"></i>
                    Add Team Member
                </a>
            </x-slot:actions>
        </x-admin.page-header>

        <x-admin.breadcrumb :items="[['label' => 'CMS'], ['label' => 'Team Members']]" />

        @include('admin.partials.alerts')

        <x-admin.table-card title="Team Member List">
            <x-slot:toolbar>
                <x-admin.filter-toolbar action="{{ route('admin.cms.team-members.index') }}">
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
                </x-admin.filter-toolbar>
            </x-slot:toolbar>

            <x-slot:head>
                <th>#</th>
                <th>Photo</th>
                <th>Name</th>
                <th>Designation</th>
                <th>Email</th>
                <th>Sort Order</th>
                <th>Status</th>
                <th width="130">Action</th>
            </x-slot:head>

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
                        <x-admin.status-badge :status="$teamMember->status" />
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
                    <td colspan="8">
                        <x-admin.empty-state>No team members found.</x-admin.empty-state>
                    </td>
                </tr>
            @endforelse

            <x-slot:pagination>
                {{ $teamMembers->links() }}
            </x-slot:pagination>
        </x-admin.table-card>

    </main>
</x-admin-layout>
