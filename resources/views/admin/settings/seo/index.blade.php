<x-admin-layout title="SEO Settings">
<main class="pc-container-edit">

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">SEO Settings</h4>
        <p class="text-muted mb-0">Manage website search engine and tracking settings</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5>SEO Information</h5>
    </div>

    <div class="card-body">
        <form action="#" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Meta Title</label>
                    <input type="text" class="form-control" value="Cleann Organics - Organic Products">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Canonical URL</label>
                    <input type="url" class="form-control" placeholder="https://example.com">
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Meta Keywords</label>
                    <input type="text" class="form-control" placeholder="organic products, honey, oil">
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Meta Description</label>
                    <textarea class="form-control" rows="3">Buy premium organic products online.</textarea>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Google Analytics Code</label>
                    <input type="text" class="form-control" placeholder="G-XXXXXXXXXX">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Google Tag Manager</label>
                    <input type="text" class="form-control" placeholder="GTM-XXXXXXX">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Facebook Pixel ID</label>
                    <input type="text" class="form-control" placeholder="Pixel ID">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Open Graph Image</label>
                    <input type="file" class="form-control">
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Robots.txt</label>
                    <textarea class="form-control" rows="4">User-agent: *
Allow: /</textarea>
                </div>
            </div>

            <div class="text-end mt-4">
                <button class="btn btn-primary">
                    <i class="ph ph-floppy-disk me-1"></i>
                    Save SEO Settings
                </button>
            </div>
        </form>
    </div>
</div>

</main>
</x-admin-layout>