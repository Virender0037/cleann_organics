<x-admin-layout title="Tax Rates">
 <main class="pc-container-edit">    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Tax Rates</h4>
            <p class="text-muted mb-0">Manage GST / tax rates for products</p>
        </div>

        <div>
            <a href="{{ route('admin.catalog.tax-rates.create') }}" class="btn btn-primary">
                <i class="ph ph-plus me-1"></i>
                Add Tax Rate
            </a>
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <span class="mx-2">›</span>
        <span>Catalog</span>
        <span class="mx-2">›</span>
        <span>Tax Rates</span>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Tax Rate List</h5>
        </div>

        <div class="card-body">

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form method="GET" action="{{ route('admin.catalog.tax-rates.index') }}">
                <div class="row mb-3">
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
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tax Name</th>
                            <th>Rate</th>
                            <th>Status</th>
                            <th>Products</th>
                            <th width="130">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($taxRates as $taxRate)
                            <tr>
                                <td>{{ $taxRate->id }}</td>

                                <td>
                                    <strong>{{ $taxRate->name }}</strong>
                                </td>

                                <td>{{ number_format((float) $taxRate->percentage, 2) }}%</td>

                                <td>
                                    <span class="badge {{ $taxRate->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ ucfirst($taxRate->status) }}
                                    </span>
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
                                <td colspan="6" class="text-center text-muted">No tax rates found.</td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $taxRates->links() }}
            </div>

        </div>
    </div>
</main>
</x-admin-layout>