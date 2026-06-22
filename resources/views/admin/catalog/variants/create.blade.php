<x-admin-layout title="Add Variant">
    <main class="pc-container-edit">  
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Add Variant</h4>
            <p class="text-muted mb-0">Create product variant with price, stock and images</p>
        </div>

        <a href="{{ route('admin.catalog.variants.index') }}" class="btn btn-light">
            <i class="ph ph-arrow-left me-1"></i>
            Back
        </a>
    </div>

    <div class="mb-3">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <span class="mx-2">›</span>
        <span>Catalog</span>
        <span class="mx-2">›</span>
        <a href="{{ route('admin.catalog.variants.index') }}">Variants</a>
        <span class="mx-2">›</span>
        <span>Add Variant</span>
    </div>

    <form action="#" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card mb-4">
            <div class="card-header">
                <h5>Basic Variant Information</h5>
            </div>

            <div class="card-body">
                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Product <span class="text-danger">*</span></label>
                        <select name="product_id" class="form-select">
                            <option value="">Select Product</option>
                            <option value="1">Organic Honey</option>
                            <option value="2">Cold Pressed Mustard Oil</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Variant Name</label>
                        <input type="text" name="variant_name" class="form-control" placeholder="500g / 1kg / Red XL">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" class="form-control" placeholder="HON-500">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Barcode</label>
                        <input type="text" name="barcode" class="form-control" placeholder="Optional barcode">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Unit</label>
                        <select name="unit" class="form-select">
                            <option value="">Select Unit</option>
                            <option value="kg">Kg</option>
                            <option value="gram">Gram</option>
                            <option value="litre">Litre</option>
                            <option value="piece">Piece</option>
                            <option value="pack">Pack</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Size</label>
                        <input type="text" name="size" class="form-control" placeholder="500g / 1L / XL">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Weight</label>
                        <input type="number" step="0.01" name="weight" class="form-control" placeholder="0.50">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Color</label>
                        <input type="text" name="color" class="form-control" placeholder="Optional">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Pack Quantity</label>
                        <input type="text" name="pack_quantity" class="form-control" placeholder="Pack of 2">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>

                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5>Tier Pricing</h5>
            </div>

            <div class="card-body">
                <div class="mb-3">
                    <select name="enable_tiered_pricing" class="form-select">
                        <option value="0">Disable Tiered Pricing</option>
                        <option value="1">Enable Tiered Pricing</option>
                    </select>
                </div>

                <div class="row">

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Single Qty</label>
                        <input type="number" name="single_quantity" class="form-control" placeholder="1">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Single Price</label>
                        <input type="number" step="0.01" name="single_price" class="form-control" placeholder="299">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Standard Qty</label>
                        <input type="number" name="standard_quantity" class="form-control" placeholder="10">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Standard Price</label>
                        <input type="number" step="0.01" name="standard_price" class="form-control" placeholder="279">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Discount Qty</label>
                        <input type="number" name="discount_quantity" class="form-control" placeholder="30">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Discount Price</label>
                        <input type="number" step="0.01" name="discount_price" class="form-control" placeholder="249">
                    </div>

                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5>Inventory</h5>
            </div>

            <div class="card-body">
                <div class="row">

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Stock Quantity</label>
                        <input type="number" name="stock_quantity" class="form-control" value="0">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Low Stock Quantity</label>
                        <input type="number" name="low_stock_quantity" class="form-control" value="5">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Stock Status</label>
                        <select name="stock_status" class="form-select">
                            <option value="in_stock">In Stock</option>
                            <option value="out_of_stock">Out of Stock</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Default Variant</label>
                        <select name="is_default" class="form-select">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5>Variant Images</h5>
            </div>

            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Upload Images</label>
                    <input type="file" name="images[]" class="form-control" multiple>
                    <small class="text-muted">You can upload multiple images for this variant.</small>
                </div>
            </div>
        </div>

        <div class="text-end mb-4">
            <a href="{{ route('admin.catalog.variants.index') }}" class="btn btn-light">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary">
                <i class="ph ph-floppy-disk me-1"></i>
                Create Variant
            </button>
        </div>

    </form>
</main>
</x-admin-layout>