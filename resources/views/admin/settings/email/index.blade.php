<x-admin-layout title="Email Settings">

<main class="pc-container-edit">

    <x-admin.page-header title="Email Settings" subtitle="Configure SMTP and system email settings" />

    <x-admin.breadcrumb :items="[['label' => 'Settings'], ['label' => 'Email']]" />

    @include('admin.partials.alerts')

    <x-admin.form-card title="SMTP Configuration" action="{{ route('admin.settings.email.update') }}" method="PUT">
        <x-slot:actions>
            <button type="button" class="btn btn-light-primary">
                <i class="ph ph-paper-plane-tilt me-1"></i>
                Send Test Email
            </button>

            <button type="submit" class="btn btn-primary">
                <i class="ph ph-floppy-disk me-1"></i>
                Save Email Settings
            </button>
        </x-slot:actions>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Mail Driver</label>
                <select name="mail_driver" class="form-select @error('mail_driver') is-invalid @enderror">
                    <option value="smtp" @selected(old('mail_driver', $settings['mail_driver'] ?? 'smtp') === 'smtp')>SMTP</option>
                    <option value="sendmail" @selected(old('mail_driver', $settings['mail_driver'] ?? '') === 'sendmail')>Sendmail</option>
                    <option value="mailgun" @selected(old('mail_driver', $settings['mail_driver'] ?? '') === 'mailgun')>Mailgun</option>
                </select>
                @error('mail_driver') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">SMTP Host</label>
                <input type="text" name="smtp_host" class="form-control @error('smtp_host') is-invalid @enderror" value="{{ old('smtp_host', $settings['smtp_host'] ?? '') }}" placeholder="smtp.mailtrap.io">
                @error('smtp_host') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">SMTP Port</label>
                <input type="text" name="smtp_port" class="form-control @error('smtp_port') is-invalid @enderror" value="{{ old('smtp_port', $settings['smtp_port'] ?? '') }}" placeholder="587">
                @error('smtp_port') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Encryption</label>
                <select name="encryption" class="form-select @error('encryption') is-invalid @enderror">
                    <option value="tls" @selected(old('encryption', $settings['encryption'] ?? 'tls') === 'tls')>TLS</option>
                    <option value="ssl" @selected(old('encryption', $settings['encryption'] ?? '') === 'ssl')>SSL</option>
                    <option value="none" @selected(old('encryption', $settings['encryption'] ?? '') === 'none')>None</option>
                </select>
                @error('encryption') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">From Email</label>
                <input type="email" name="from_email" class="form-control @error('from_email') is-invalid @enderror" value="{{ old('from_email', $settings['from_email'] ?? '') }}">
                @error('from_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">SMTP Username</label>
                <input type="text" name="smtp_username" class="form-control @error('smtp_username') is-invalid @enderror" value="{{ old('smtp_username', $settings['smtp_username'] ?? '') }}">
                @error('smtp_username') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">SMTP Password</label>
                <input type="password" name="smtp_password" class="form-control @error('smtp_password') is-invalid @enderror" placeholder="{{ ! empty($settings['smtp_password']) ? 'Currently set — leave blank to keep' : '' }}">
                @error('smtp_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">From Name</label>
                <input type="text" name="from_name" class="form-control @error('from_name') is-invalid @enderror" value="{{ old('from_name', $settings['from_name'] ?? '') }}">
                @error('from_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Reply To Email</label>
                <input type="email" name="reply_to_email" class="form-control @error('reply_to_email') is-invalid @enderror" value="{{ old('reply_to_email', $settings['reply_to_email'] ?? '') }}">
                @error('reply_to_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </x-admin.form-card>

</main>

</x-admin-layout>
