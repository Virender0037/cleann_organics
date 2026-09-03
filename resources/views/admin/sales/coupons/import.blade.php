<x-admin-layout title="Import Coupons">

    <main class="pc-container-edit">

        <x-admin.page-header title="Import Coupons" subtitle="Bulk create coupons from a CSV or XLSX file">
            <x-slot:actions>
                <a href="{{ route('admin.sales.coupons.index') }}" class="btn btn-light">
                    <i class="ph ph-arrow-left me-1"></i>
                    Back
                </a>
            </x-slot:actions>
        </x-admin.page-header>

        <x-admin.breadcrumb :items="[
            ['label' => 'Sales'],
            ['label' => 'Coupons', 'url' => route('admin.sales.coupons.index')],
            ['label' => 'Import Coupons'],
        ]" />

        @include('admin.partials.alerts')

        @if (session('import_results'))
            @php $results = session('import_results'); @endphp
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Import Results</h5>
                </div>

                <div class="card-body">
                    <p class="text-success mb-1"><strong>{{ $results['success'] }}</strong> coupons created.</p>
                    <p class="text-warning mb-1"><strong>{{ count($results['skipped']) }}</strong> rows skipped.</p>
                    <p class="text-danger mb-3"><strong>{{ count($results['errors']) }}</strong> rows failed.</p>

                    @if (count($results['skipped']))
                        <h6>Skipped Rows</h6>
                        <ul class="mb-3">
                            @foreach ($results['skipped'] as $row)
                                <li>Row {{ $row['row'] }}{{ $row['code'] ? ' (Coupon: '.$row['code'].')' : '' }}: {{ $row['message'] }}</li>
                            @endforeach
                        </ul>
                    @endif

                    @if (count($results['errors']))
                        <h6>Errors</h6>
                        <ul class="mb-0">
                            @foreach ($results['errors'] as $row)
                                <li>Row {{ $row['row'] }}{{ $row['code'] ? ' (Coupon: '.$row['code'].')' : '' }}: {{ $row['message'] }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        @endif

        <x-admin.form-card title="Upload File" action="{{ route('admin.sales.coupons.import.store') }}" enctype="multipart/form-data">
            @error('file')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror

            <div class="mb-3">
                <a href="{{ route('admin.sales.coupons.import.template') }}" class="btn btn-light-secondary">
                    <i class="ph ph-download-simple me-1"></i>
                    Download Sample Template
                </a>
            </div>

            <div class="mb-3">
                <label class="form-label">Import File <span class="text-danger">*</span></label>
                <input type="file" name="file" class="form-control" accept=".csv,.xlsx" required>
                <small class="text-muted">Accepted formats: CSV, XLSX. Required columns: code, type, value, start_date, end_date, status. Optional: minimum_order_amount, maximum_discount_amount, usage_limit. Dates must be in YYYY-MM-DD format. type must be fixed or percentage (value capped at 100 for percentage). used_count cannot be imported &mdash; every imported coupon starts with 0 uses.</small>
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
