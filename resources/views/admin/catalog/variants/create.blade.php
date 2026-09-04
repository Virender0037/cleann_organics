<x-admin-layout title="Add Variant">
    <main class="pc-container-edit">  
    <x-admin.page-header title="Add Variant" subtitle="Create product variant with price, stock and images">
        <x-slot:actions>
            <a href="{{ route('admin.catalog.variants.index') }}" class="btn btn-light">
                <i class="ph ph-arrow-left me-1"></i>
                Back
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.breadcrumb :items="[
        ['label' => 'Catalog'],
        ['label' => 'Variants', 'url' => route('admin.catalog.variants.index')],
        ['label' => 'Add Variant'],
    ]" />

    @include('admin.partials.alerts')

    <form action="{{ route('admin.catalog.variants.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

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
                                <option value="{{ $product->id }}" @selected((int) old('product_id') === $product->id)>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Variant Name</label>
                        <input type="text" name="variant_name" class="form-control @error('variant_name') is-invalid @enderror" value="{{ old('variant_name') }}" placeholder="500g / 1kg / Red XL">
                        @error('variant_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku') }}" placeholder="HON-500">
                        @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Barcode</label>
                        <input type="text" name="barcode" class="form-control @error('barcode') is-invalid @enderror" value="{{ old('barcode') }}" placeholder="Optional barcode">
                        @error('barcode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Unit</label>
                        <select name="unit" class="form-select @error('unit') is-invalid @enderror">
                            <option value="">Select Unit</option>
                            <option value="kg" @selected(old('unit') === 'kg')>Kg</option>
                            <option value="gram" @selected(old('unit') === 'gram')>Gram</option>
                            <option value="litre" @selected(old('unit') === 'litre')>Litre</option>
                            <option value="piece" @selected(old('unit') === 'piece')>Piece</option>
                            <option value="pack" @selected(old('unit') === 'pack')>Pack</option>
                        </select>
                        @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Size</label>
                        <input type="text" name="size" class="form-control @error('size') is-invalid @enderror" value="{{ old('size') }}" placeholder="500g / 1L / XL">
                        @error('size') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Weight</label>
                        <input type="number" step="0.01" name="weight" class="form-control @error('weight') is-invalid @enderror" value="{{ old('weight') }}" placeholder="0.50">
                        @error('weight') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Color</label>
                        <input type="text" name="color" class="form-control @error('color') is-invalid @enderror" value="{{ old('color') }}" placeholder="Optional">
                        @error('color') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Pack Quantity</label>
                        <input type="text" name="pack_quantity" class="form-control @error('pack_quantity') is-invalid @enderror" value="{{ old('pack_quantity') }}" placeholder="Pack of 2">
                        @error('pack_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', 0) }}">
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
                    <select name="enable_tiered_pricing" class="form-select @error('enable_tiered_pricing') is-invalid @enderror">
                        <option value="0" @selected(old('enable_tiered_pricing', '0') === '0')>Disable Tiered Pricing</option>
                        <option value="1" @selected(old('enable_tiered_pricing') === '1')>Enable Tiered Pricing</option>
                    </select>
                    @error('enable_tiered_pricing') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Single Qty</label>
                        <input type="number" name="single_quantity" class="form-control @error('single_quantity') is-invalid @enderror" value="{{ old('single_quantity') }}" placeholder="1">
                        @error('single_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Single Price</label>
                        <input type="number" step="0.01" name="single_price" class="form-control @error('single_price') is-invalid @enderror" value="{{ old('single_price') }}" placeholder="299">
                        @error('single_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Standard Qty</label>
                        <input type="number" name="standard_quantity" class="form-control @error('standard_quantity') is-invalid @enderror" value="{{ old('standard_quantity') }}" placeholder="10">
                        @error('standard_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Standard Price</label>
                        <input type="number" step="0.01" name="standard_price" class="form-control @error('standard_price') is-invalid @enderror" value="{{ old('standard_price') }}" placeholder="279">
                        @error('standard_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Discount Qty</label>
                        <input type="number" name="discount_quantity" class="form-control @error('discount_quantity') is-invalid @enderror" value="{{ old('discount_quantity') }}" placeholder="30">
                        @error('discount_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Discount Price</label>
                        <input type="number" step="0.01" name="discount_price" class="form-control @error('discount_price') is-invalid @enderror" value="{{ old('discount_price') }}" placeholder="249">
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
                        <input type="number" name="stock_quantity" class="form-control @error('stock_quantity') is-invalid @enderror" value="{{ old('stock_quantity', 0) }}">
                        @error('stock_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Low Stock Quantity</label>
                        <input type="number" name="low_stock_quantity" class="form-control @error('low_stock_quantity') is-invalid @enderror" value="{{ old('low_stock_quantity', 5) }}">
                        @error('low_stock_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Stock Status</label>
                        <select name="stock_status" class="form-select @error('stock_status') is-invalid @enderror">
                            <option value="in_stock" @selected(old('stock_status', 'in_stock') === 'in_stock')>In Stock</option>
                            <option value="out_of_stock" @selected(old('stock_status') === 'out_of_stock')>Out of Stock</option>
                        </select>
                        @error('stock_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Default Variant</label>
                        <select name="is_default" class="form-select @error('is_default') is-invalid @enderror">
                            <option value="0" @selected(old('is_default', '0') === '0')>No</option>
                            <option value="1" @selected(old('is_default') === '1')>Yes</option>
                        </select>
                        @error('is_default') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                            <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                </div>
            </div>
        </div>

        @include('admin.catalog.variants.partials.media-manager', ['variant' => null])

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

    <script src="{{ admin_asset('assets/js/admin/variant-media-manager.js') }}"></script>
</main>
</x-admin-layout>