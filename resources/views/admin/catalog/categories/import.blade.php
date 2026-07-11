<x-admin-layout title="Import Categories">

    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Import Categories</h4>
                <p class="text-muted mb-0">Bulk create categories from a CSV file</p>
            </div>

            <a href="{{ route('admin.catalog.categories.index') }}" class="btn btn-light">
                <i class="ph ph-arrow-left me-1"></i>
                Back
            </a>
        </div>

        @if (session('import_results'))
            @php $results = session('import_results'); @endphp
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Import Results</h5>
                </div>

                <div class="card-body">
                    <p class="text-success mb-1"><strong>{{ $results['success'] }}</strong> categories created.</p>
                    <p class="text-warning mb-1"><strong>{{ count($results['skipped']) }}</strong> rows skipped.</p>
                    <p class="text-danger mb-3"><strong>{{ count($results['errors']) }}</strong> rows failed.</p>

                    @if (count($results['skipped']))
                        <h6>Skipped Rows</h6>
                        <ul class="mb-3">
                            @foreach ($results['skipped'] as $row)
                                <li>Row {{ $row['row'] }} (slug: {{ $row['slug'] }}): {{ $row['message'] }}</li>
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

        <div class="card">
            <div class="card-header">
                <h5>Upload CSV</h5>
            </div>

            <div class="card-body">

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @error('file')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror

                <div class="mb-3">
                    <a href="{{ route('admin.catalog.categories.import.template') }}" class="btn btn-light-secondary">
                        <i class="ph ph-download-simple me-1"></i>
                        Download Sample Template
                    </a>
                </div>

                <form action="{{ route('admin.catalog.categories.import.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">CSV File <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".csv" required>
                        <small class="text-muted">Required columns: name, status. Optional: slug, parent_slug, description, sort_order, meta_title, meta_keywords, meta_description.</small>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="ph ph-upload-simple me-1"></i>
                            Import
                        </button>
                    </div>
                </form>

            </div>
        </div>

    </main>

</x-admin-layout>
