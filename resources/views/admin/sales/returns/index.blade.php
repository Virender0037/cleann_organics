<x-admin-layout title="Returns">

    <main class="pc-container-edit">

        <x-admin.page-header title="Returns" subtitle="Manage customer return requests and refunds">
            <x-slot:actions>
                <a href="{{ route('admin.sales.returns.export', request()->query()) }}" class="btn btn-light-secondary">
                    <i class="ph ph-download-simple me-1"></i>
                    Export
                </a>
            </x-slot:actions>
        </x-admin.page-header>

        <x-admin.breadcrumb :items="[['label' => 'Sales'], ['label' => 'Returns']]" />

        @include('admin.partials.alerts')

        <x-admin.table-card title="Return Requests">
            <x-slot:toolbar>
                <x-admin.filter-toolbar action="{{ route('admin.sales.returns.index') }}">
                    <div class="col-md-4">
                        <input name="search"
                               class="form-control"
                               value="{{ request('search') }}"
                               placeholder="Search Order / Return ID / Customer">
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
                <th>#</th>
                <th>Return ID</th>
                <th>Order</th>
                <th>Customer</th>
                <th>Reason</th>
                <th>Refund</th>
                <th>Status</th>
                <th>Date</th>
                <th width="130">Action</th>
            </x-slot:head>

            @forelse ($returns as $return)
                <tr>

                    <td>{{ $return->id }}</td>

                    <td>#{{ $return->return_number }}</td>

                    <td>#{{ $return->order->order_number ?? '—' }}</td>

                    <td>{{ $return->user->name ?? '—' }}</td>

                    <td>{{ \Illuminate\Support\Str::limit($return->reason, 40) }}</td>

                    <td>₹{{ number_format((float) $return->refund_amount, 2) }}</td>

                    <td>
                        <x-admin.status-badge :status="$return->status" />
                    </td>

                    <td>{{ $return->created_at->format('d M Y') }}</td>

                    <td>

                        <a href="{{ route('admin.sales.returns.show', $return) }}"
                           class="btn btn-sm btn-info">
                            <i class="ph ph-eye"></i>
                        </a>

                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="9">
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
