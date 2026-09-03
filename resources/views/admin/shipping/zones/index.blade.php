<x-admin-layout title="Shipping Zones">

<main class="pc-container-edit">

    <x-admin.page-header title="Shipping Zones" subtitle="Manage delivery zones by state, city and pincode">
        <x-slot:actions>
            <a href="{{ route('admin.shipping.zones.import') }}" class="btn btn-light-primary me-2">
                <i class="ph ph-upload-simple"></i>
                Import
            </a>
            <a href="{{ route('admin.shipping.zones.export', request()->query()) }}" class="btn btn-light-secondary me-2">
                <i class="ph ph-download-simple"></i>
                Export
            </a>
            <a href="{{ route('admin.shipping.zones.create') }}" class="btn btn-primary">
                <i class="ph ph-plus me-1"></i>
                Add Zone
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[['label' => 'Shipping'], ['label' => 'Zones']]" />

    @include('admin.partials.alerts')

    <x-admin.table-card title="Zone List">
        <x-slot:toolbar>
            <x-admin.filter-toolbar action="{{ route('admin.shipping.zones.index') }}">
                <div class="col-md-4">
                    <input type="text"
                           name="search"
                           class="form-control"
                           value="{{ request('search') }}"
                           placeholder="Search zone, state, city or pincode"
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
            <th>Zone Name</th>
            <th>State</th>
            <th>City</th>
            <th>Pincode</th>
            <th>Zone Type</th>
            <th>Status</th>
            <th width="130">Action</th>
        </x-slot:head>

        @forelse ($zones as $zone)
            <tr>
                <td>{{ $zone->id }}</td>
                <td><strong>{{ $zone->name }}</strong></td>
                <td>{{ $zone->state ?? '—' }}</td>
                <td>{{ $zone->city ?? '—' }}</td>
                <td>{{ $zone->pincode ?? '—' }}</td>
                <td>{{ $zone->zone_type ?? '—' }}</td>
                <td>
                    <x-admin.status-badge :status="$zone->status" />
                </td>
                <td>
                    <a href="{{ route('admin.shipping.zones.edit', $zone) }}" class="btn btn-sm btn-warning" title="Edit Zone">
                        <i class="ph ph-pencil-simple"></i>
                    </a>

                    <form action="{{ route('admin.shipping.zones.destroy', $zone) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this zone?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" title="Delete Zone">
                            <i class="ph ph-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8">
                    <x-admin.empty-state>No shipping zones found.</x-admin.empty-state>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $zones->links() }}
        </x-slot:pagination>
    </x-admin.table-card>

</main>

</x-admin-layout>
