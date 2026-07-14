<x-admin-layout title="Customers Report">
<main class="pc-container-edit">

    <x-admin.page-header title="Customers Report" subtitle="Customer registrations and purchase statistics">
        <x-slot:actions>
            <a href="{{ route('admin.reports.customers.export', request()->query()) }}" class="btn btn-light-secondary">
                <i class="ph ph-download-simple me-1"></i>Export
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[['label' => 'Reports'], ['label' => 'Customers Report']]" />

    @include('admin.partials.alerts')

    <div class="row mb-4">

        <div class="col-md-3">
            <div class="card"><div class="card-body">
                <p class="text-muted">Total Customers</p>
                <h4>{{ $stats['total'] }}</h4>
            </div></div>
        </div>

        <div class="col-md-3">
            <div class="card"><div class="card-body">
                <p class="text-muted">New This Month</p>
                <h4 class="text-success">{{ $stats['new_this_month'] }}</h4>
            </div></div>
        </div>

        <div class="col-md-3">
            <div class="card"><div class="card-body">
                <p class="text-muted">Returning</p>
                <h4>{{ $stats['returning'] }}</h4>
            </div></div>
        </div>

        <div class="col-md-3">
            <div class="card"><div class="card-body">
                <p class="text-muted">Inactive</p>
                <h4 class="text-danger">{{ $stats['inactive'] }}</h4>
            </div></div>
        </div>

    </div>

    <x-admin.table-card title="Customer List">
        <x-slot:toolbar>
            <x-admin.filter-toolbar action="{{ route('admin.reports.customers.index') }}">
                <div class="col-md-4">
                    <input name="search"
                           class="form-control"
                           value="{{ request('search') }}"
                           placeholder="Search name or email">
                </div>

                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                    </select>
                </div>

                <x-slot:submit>
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </x-slot:submit>
            </x-admin.filter-toolbar>
        </x-slot:toolbar>

        <x-slot:head>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Orders</th>
            <th>Total Spend</th>
            <th>Joined</th>
        </x-slot:head>

        @forelse ($customers as $customer)
            <tr>
                <td>{{ $customer->id }}</td>
                <td>{{ $customer->name }}</td>
                <td>{{ $customer->email }}</td>
                <td>{{ $customer->orders_count }}</td>
                <td>₹{{ number_format((float) $customer->total_spent, 2) }}</td>
                <td>{{ $customer->created_at->format('d M Y') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6">
                    <x-admin.empty-state>No customers found.</x-admin.empty-state>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $customers->links() }}
        </x-slot:pagination>
    </x-admin.table-card>

</main>
</x-admin-layout>
