<x-admin-layout title="Shipping Rates">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Shipping Rates</h4>
                <p class="text-muted mb-0">Manage shipping charges based on zone and weight range</p>
            </div>

            <a href="{{ route('admin.shipping.rates.create') }}" class="btn btn-primary">
                <i class="ph ph-plus me-1"></i>
                Add Rate
            </a>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>Shipping</span>
            <span class="mx-2">›</span>
            <span>Rates</span>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Rate List</h5>
            </div>

            <div class="card-body">

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form method="GET" action="{{ route('admin.shipping.rates.index') }}">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <input type="text"
                                   name="search"
                                   class="form-control"
                                   value="{{ request('search') }}"
                                   placeholder="Search zone"
                                   onchange="this.form.submit()">
                        </div>

                        <div class="col-md-3">
                            <select name="zone_id" class="form-select" onchange="this.form.submit()">
                                <option value="">All Zones</option>
                                @foreach ($zones as $zoneOption)
                                    <option value="{{ $zoneOption->id }}" @selected((int) request('zone_id') === $zoneOption->id)>
                                        {{ $zoneOption->name }}
                                    </option>
                                @endforeach
                            </select>
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
                                <th>Shipping Zone</th>
                                <th>Weight Range</th>
                                <th>Shipping Charge</th>
                                <th>Free Shipping Above</th>
                                <th>Status</th>
                                <th width="130">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($rates as $rate)
                                <tr>
                                    <td>{{ $rate->id }}</td>
                                    <td><strong>{{ $rate->zone->name ?? '—' }}</strong></td>
                                    <td>{{ number_format((float) $rate->min_weight, 2) }} kg - {{ $rate->max_weight !== null ? number_format((float) $rate->max_weight, 2).' kg' : 'and above' }}</td>
                                    <td>₹{{ number_format((float) $rate->shipping_charge, 2) }}</td>
                                    <td>{{ $rate->free_shipping_above !== null ? '₹'.number_format((float) $rate->free_shipping_above, 2) : '—' }}</td>
                                    <td>
                                        <span class="badge {{ $rate->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ucfirst($rate->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.shipping.rates.edit', $rate) }}" class="btn btn-sm btn-warning" title="Edit Rate">
                                            <i class="ph ph-pencil-simple"></i>
                                        </a>

                                        <form action="{{ route('admin.shipping.rates.destroy', $rate) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this rate?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete Rate">
                                                <i class="ph ph-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No shipping rates found.</td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                <div class="d-flex justify-content-end">
                    {{ $rates->links() }}
                </div>

            </div>
        </div>

    </main>

</x-admin-layout>