<div class="dashboard__content-card">
    <div class="dashboard__content-card-header">
        <h5 class="font-body--xxl-500">{{ __('Profile Information') }}</h5>
    </div>
    <div class="dashboard__content-card-body">
        <p class="font-body--md-400" style="color:#666666;margin-bottom:24px;">
            {{ __("Update your account's profile information and email address.") }}
        </p>

        @if (session('status') === 'profile-updated')
            <span style="color:#00B307;font-size:12px;">{{ __('Saved.') }}</span>
        @endif

        {{-- Kept for the email-verification resend button below; unchanged. --}}
        <form id="send-verification" method="post" action="{{ route('verification.send') }}">
            @csrf
        </form>

        <form method="post" action="{{ route('profile.update') }}">
            @csrf
            @method('patch')

            <div class="contact-form__content">
                <div class="contact-form-input">
                    <label for="name">{{ __('Name') }}</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        placeholder="{{ __('Name') }}"
                        required
                        autofocus
                        autocomplete="name"
                    />
                    @error('name')
                        <span style="color:#EA4B48;font-size:12px;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="contact-form-input">
                    <label for="email">{{ __('Email') }}</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        placeholder="{{ __('Email') }}"
                        required
                        autocomplete="username"
                    />
                    @error('email')
                        <span style="color:#EA4B48;font-size:12px;">{{ $message }}</span>
                    @enderror

                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <p class="font-body--md-400" style="margin-top:8px;">
                            {{ __('Your email address is unverified.') }}
                            <button form="send-verification" type="submit" style="background:none;border:0;padding:0;text-decoration:underline;color:#00B207;cursor:pointer;">
                                {{ __('Click here to re-send the verification email.') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <span style="color:#00B307;font-size:12px;">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </span>
                        @endif
                    @endif
                </div>

                <div class="contact-form-btn">
                    <button class="button button--md" type="submit">{{ __('Save') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
