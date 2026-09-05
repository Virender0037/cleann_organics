{{--
    Minimal reusable flash banner — no toast/notification library exists on
    the storefront (Breeze auth pages just print `session('status')` inline
    per-page), so this is the smallest addition that covers every page via
    the shared header rather than introducing one. Non-JS forms (cart
    add/update/remove/clear) redirect back with session('success')/
    session('error'); cart.js additionally shows the same style of message
    without a reload where it's loaded. Dismissible via Bootstrap's own
    bundled JS (already loaded on every page), no custom script needed.
--}}
@if (session('success'))
    <div class="container" style="margin-top: 16px;">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
@endif

@if (session('error'))
    <div class="container" style="margin-top: 16px;">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
@endif
