<x-admin-layout title="Shipping Methods">

<main class="pc-container-edit">

    <x-admin.page-header title="Shipping Methods" subtitle="Manage available delivery methods">
        <x-slot:actions>
            <a href="{{ route('admin.shipping.methods.create') }}" class="btn btn-primary">
                <i class="ph ph-plus me-1"></i>
                Add Method
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[['label' => 'Shipping'], ['label' => 'Methods']]" />

    @include('admin.partials.alerts')

    <x-admin.table-card title="Method List">
        <x-slot:toolbar>
            <x-admin.filter-toolbar action="{{ route('admin.shipping.methods.index') }}">
                <div class="col-md-4">
                    <input type="text"
                           name="search"
                           class="form-control"
                           value="{{ request('search') }}"
                           placeholder="Search method"
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
            <th>Method Name</th>
            <th>Code</th>
            <th>Estimated Delivery</th>
            <th>Sort Order</th>
            <th>Status</th>
            <th width="130">Action</th>
        </x-slot:head>

        @forelse ($methods as $method)
            <tr>
                <td>{{ $method->id }}</td>
                <td><strong>{{ $method->name }}</strong></td>
                <td>{{ $method->code }}</td>
                <td>{{ $method->estimated_delivery ?? '—' }}</td>
                <td>{{ $method->sort_order }}</td>
                <td>
                    <x-admin.status-badge :status="$method->status" />
                </td>
                <td>
                    <a href="{{ route('admin.shipping.methods.edit', $method) }}" class="btn btn-sm btn-warning" title="Edit Method">
                        <i class="ph ph-pencil-simple"></i>
                    </a>

                    <form action="{{ route('admin.shipping.methods.destroy', $method) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this method?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" title="Delete Method">
                            <i class="ph ph-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7">
                    <x-admin.empty-state>No shipping methods found.</x-admin.empty-state>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $methods->links() }}
        </x-slot:pagination>
    </x-admin.table-card>

</main>

</x-admin-layout>
