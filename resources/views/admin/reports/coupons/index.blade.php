<x-admin-layout title="Coupons Report">
<main class="pc-container-edit">

<div class="d-flex justify-content-between align-items-center mb-4">
<div>
<h4>Coupons Report</h4>
<p class="text-muted">Coupon usage and savings report</p>
</div>

<button class="btn btn-light-secondary">
<i class="ph ph-download-simple me-1"></i>Export
</button>
</div>

<div class="row mb-4">

<div class="col-md-3"><div class="card"><div class="card-body">
<p>Total Coupons</p>
<h4>18</h4>
</div></div></div>

<div class="col-md-3"><div class="card"><div class="card-body">
<p>Used</p>
<h4>235</h4>
</div></div></div>

<div class="col-md-3"><div class="card"><div class="card-body">
<p>Discount Given</p>
<h4>₹18,500</h4>
</div></div></div>

<div class="col-md-3"><div class="card"><div class="card-body">
<p>Expired</p>
<h4>5</h4>
</div></div></div>

</div>

<div class="card">
<div class="card-header">
<h5>Coupon Usage</h5>
</div>

<div class="card-body table-responsive">

<table class="table table-hover">

<thead>
<tr>
<th>Coupon</th>
<th>Type</th>
<th>Used</th>
<th>Discount</th>
<th>Status</th>
</tr>
</thead>

<tbody>

<tr>
<td>WELCOME10</td>
<td>10%</td>
<td>120</td>
<td>₹6,400</td>
<td><span class="badge bg-success">Active</span></td>
</tr>

<tr>
<td>SUMMER20</td>
<td>20%</td>
<td>65</td>
<td>₹8,900</td>
<td><span class="badge bg-warning">Expired</span></td>
</tr>

</tbody>

</table>

</div>

</div>

</main>
</x-admin-layout>