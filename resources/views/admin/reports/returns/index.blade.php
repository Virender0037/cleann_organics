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
<h4>42</h4>
</div></div></div>

<div class="col-md-3"><div class="card"><div class="card-body">
<p>Approved</p>
<h4 class="text-success">31</h4>
</div></div></div>

<div class="col-md-3"><div class="card"><div class="card-body">
<p>Pending</p>
<h4 class="text-warning">9</h4>
</div></div></div>

<div class="col-md-3"><div class="card"><div class="card-body">
<p>Rejected</p>
<h4 class="text-danger">2</h4>
</div></div></div>

</div>

<div class="card">
<div class="card-header">
<h5>Return Requests</h5>
</div>

<div class="card-body table-responsive">

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

<tr>
<td>RET1001</td>
<td>#ORD1001</td>
<td>Rahul Sharma</td>
<td>Damaged Product</td>
<td><span class="badge bg-success">Approved</span></td>
<td>₹1250</td>
</tr>

<tr>
<td>RET1002</td>
<td>#ORD1002</td>
<td>Priya Patel</td>
<td>Wrong Item</td>
<td><span class="badge bg-warning">Pending</span></td>
<td>₹799</td>
</tr>

</tbody>

</table>

</div>

</div>

</main>
</x-admin-layout>