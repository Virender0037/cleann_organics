<x-admin-layout title="General Settings">

<main class="pc-container-edit">

    <x-admin.page-header title="General Settings" subtitle="Manage basic website and company information" />

    <x-admin.breadcrumb :items="[['label' => 'Settings'], ['label' => 'General']]" />

    @include('admin.partials.alerts')

    <x-admin.form-card title="General Information" action="{{ route('admin.settings.general.update') }}" method="PUT" enctype="multipart/form-data">
        <x-slot:actions>
            <button type="submit" class="btn btn-primary">
                <i class="ph ph-floppy-disk me-1"></i>
                Save Settings
            </button>
        </x-slot:actions>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Site Name <span class="text-danger">*</span></label>
                <input type="text" name="site_name" class="form-control @error('site_name') is-invalid @enderror" value="{{ old('site_name', $settings['site_name'] ?? '') }}">
                @error('site_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Company Name</label>
                <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name', $settings['company_name'] ?? '') }}">
                @error('company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Company Email</label>
                <input type="email" name="company_email" class="form-control @error('company_email') is-invalid @enderror" value="{{ old('company_email', $settings['company_email'] ?? '') }}">
                @error('company_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Company Phone</label>
                <input type="text" name="company_phone" class="form-control @error('company_phone') is-invalid @enderror" value="{{ old('company_phone', $settings['company_phone'] ?? '') }}">
                @error('company_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">GST Number</label>
                <input type="text" name="gst_number" class="form-control @error('gst_number') is-invalid @enderror" value="{{ old('gst_number', $settings['gst_number'] ?? '') }}" placeholder="Enter GST number">
                @error('gst_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-12 mb-3">
                <label class="form-label">Company Address</label>
                <textarea name="company_address" class="form-control @error('company_address') is-invalid @enderror" rows="3">{{ old('company_address', $settings['company_address'] ?? '') }}</textarea>
                @error('company_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Timezone</label>
                <select name="timezone" class="form-select @error('timezone') is-invalid @enderror">
                    <option value="Asia/Kolkata" @selected(old('timezone', $settings['timezone'] ?? 'Asia/Kolkata') === 'Asia/Kolkata')>Asia/Kolkata</option>
                    <option value="UTC" @selected(old('timezone', $settings['timezone'] ?? '') === 'UTC')>UTC</option>
                </select>
                @error('timezone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Currency</label>
                <select name="currency" class="form-select @error('currency') is-invalid @enderror">
                    <option value="INR" @selected(old('currency', $settings['currency'] ?? 'INR') === 'INR')>INR ₹</option>
                    <option value="USD" @selected(old('currency', $settings['currency'] ?? '') === 'USD')>USD $</option>
                </select>
                @error('currency') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Language</label>
                <select name="language" class="form-select @error('language') is-invalid @enderror">
                    <option value="en" @selected(old('language', $settings['language'] ?? 'en') === 'en')>English</option>
                    <option value="hi" @selected(old('language', $settings['language'] ?? '') === 'hi')>Hindi</option>
                </select>
                @error('language') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Logo</label>
                <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*">
                @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                @if (! empty($settings['logo']))
                    <div class="mt-2">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($settings['logo']) }}" class="rounded border" width="80" height="80" alt="Logo">
                    </div>
                @endif
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Favicon</label>
                <input type="file" name="favicon" class="form-control @error('favicon') is-invalid @enderror" accept="image/*">
                @error('favicon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                @if (! empty($settings['favicon']))
                    <div class="mt-2">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($settings['favicon']) }}" class="rounded border" width="48" height="48" alt="Favicon">
                    </div>
                @endif
            </div>
        </div>
    </x-admin.form-card>

</main>

</x-admin-layout>
