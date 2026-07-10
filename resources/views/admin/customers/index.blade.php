<x-admin-layout title="Customers">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>
                <h4 class="mb-1">Customers</h4>
                <p class="text-muted mb-0">
                    Manage registered customers and their accounts
                </p>
            </div>

            <div>

                <button class="btn btn-light-secondary me-2">
                    <i class="ph ph-download-simple me-1"></i>
                    Export
                </button>

            </div>

        </div>

        <div class="mb-3">

            <a href="{{ route('admin.dashboard') }}">Dashboard</a>

            <span class="mx-2">›</span>

            <span>Customers</span>

        </div>

        <div class="card">

            <div class="card-header">

                <h5>Customer List</h5>

            </div>

            <div class="card-body">

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form method="GET" action="{{ route('admin.customers.index') }}">
                    <div class="row mb-4">

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

                    </div>
                </form>

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>

                        <tr>

                            <th>#</th>

                            <th>Customer</th>

                            <th>Phone</th>

                            <th>Total Orders</th>

                            <th>Total Spent</th>

                            <th>Last Order</th>

                            <th>Status</th>

                            <th width="140">Action</th>

                        </tr>

                        </thead>

                        <tbody>

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

                                    <span class="badge {{ $customer->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ ucfirst($customer->status) }}
                                    </span>

                                </td>

                                <td>

                                    <a href="{{ route('admin.customers.show', $customer) }}"
                                       class="btn btn-sm btn-info">
                                        <i class="ph ph-eye"></i>
                                    </a>

                                    <a href="{{ route('admin.customers.edit', $customer) }}"
                                    class="btn btn-sm btn-warning">
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
                                <td colspan="8" class="text-center text-muted">No customers found.</td>
                            </tr>
                        @endforelse

                        </tbody>

                    </table>

                </div>

                <div class="d-flex justify-content-end">
                    {{ $customers->links() }}
                </div>

            </div>

        </div>

    </main>

</x-admin-layout>