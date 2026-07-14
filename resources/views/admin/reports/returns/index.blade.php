<x-admin-layout title="Returns Report">
<main class="pc-container-edit">

    <x-admin.page-header title="Returns Report" subtitle="Track returned orders and refund requests">
        <x-slot:actions>
            <a href="{{ route('admin.reports.returns.export', request()->query()) }}" class="btn btn-light-secondary">
                <i class="ph ph-download-simple me-1"></i>Export
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[['label' => 'Reports'], ['label' => 'Returns Report']]" />

    @include('admin.partials.alerts')

    <div class="row mb-4">

        <div class="col-md-3"><div class="card"><div class="card-body">
            <p>Total Returns</p>
            <h4>{{ $stats['total'] }}</h4>
        </div></div></div>

        <div class="col-md-3"><div class="card"><div class="card-body">
            <p>Approved</p>
            <h4 class="text-success">{{ $stats['approved'] }}</h4>
        </div></div></div>

        <div class="col-md-3"><div class="card"><div class="card-body">
            <p>Requested</p>
            <h4 class="text-warning">{{ $stats['pending'] }}</h4>
        </div></div></div>

        <div class="col-md-3"><div class="card"><div class="card-body">
            <p>Rejected</p>
            <h4 class="text-danger">{{ $stats['rejected'] }}</h4>
        </div></div></div>

    </div>

    <x-admin.table-card title="Return Requests">
        <x-slot:toolbar>
            <x-admin.filter-toolbar action="{{ route('admin.reports.returns.index') }}">
                <div class="col-md-4">
                    <input name="search"
                           class="form-control"
                           value="{{ request('search') }}"
                           placeholder="Search Return ID / Order / Customer / Email">
                </div>

                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        @foreach (['requested' => 'Requested', 'approved' => 'Approved', 'rejected' => 'Rejected', 'picked_up' => 'Picked Up', 'received' => 'Received', 'refunded' => 'Refunded'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <x-slot:submit>
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </x-slot:submit>
            </x-admin.filter-toolbar>
        </x-slot:toolbar>

        <x-slot:head>
            <th>Return ID</th>
            <th>Order</th>
            <th>Customer</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Refund</th>
        </x-slot:head>

        @forelse ($returns as $return)
            <tr>
                <td>{{ $return->return_number }}</td>
                <td>#{{ $return->order->order_number ?? '—' }}</td>
                <td>{{ $return->user->name ?? '—' }}</td>
                <td>{{ \Illuminate\Support\Str::limit($return->reason, 40) }}</td>
                <td>
                    <x-admin.status-badge :status="$return->status" :map="['refunded' => 'success']" />
                </td>
                <td>₹{{ number_format((float) $return->refund_amount, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6">
                    <x-admin.empty-state>No return requests found.</x-admin.empty-state>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $returns->links() }}
        </x-slot:pagination>
    </x-admin.table-card>

</main>
</x-admin-layout>
