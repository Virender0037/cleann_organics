<x-admin-layout title="Users">
<main class="pc-container-edit">

    <x-admin.page-header title="Users" subtitle="Admin panel users">
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[['label' => 'Administration'], ['label' => 'Users']]" />

    @include('admin.partials.alerts')

    <x-admin.table-card title="User List">
        <x-slot:toolbar>
            <x-admin.filter-toolbar action="{{ route('admin.administration.users.index') }}">
                <div class="col-md-4">
                    <input type="text"
                           name="search"
                           class="form-control"
                           value="{{ request('search') }}"
                           placeholder="Search name or email"
                           onchange="this.form.submit()">
                </div>
            </x-admin.filter-toolbar>
        </x-slot:toolbar>

        <x-slot:head>
            <th width="80">#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
        </x-slot:head>

        @forelse ($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ ucfirst($user->role) }}</td>
                <td><x-admin.status-badge :status="$user->status" /></td>
            </tr>
        @empty
            <tr>
                <td colspan="5">
                    <x-admin.empty-state>No admin users found.</x-admin.empty-state>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $users->links() }}
        </x-slot:pagination>
    </x-admin.table-card>

</main>
</x-admin-layout>
