<x-admin-layout title="Tax Rates">
 <main class="pc-container-edit">
    <x-admin.page-header title="Tax Rates" subtitle="Manage GST / tax rates for products">
        <x-slot:actions>
            <a href="{{ route('admin.catalog.tax-rates.create') }}" class="btn btn-primary">
                <i class="ph ph-plus me-1"></i>
                Add Tax Rate
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[['label' => 'Catalog'], ['label' => 'Tax Rates']]" />

    @include('admin.partials.alerts')

    <x-admin.table-card title="Tax Rate List">
        <x-slot:toolbar>
            <x-admin.filter-toolbar action="{{ route('admin.catalog.tax-rates.index') }}">
                <div class="col-md-4">
                    <input type="text"
                           name="search"
                           class="form-control"
                           value="{{ request('search') }}"
                           placeholder="Search tax rate"
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
            <th>Tax Name</th>
            <th>Rate</th>
            <th>Status</th>
            <th>Products</th>
            <th width="130">Action</th>
        </x-slot:head>

        @forelse ($taxRates as $taxRate)
            <tr>
                <td>{{ $taxRate->id }}</td>

                <td>
                    <strong>{{ $taxRate->name }}</strong>
                </td>

                <td>{{ number_format((float) $taxRate->percentage, 2) }}%</td>

                <td>
                    <x-admin.status-badge :status="$taxRate->status" />
                </td>

                <td>{{ $taxRate->products_count }}</td>

                <td>
                    <a href="{{ route('admin.catalog.tax-rates.edit', $taxRate) }}" class="btn btn-sm btn-info" title="Edit Tax Rate">
                        <i class="ph ph-pencil-simple"></i>
                    </a>

                    <form action="{{ route('admin.catalog.tax-rates.destroy', $taxRate) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this tax rate?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" title="Delete Tax Rate">
                            <i class="ph ph-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6">
                    <x-admin.empty-state>No tax rates found.</x-admin.empty-state>
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $taxRates->links() }}
        </x-slot:pagination>
    </x-admin.table-card>
</main>
</x-admin-layout>
