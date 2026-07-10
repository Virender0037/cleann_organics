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

    <form action="{{ route('admin.catalog.variants.update', $variant) }}" method="POST" enctype="multipart/form-data">
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
                        <select name="product_id" class="form-select @error('product_id') is-invalid @enderror">
                            <option value="">Select Product</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected((int) old('product_id', $variant->product_id) === $product->id)>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Variant Name</label>
                        <input type="text" name="variant_name" class="form-control @error('variant_name') is-invalid @enderror" value="{{ old('variant_name', $variant->variant_name) }}">
                        @error('variant_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku', $variant->sku) }}">
                        @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Barcode</label>
                        <input type="text" name="barcode" class="form-control @error('barcode') is-invalid @enderror" value="{{ old('barcode', $variant->barcode) }}">
                        @error('barcode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Unit</label>
                        <select name="unit" class="form-select @error('unit') is-invalid @enderror">
                            <option value="">Select Unit</option>
                            <option value="kg" @selected(old('unit', $variant->unit) === 'kg')>Kg</option>
                            <option value="gram" @selected(old('unit', $variant->unit) === 'gram')>Gram</option>
                            <option value="litre" @selected(old('unit', $variant->unit) === 'litre')>Litre</option>
                            <option value="piece" @selected(old('unit', $variant->unit) === 'piece')>Piece</option>
                            <option value="pack" @selected(old('unit', $variant->unit) === 'pack')>Pack</option>
                        </select>
                        @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Size</label>
                        <input type="text" name="size" class="form-control @error('size') is-invalid @enderror" value="{{ old('size', $variant->size) }}">
                        @error('size') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Weight</label>
                        <input type="number" step="0.01" name="weight" class="form-control @error('weight') is-invalid @enderror" value="{{ old('weight', $variant->weight) }}">
                        @error('weight') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Color</label>
                        <input type="text" name="color" class="form-control @error('color') is-invalid @enderror" value="{{ old('color', $variant->color) }}">
                        @error('color') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Pack Quantity</label>
                        <input type="text" name="pack_quantity" class="form-control @error('pack_quantity') is-invalid @enderror" value="{{ old('pack_quantity', $variant->pack_quantity) }}">
                        @error('pack_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $variant->sort_order) }}">
                        @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                    <select name="enable_tiered_pricing" class="form-select @error('enable_tiered_pricing') is-invalid @enderror">
                        <option value="0" @selected((string) old('enable_tiered_pricing', (int) $variant->enable_tiered_pricing) === '0')>Disable Tiered Pricing</option>
                        <option value="1" @selected((string) old('enable_tiered_pricing', (int) $variant->enable_tiered_pricing) === '1')>Enable Tiered Pricing</option>
                    </select>
                    @error('enable_tiered_pricing') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Single Qty</label>
                        <input type="number" name="single_quantity" class="form-control @error('single_quantity') is-invalid @enderror" value="{{ old('single_quantity', $variant->single_quantity) }}">
                        @error('single_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Single Price</label>
                        <input type="number" step="0.01" name="single_price" class="form-control @error('single_price') is-invalid @enderror" value="{{ old('single_price', $variant->single_price) }}">
                        @error('single_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Standard Qty</label>
                        <input type="number" name="standard_quantity" class="form-control @error('standard_quantity') is-invalid @enderror" value="{{ old('standard_quantity', $variant->standard_quantity) }}">
                        @error('standard_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Standard Price</label>
                        <input type="number" step="0.01" name="standard_price" class="form-control @error('standard_price') is-invalid @enderror" value="{{ old('standard_price', $variant->standard_price) }}">
                        @error('standard_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Discount Qty</label>
                        <input type="number" name="discount_quantity" class="form-control @error('discount_quantity') is-invalid @enderror" value="{{ old('discount_quantity', $variant->discount_quantity) }}">
                        @error('discount_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Discount Price</label>
                        <input type="number" step="0.01" name="discount_price" class="form-control @error('discount_price') is-invalid @enderror" value="{{ old('discount_price', $variant->discount_price) }}">
                        @error('discount_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                        <input type="number" name="stock_quantity" class="form-control @error('stock_quantity') is-invalid @enderror" value="{{ old('stock_quantity', $variant->stock_quantity) }}">
                        @error('stock_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Low Stock Quantity</label>
                        <input type="number" name="low_stock_quantity" class="form-control @error('low_stock_quantity') is-invalid @enderror" value="{{ old('low_stock_quantity', $variant->low_stock_quantity) }}">
                        @error('low_stock_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Stock Status</label>
                        <select name="stock_status" class="form-select @error('stock_status') is-invalid @enderror">
                            <option value="in_stock" @selected(old('stock_status', $variant->stock_status) === 'in_stock')>In Stock</option>
                            <option value="out_of_stock" @selected(old('stock_status', $variant->stock_status) === 'out_of_stock')>Out of Stock</option>
                        </select>
                        @error('stock_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Default Variant</label>
                        <select name="is_default" class="form-select @error('is_default') is-invalid @enderror">
                            <option value="0" @selected((string) old('is_default', (int) $variant->is_default) === '0')>No</option>
                            <option value="1" @selected((string) old('is_default', (int) $variant->is_default) === '1')>Yes</option>
                        </select>
                        @error('is_default') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="active" @selected(old('status', $variant->status) === 'active')>Active</option>
                            <option value="inactive" @selected(old('status', $variant->status) === 'inactive')>Inactive</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                    <input type="file" name="images[]" class="form-control @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror" multiple>
                    <small class="text-muted">Uploading new images will add them to this variant.</small>
                    @error('images') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @error('images.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                @error('primary_image') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

                <label class="form-label">Existing Images</label>

                <div class="row mt-2">
                    @forelse ($variant->images as $image)
                        <div class="col-md-2 mb-3">
                            <div class="border rounded p-2 text-center">
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($image->image) }}"
                                     class="img-fluid rounded mb-2"
                                     alt="Variant Image">

                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input me-1" type="radio" name="primary_image" value="{{ $image->id }}" @checked($image->is_primary)>
                                    <label class="form-check-label">Primary</label>
                                </div>

                                <button type="submit" form="delete-variant-image-{{ $image->id }}" class="btn btn-sm btn-danger mt-2">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <p class="text-muted">No images uploaded for this variant yet.</p>
                        </div>
                    @endforelse
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

    @foreach ($variant->images as $image)
        <form id="delete-variant-image-{{ $image->id }}" action="{{ route('admin.catalog.variants.images.destroy', [$variant, $image]) }}" method="POST" onsubmit="return confirm('Delete this image?');">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
</main>
</x-admin-layout>