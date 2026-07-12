<x-admin-layout title="Customers Report">
<main class="pc-container-edit">

<div class="d-flex justify-content-between align-items-center mb-4">
<div>
<h4>Customers Report</h4>
<p class="text-muted">Customer registrations and purchase statistics</p>
</div>

<a href="{{ route('admin.reports.customers.export', request()->query()) }}" class="btn btn-light-secondary">
<i class="ph ph-download-simple me-1"></i>Export
</a>
</div>

<div class="mb-3">
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <span class="mx-2">›</span>
    <span>Reports</span>
    <span class="mx-2">›</span>
    <span>Customers Report</span>
</div>

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

<div class="card">
<div class="card-header">
<h5>Customer List</h5>
</div>

<div class="card-body">

<form method="GET" action="{{ route('admin.reports.customers.index') }}">
    <div class="row mb-4">

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

        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Search</button>
        </div>

    </div>
</form>

<div class="table-responsive">
<table class="table table-hover">

<thead>
<tr>
<th>#</th>
<th>Name</th>
<th>Email</th>
<th>Orders</th>
<th>Total Spend</th>
<th>Joined</th>
</tr>
</thead>

<tbody>

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
<td colspan="6" class="text-center text-muted">No customers found.</td>
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
