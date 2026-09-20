@php $r = $reel ?? null; @endphp

<div class="card mb-4">
    <div class="card-header"><h5>Reel</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $r?->title) }}" maxlength="255">
                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Sort order</label>
                <input type="number" min="0" name="sort_order" class="form-control" value="{{ old('sort_order', $r?->sort_order ?? 0) }}">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Status *</label>
                <select name="status" class="form-select">
                    <option value="active" @selected(old('status', $r?->status ?? 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $r?->status) === 'inactive')>Inactive</option>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Thumbnail (poster image)</label>
                <input type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp" class="form-control @error('thumbnail') is-invalid @enderror">
                @error('thumbnail') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div class="form-text">Vertical (9:16) works best. Shown until the video is played.</div>
                @if ($r?->thumbnail) <img src="{{ Storage::url($r->thumbnail) }}" alt="" class="mt-2 rounded border" style="height:80px"> @endif
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Video file (MP4/WebM)</label>
                <input type="file" name="video" accept="video/mp4,video/webm" class="form-control @error('video') is-invalid @enderror">
                @error('video') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div class="form-text">Max 30 MB — compress before uploading (H.264, ~720p, under 20 s works well). {{ $r?->video_path ? 'A file is already uploaded; choosing a new one replaces it.' : '' }}</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">…or direct video URL</label>
                <input type="url" name="video_url" class="form-control @error('video_url') is-invalid @enderror" value="{{ old('video_url', $r?->video_url) }}" placeholder="https://…/video.mp4">
                @error('video_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Instagram post/reel URL</label>
                <input type="url" name="instagram_url" class="form-control @error('instagram_url') is-invalid @enderror" value="{{ old('instagram_url', $r?->instagram_url) }}" placeholder="https://www.instagram.com/reel/…">
                @error('instagram_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div class="form-text">Opens on Instagram from the card. Instagram videos cannot be embedded without their API.</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5>Linked product *</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Product</label>
                <select name="product_id" id="reel_product" class="form-select @error('product_id') is-invalid @enderror" required>
                    <option value="">— choose —</option>
                    @foreach ($products as $p)
                        <option value="{{ $p->id }}" @selected((int) old('product_id', $r?->product_id) === $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
                @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Variant (optional)</label>
                <select name="product_variant_id" id="reel_variant" class="form-select @error('product_variant_id') is-invalid @enderror">
                    <option value="">Default variant</option>
                    @foreach ($products as $p)
                        @foreach ($p->variants as $v)
                            <option value="{{ $v->id }}" data-product="{{ $p->id }}" @selected((int) old('product_variant_id', $r?->product_variant_id) === $v->id)>{{ $v->displayLabel() }} ({{ $v->sku }})</option>
                        @endforeach
                    @endforeach
                </select>
                @error('product_variant_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div class="form-text">"Add to cart" on the card adds this variant. Product name and price are read live from the catalogue.</div>
            </div>
        </div>
    </div>
</div>

@error('video') <div class="alert alert-danger">{{ $message }}</div> @enderror

<script>
    (function () {
        var product = document.getElementById('reel_product');
        var variant = document.getElementById('reel_variant');
        function sync() {
            Array.prototype.forEach.call(variant.options, function (o) {
                if (! o.dataset.product) { return; }
                var show = o.dataset.product === product.value;
                o.hidden = ! show;
                if (! show && o.selected) { variant.value = ''; }
            });
        }
        product.addEventListener('change', sync);
        sync();
    })();
</script>
