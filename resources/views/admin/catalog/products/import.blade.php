<x-admin-layout title="Import Products">

    <main class="pc-container-edit">

        <x-admin.page-header title="Import Products" subtitle="Bulk create products from a CSV or XLSX file">
            <x-slot:actions>
                <a href="{{ route('admin.catalog.products.index') }}" class="btn btn-light">
                    <i class="ph ph-arrow-left me-1"></i>
                    Back
                </a>
            </x-slot:actions>
        </x-admin.page-header>

        <x-admin.breadcrumb :items="[
            ['label' => 'Catalog'],
            ['label' => 'Products', 'url' => route('admin.catalog.products.index')],
            ['label' => 'Import Products'],
        ]" />

        @include('admin.partials.alerts')

        @if (session('import_results'))
            @php $results = session('import_results'); @endphp
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Import Results</h5>
                </div>

                <div class="card-body">
                    <p class="text-success mb-1"><strong>{{ $results['success'] }}</strong> products created.</p>
                    <p class="text-warning mb-1"><strong>{{ count($results['skipped']) }}</strong> rows skipped.</p>
                    <p class="text-danger mb-3"><strong>{{ count($results['errors']) }}</strong> rows failed.</p>

                    @if (count($results['skipped']))
                        <h6>Skipped Rows</h6>
                        <ul class="mb-3">
                            @foreach ($results['skipped'] as $row)
                                <li>Row {{ $row['row'] }} (slug: {{ $row['slug'] }}): {{ $row['reason'] }}</li>
                            @endforeach
                        </ul>
                    @endif

                    @if (count($results['errors']))
                        <h6>Errors</h6>
                        <ul class="mb-0">
                            @foreach ($results['errors'] as $row)
                                <li>Row {{ $row['row'] }}: {{ $row['message'] }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        @endif

        <x-admin.form-card title="Upload File" action="{{ route('admin.catalog.products.import.store') }}" enctype="multipart/form-data">
            @error('file')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror

            <div class="mb-3">
                <a href="{{ route('admin.catalog.products.import.template') }}" class="btn btn-light-secondary">
                    <i class="ph ph-download-simple me-1"></i>
                    Download Sample Template
                </a>
            </div>

            <div class="mb-3">
                <label class="form-label">Import File <span class="text-danger">*</span></label>
                <input type="file" name="file" class="form-control" accept=".csv,.xlsx" required>
                <small class="text-muted">Accepted formats: CSV, XLSX. Required columns: name, status. category_slug is required per row. Optional: slug, tax_rate_name, brand, short_description, description, is_returnable, return_days, is_featured, is_latest, is_best_seller, sort_order, meta_title, meta_keywords, meta_description, tags (comma-separated existing tag names).</small>
            </div>

            <x-slot:actions>
                <button type="submit" class="btn btn-primary">
                    <i class="ph ph-upload-simple me-1"></i>
                    Import
                </button>
            </x-slot:actions>
        </x-admin.form-card>

    </main>

</x-admin-layout>
