<x-admin-layout title="Shipping Zones">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Shipping Zones</h4>
                <p class="text-muted mb-0">Manage delivery zones by state, city and pincode</p>
            </div>

            <a href="{{ route('admin.shipping.zones.create') }}" class="btn btn-primary">
                <i class="ph ph-plus me-1"></i>
                Add Zone
            </a>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>Shipping</span>
            <span class="mx-2">›</span>
            <span>Zones</span>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Zone List</h5>
            </div>

            <div class="card-body">

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form method="GET" action="{{ route('admin.shipping.zones.index') }}">
                    <div class="row mb-4">
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
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Zone Name</th>
                                <th>State</th>
                                <th>City</th>
                                <th>Pincode</th>
                                <th>Status</th>
                                <th width="130">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($zones as $zone)
                                <tr>
                                    <td>{{ $zone->id }}</td>
                                    <td><strong>{{ $zone->name }}</strong></td>
                                    <td>{{ $zone->state ?? '—' }}</td>
                                    <td>{{ $zone->city ?? '—' }}</td>
                                    <td>{{ $zone->pincode ?? '—' }}</td>
                                    <td>
                                        <span class="badge {{ $zone->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ucfirst($zone->status) }}
                                        </span>
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
                                    <td colspan="7" class="text-center text-muted">No shipping zones found.</td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                <div class="d-flex justify-content-end">
                    {{ $zones->links() }}
                </div>

            </div>
        </div>

    </main>

</x-admin-layout>