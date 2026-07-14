<x-admin-layout title="Shipping Rates">

<main class="pc-container-edit">

    <x-admin.page-header title="Shipping Rates" subtitle="Manage shipping charges based on zone and weight range">
        <x-slot:actions>
            <a href="{{ route('admin.shipping.rates.create') }}" class="btn btn-primary">
                <i class="ph ph-plus me-1"></i>
                Add Rate
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[['label' => 'Shipping'], ['label' => 'Rates']]" />

    @include('admin.partials.alerts')

    <x-admin.table-card title="Rate List">
        <x-slot:toolbar>
            <x-admin.filter-toolbar action="{{ route('admin.shipping.rates.index') }}">
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
            </x-admin.filter-toolbar>
        </x-slot:toolbar>

        <x-slot:head>
            <th>#</th>
            <th>Shipping Zone</th>
            <th>Weight Range</th>
            <th>Shipping Charge</th>
            <th>Free Shipping Above</th>
            <th>Status</th>
            <th width="130">Action</th>
        </x-slot:head>

        @forelse ($rates as $rate)
            <tr>
                <td>{{ $rate->id }}</td>
                <td><strong>{{ $rate->zone->name ?? '—' }}</strong></td>
                <td>{{ number_format((float) $rate->min_weight, 2) }} kg - {{ $rate->max_weight !== null ? number_format((float) $rate->max_weight, 2).' kg' : 'and above' }}</td>
                <td>₹{{ number_format((float) $rate->shipping_charge, 2) }}</td>
                <td>{{ $rate->free_shipping_above !== null ? '₹'.number_format((float) $rate->free_shipping_above, 2) : '—' }}</td>
                <td>
                    <x-admin.status-badge :status="$rate->status" />
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
                <td colspan="7">
                    <x-admin.empty-state>No shipping rates found.</x-admin.empty-state>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $rates->links() }}
        </x-slot:pagination>
    </x-admin.table-card>

</main>

</x-admin-layout>
