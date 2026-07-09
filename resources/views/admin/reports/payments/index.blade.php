<x-admin-layout title="Payments Report">
<main class="pc-container-edit">

<div class="d-flex justify-content-between align-items-center mb-4">
<div>
<h4>Payments Report</h4>
<p class="text-muted">Payment collection summary</p>
</div>

<button class="btn btn-light-secondary">
<i class="ph ph-download-simple me-1"></i>Export
</button>
</div>

<div class="row mb-4">

<div class="col-md-3"><div class="card"><div class="card-body">
<p>Total Collection</p>
<h4>₹4,25,000</h4>
</div></div></div>

<div class="col-md-3"><div class="card"><div class="card-body">
<p>Paid</p>
<h4 class="text-success">298</h4>
</div></div></div>

<div class="col-md-3"><div class="card"><div class="card-body">
<p>Pending</p>
<h4 class="text-warning">22</h4>
</div></div></div>

<div class="col-md-3"><div class="card"><div class="card-body">
<p>Refunded</p>
<h4 class="text-danger">8</h4>
</div></div></div>

</div>

<div class="card">
<div class="card-header">
<h5>Payment Transactions</h5>
</div>

<div class="card-body table-responsive">

<table class="table table-hover">

<thead>
<tr>
<th>Order</th>
<th>Customer</th>
<th>Method</th>
<th>Status</th>
<th>Amount</th>
</tr>
</thead>

<tbody>

<tr>
<td>#1001</td>
<td>Rahul Sharma</td>
<td>UPI</td>
<td><span class="badge bg-success">Paid</span></td>
<td>₹1250</td>
</tr>

<tr>
<td>#1002</td>
<td>Priya Patel</td>
<td>COD</td>
<td><span class="badge bg-warning">Pending</span></td>
<td>₹799</td>
</tr>

</tbody>

</table>

</div>

</div>

</main>
</x-admin-layout>