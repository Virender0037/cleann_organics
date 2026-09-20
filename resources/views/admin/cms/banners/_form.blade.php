@php
    use App\Models\HomeBanner;
    use Illuminate\Support\Facades\Storage;

    $b = $banner ?? null;
    $isHero = $section === HomeBanner::SECTION_HERO;
    $currentImage = ($b?->image && Storage::disk('public')->exists($b->image)) ? Storage::url($b->image) : null;
    $currentMobile = ($b?->mobile_image && Storage::disk('public')->exists($b->mobile_image)) ? Storage::url($b->mobile_image) : null;
    $type = old('link_type', $b?->link_type ?? ($isHero ? 'category' : 'none'));
    $selectedIcon = old('icon', $b?->icon);
@endphp

<input type="hidden" name="section" value="{{ $section }}">

<div class="card mb-4">
    <div class="card-header"><h5>{{ $isHero ? 'Slide' : 'Benefit card' }}</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Title {{ $isHero ? '' : '*' }}</label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $b?->title) }}" maxlength="255" {{ $isHero ? '' : 'required' }}>
                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">{{ $isHero ? 'Subtitle' : 'Description' }}</label>
                <input type="text" name="subtitle" class="form-control @error('subtitle') is-invalid @enderror" value="{{ old('subtitle', $b?->subtitle) }}" maxlength="255">
                @error('subtitle') <div class="invalid-feedback">{{ $message }}</div> @enderror
                @unless ($isHero)
                    <div class="form-text">Use <code>{free_shipping_threshold}</code> to insert the live free-shipping amount (Settings → Storefront &amp; Offers), e.g. “Free Shipping on Orders Above ₹{free_shipping_threshold}”.</div>
                @endunless
            </div>
            @if ($isHero)
                <div class="col-md-6 mb-3">
                    <label class="form-label">Button text</label>
                    <input type="text" name="button_text" class="form-control @error('button_text') is-invalid @enderror" value="{{ old('button_text', $b?->button_text) }}" maxlength="60" placeholder="Shop Now">
                    @error('button_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">Optional. The whole slide is clickable either way.</div>
                </div>
            @endif
            <div class="col-md-3 mb-3">
                <label class="form-label">Sort order</label>
                <input type="number" min="0" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $b?->sort_order ?? 0) }}">
                @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div class="form-text">Lowest number shows first.</div>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Status *</label>
                <select name="status" class="form-select">
                    <option value="active" @selected(old('status', $b?->status ?? 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $b?->status) === 'inactive')>Inactive</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5>{{ $isHero ? 'Images' : 'Icon / image' }}</h5></div>
    <div class="card-body">
        <div class="row">
            @unless ($isHero)
                <div class="col-12 mb-3">
                    <label class="form-label">Icon</label>
                    <div class="d-flex flex-wrap gap-3">
                        <label class="d-flex flex-column align-items-center border rounded p-2" style="cursor:pointer;min-width:84px">
                            <input type="radio" name="icon" value="" @checked(! $selectedIcon)>
                            <span class="small mt-1">None</span>
                        </label>
                        @foreach (HomeBanner::ICONS as $key => $iconLabel)
                            <label class="d-flex flex-column align-items-center border rounded p-2" style="cursor:pointer;min-width:84px">
                                <input type="radio" name="icon" value="{{ $key }}" @checked($selectedIcon === $key)>
                                <span class="text-success my-1"><x-frontend.benefit-icon :name="$key" :size="30" /></span>
                                <span class="small text-center">{{ $iconLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('icon') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    <div class="form-text">An uploaded image below replaces the icon on the storefront.</div>
                </div>
            @endunless

            <div class="col-md-6 mb-3">
                <label class="form-label">{{ $isHero ? 'Desktop image '.($b ? '' : '*') : 'Custom image (optional)' }}</label>
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="form-control @error('image') is-invalid @enderror" {{ ($isHero && ! $currentImage) ? 'required' : '' }}>
                @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div class="form-text">
                    {{ $isHero ? 'Wide image, about 1920×720 px.' : 'Small square image, about 96×96 px.' }} JPG/PNG/WebP, max 4 MB. Uploading a new file replaces the current one.
                </div>
                @if ($currentImage)
                    <img src="{{ $currentImage }}" alt="{{ $b->alt_text }}" class="mt-2 rounded border d-block" style="max-height:90px;max-width:100%">
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image">
                        <label class="form-check-label" for="remove_image">Remove current image</label>
                    </div>
                @elseif ($b?->image)
                    <div class="text-warning small mt-2">The stored file is missing on disk — the storefront shows a fallback. Upload a new image.</div>
                @endif
            </div>

            @if ($isHero)
                <div class="col-md-6 mb-3">
                    <label class="form-label">Mobile image</label>
                    <input type="file" name="mobile_image" accept="image/jpeg,image/png,image/webp" class="form-control @error('mobile_image') is-invalid @enderror">
                    @error('mobile_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">Optional taller crop, about 960×1200 px. Falls back to the desktop image.</div>
                    @if ($currentMobile)
                        <img src="{{ $currentMobile }}" alt="" class="mt-2 rounded border d-block" style="max-height:90px;max-width:100%">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="remove_mobile_image" value="1" id="remove_mobile_image">
                            <label class="form-check-label" for="remove_mobile_image">Remove current mobile image</label>
                        </div>
                    @endif
                </div>
            @endif

            <div class="col-md-12 mb-3">
                <label class="form-label">Image alt text</label>
                <input type="text" name="alt_text" class="form-control @error('alt_text') is-invalid @enderror" value="{{ old('alt_text', $b?->alt_text) }}" maxlength="255" placeholder="Describe the image for screen readers and SEO">
                @error('alt_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5>Click destination</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Links to *</label>
                <select name="link_type" id="link_type" class="form-select @error('link_type') is-invalid @enderror">
                    @unless ($isHero)
                        <option value="none" @selected($type === 'none')>Nothing (not clickable)</option>
                    @endunless
                    <option value="product" @selected($type === 'product')>A product</option>
                    <option value="category" @selected($type === 'category')>A category</option>
                    <option value="tag" @selected($type === 'tag')>A collection (tag)</option>
                    <option value="url" @selected($type === 'url')>Custom URL / page</option>
                </select>
                @error('link_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-8 mb-3 link-target" data-type="product">
                <label class="form-label">Product</label>
                <select name="product_id" class="form-select @error('product_id') is-invalid @enderror">
                    <option value="">— choose —</option>
                    @foreach ($products as $p) <option value="{{ $p->id }}" @selected((int) old('product_id', $b?->product_id) === $p->id)>{{ $p->name }}</option> @endforeach
                </select>
                @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-8 mb-3 link-target" data-type="category">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                    <option value="">— choose —</option>
                    @foreach ($categories as $c) <option value="{{ $c->id }}" @selected((int) old('category_id', $b?->category_id) === $c->id)>{{ $c->name }}</option> @endforeach
                </select>
                @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-8 mb-3 link-target" data-type="tag">
                <label class="form-label">Collection (tag)</label>
                <select name="tag_id" class="form-select @error('tag_id') is-invalid @enderror">
                    <option value="">— choose —</option>
                    @foreach ($tags as $t) <option value="{{ $t->id }}" @selected((int) old('tag_id', $b?->tag_id) === $t->id)>{{ $t->name }}</option> @endforeach
                </select>
                @error('tag_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-8 mb-3 link-target" data-type="url">
                <label class="form-label">URL or path</label>
                <input type="text" name="link_url" class="form-control @error('link_url') is-invalid @enderror" value="{{ old('link_url', $b?->link_url) }}" placeholder="/shop?max_price=99">
                @error('link_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 link-target-any">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="opens_new_tab" value="1" id="opens_new_tab" @checked(old('opens_new_tab', $b?->opens_new_tab))>
                    <label class="form-check-label" for="opens_new_tab">Open in a new tab</label>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var select = document.getElementById('link_type');
        function sync() {
            document.querySelectorAll('.link-target').forEach(function (el) {
                el.style.display = el.dataset.type === select.value ? '' : 'none';
            });
            var any = document.querySelector('.link-target-any');
            if (any) { any.style.display = select.value === 'none' ? 'none' : ''; }
        }
        select.addEventListener('change', sync);
        sync();
    })();
</script>
