<x-admin-layout title="Returns Report">
<main class="pc-container-edit">

<div class="d-flex justify-content-between align-items-center mb-4">
<div>
<h4>Returns Report</h4>
<p class="text-muted">Track returned orders and refund requests</p>
</div>

<button class="btn btn-light-secondary">
<i class="ph ph-download-simple me-1"></i>Export
</button>
</div>

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

<div class="card">
<div class="card-header">
<h5>Return Requests</h5>
</div>

<div class="card-body">

<form method="GET" action="{{ route('admin.reports.returns.index') }}">
    <div class="row mb-4">

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

        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Search</button>
        </div>

    </div>
</form>

<div class="table-responsive">

<table class="table table-hover">

<thead>
<tr>
<th>Return ID</th>
<th>Order</th>
<th>Customer</th>
<th>Reason</th>
<th>Status</th>
<th>Refund</th>
</tr>
</thead>

<tbody>

@forelse ($returns as $return)
<tr>
<td>{{ $return->return_number }}</td>
<td>#{{ $return->order->order_number ?? '—' }}</td>
<td>{{ $return->user->name ?? '—' }}</td>
<td>{{ \Illuminate\Support\Str::limit($return->reason, 40) }}</td>
<td>
    @php
        $statusBadge = match ($return->status) {
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            'picked_up', 'received' => 'bg-info',
            'refunded' => 'bg-success',
            default => 'bg-warning',
        };
    @endphp
    <span class="badge {{ $statusBadge }}">{{ ucwords(str_replace('_', ' ', $return->status)) }}</span>
</td>
<td>₹{{ number_format((float) $return->refund_amount, 2) }}</td>
</tr>
@empty
<tr>
<td colspan="6" class="text-center text-muted">No return requests found.</td>
</tr>
@endforelse

</tbody>

</table>

</div>

<div class="d-flex justify-content-end">
    {{ $returns->links() }}
</div>

</div>

</div>

</main>
</x-admin-layout>