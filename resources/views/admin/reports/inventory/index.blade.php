<x-admin-layout title="Inventory Report">
<main class="pc-container-edit">

<div class="d-flex justify-content-between align-items-center mb-4">
<div>
<h4>Inventory Report</h4>
<p class="text-muted">Current inventory summary</p>
</div>

<button class="btn btn-light-secondary">
<i class="ph ph-download-simple me-1"></i>Export
</button>
</div>

<div class="row mb-4">

<div class="col-md-3">
<div class="card"><div class="card-body">
<p>Total Products</p>
<h4>158</h4>
</div></div>
</div>

<div class="col-md-3">
<div class="card"><div class="card-body">
<p>In Stock</p>
<h4 class="text-success">142</h4>
</div></div>
</div>

<div class="col-md-3">
<div class="card"><div class="card-body">
<p>Low Stock</p>
<h4 class="text-warning">9</h4>
</div></div>
</div>

<div class="col-md-3">
<div class="card"><div class="card-body">
<p>Out of Stock</p>
<h4 class="text-danger">7</h4>
</div></div>
</div>

</div>

<div class="card">
<div class="card-header">
<h5>Inventory Summary</h5>
</div>

<div class="card-body table-responsive">
<table class="table table-hover">

<thead>
<tr>
<th>Product</th>
<th>SKU</th>
<th>Stock</th>
<th>Reserved</th>
<th>Status</th>
</tr>
</thead>

<tbody>

<tr>
<td>Organic Honey</td>
<td>HON500</td>
<td>120</td>
<td>15</td>
<td><span class="badge bg-success">In Stock</span></td>
</tr>

<tr>
<td>Cold Pressed Oil</td>
<td>OIL1L</td>
<td>4</td>
<td>2</td>
<td><span class="badge bg-warning">Low Stock</span></td>
</tr>

</tbody>

</table>
</div>

</div>

</main>
</x-admin-layout>