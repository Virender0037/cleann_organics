<x-admin-layout title="Edit Variant">
<main class="pc-container-edit"> 
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Edit Variant</h4>
            <p class="text-muted mb-0">Update product variant details</p>
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
        <span>Edit Variant</span>
    </div>

    <form action="#" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

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
                            <option value="1" selected>Organic Honey</option>
                            <option value="2">Cold Pressed Mustard Oil</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Variant Name</label>
                        <input type="text" name="variant_name" class="form-control" value="500g">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" class="form-control" value="HON-500">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Barcode</label>
                        <input type="text" name="barcode" class="form-control" value="8901234567890">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Unit</label>
                        <select name="unit" class="form-select">
                            <option value="">Select Unit</option>
                            <option value="kg">Kg</option>
                            <option value="gram" selected>Gram</option>
                            <option value="litre">Litre</option>
                            <option value="piece">Piece</option>
                            <option value="pack">Pack</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Size</label>
                        <input type="text" name="size" class="form-control" value="500g">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Weight</label>
                        <input type="number" step="0.01" name="weight" class="form-control" value="0.50">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Color</label>
                        <input type="text" name="color" class="form-control" value="">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Pack Quantity</label>
                        <input type="text" name="pack_quantity" class="form-control" value="Pack of 1">
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
                    <label class="form-label">Tiered Pricing</label>
                    <select name="enable_tiered_pricing" class="form-select">
                        <option value="0">Disable Tiered Pricing</option>
                        <option value="1" selected>Enable Tiered Pricing</option>
                    </select>
                </div>

                <div class="row">

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Single Qty</label>
                        <input type="number" name="single_quantity" class="form-control" value="1">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Single Price</label>
                        <input type="number" step="0.01" name="single_price" class="form-control" value="299">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Standard Qty</label>
                        <input type="number" name="standard_quantity" class="form-control" value="10">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Standard Price</label>
                        <input type="number" step="0.01" name="standard_price" class="form-control" value="279">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Discount Qty</label>
                        <input type="number" name="discount_quantity" class="form-control" value="30">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Discount Price</label>
                        <input type="number" step="0.01" name="discount_price" class="form-control" value="249">
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
                        <input type="number" name="stock_quantity" class="form-control" value="120">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Low Stock Quantity</label>
                        <input type="number" name="low_stock_quantity" class="form-control" value="10">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Stock Status</label>
                        <select name="stock_status" class="form-select">
                            <option value="in_stock" selected>In Stock</option>
                            <option value="out_of_stock">Out of Stock</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Default Variant</label>
                        <select name="is_default" class="form-select">
                            <option value="0">No</option>
                            <option value="1" selected>Yes</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" selected>Active</option>
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
                    <label class="form-label">Upload New Images</label>
                    <input type="file" name="images[]" class="form-control" multiple>
                    <small class="text-muted">Uploading new images will add them to this variant.</small>
                </div>

                <label class="form-label">Existing Images</label>

                <div class="row mt-2">
                    <div class="col-md-2 mb-3">
                        <div class="border rounded p-2 text-center">
                            <img src="https://placehold.co/120x120"
                                 class="img-fluid rounded mb-2"
                                 alt="Variant Image">

                            <div class="form-check d-flex justify-content-center">
                                <input class="form-check-input me-1" type="radio" name="primary_image" checked>
                                <label class="form-check-label">Primary</label>
                            </div>

                            <button type="button" class="btn btn-sm btn-danger mt-2">
                                <i class="ph ph-trash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-md-2 mb-3">
                        <div class="border rounded p-2 text-center">
                            <img src="https://placehold.co/120x120"
                                 class="img-fluid rounded mb-2"
                                 alt="Variant Image">

                            <div class="form-check d-flex justify-content-center">
                                <input class="form-check-input me-1" type="radio" name="primary_image">
                                <label class="form-check-label">Primary</label>
                            </div>

                            <button type="button" class="btn btn-sm btn-danger mt-2">
                                <i class="ph ph-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="text-end mb-4">
            <a href="{{ route('admin.catalog.variants.index') }}" class="btn btn-light">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary">
                <i class="ph ph-floppy-disk me-1"></i>
                Update Variant
            </button>
        </div>

    </form>
</main>
</x-admin-layout>