<x-admin-layout title="Customers">

    <main class="pc-container-edit">

        <x-admin.page-header title="Customers" subtitle="Manage registered customers and their accounts">
            <x-slot:actions>
                <a href="{{ route('admin.customers.export', request()->query()) }}" class="btn btn-light-secondary me-2">
                    <i class="ph ph-download-simple me-1"></i>
                    Export
                </a>
            </x-slot:actions>
        </x-admin.page-header>

        <x-admin.breadcrumb :items="[['label' => 'Customers']]" />

        @include('admin.partials.alerts')

        <x-admin.table-card title="Customer List">
            <x-slot:toolbar>
                <x-admin.filter-toolbar action="{{ route('admin.customers.index') }}">
                    <div class="col-md-4">
                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            value="{{ request('search') }}"
                            placeholder="Search customer"
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
                <th>Customer</th>
                <th>Phone</th>
                <th>Total Orders</th>
                <th>Total Spent</th>
                <th>Last Order</th>
                <th>Status</th>
                <th width="140">Action</th>
            </x-slot:head>

            @forelse ($customers as $customer)
                <tr>

                    <td>{{ $customer->id }}</td>

                    <td>

                        <strong>{{ $customer->name }}</strong>

                        <br>

                        <small class="text-muted">
                            {{ $customer->email }}
                        </small>

                    </td>

                    <td>

                        {{ $customer->phone ?? '—' }}

                    </td>

                    <td>

                        {{ $customer->orders_count }}

                    </td>

                    <td>

                        ₹{{ number_format((float) ($customer->total_spent ?? 0), 2) }}

                    </td>

                    <td>

                        {{ $customer->last_order_at ? \Illuminate\Support\Carbon::parse($customer->last_order_at)->format('d M Y') : '—' }}

                    </td>

                    <td>
                        <x-admin.status-badge :status="$customer->status" />
                    </td>

                    <td>

                        <a href="{{ route('admin.customers.show', $customer) }}"
                           class="btn btn-sm btn-info" title="View Customer">
                            <i class="ph ph-eye"></i>
                        </a>

                        <a href="{{ route('admin.customers.edit', $customer) }}"
                        class="btn btn-sm btn-warning" title="Edit Customer">
                            <i class="ph ph-pencil-simple"></i>
                        </a>

                        <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this customer?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Delete Customer">
                                <i class="ph ph-trash"></i>
                            </button>
                        </form>

                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="8">
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
