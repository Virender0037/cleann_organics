<div class="dashboard__content-card">
    <div class="dashboard__content-card-header">
        <h5 class="font-body--xxl-500">{{ __('Update Password') }}</h5>
    </div>
    <div class="dashboard__content-card-body">
        <p class="font-body--md-400" style="color:#666666;margin-bottom:24px;">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>

        @if (session('status') === 'password-updated')
            <span style="color:#00B307;font-size:12px;">{{ __('Saved.') }}</span>
        @endif

        <form method="post" action="{{ route('password.update') }}">
            @csrf
            @method('put')

            <div class="contact-form__content">
                <div class="contact-form-input">
                    <label for="update_password_current_password">{{ __('Current Password') }}</label>
                    <input
                        type="password"
                        id="update_password_current_password"
                        name="current_password"
                        placeholder="{{ __('Current Password') }}"
                        autocomplete="current-password"
                    />
                    @foreach ($errors->updatePassword->get('current_password') as $message)
                        <span style="color:#EA4B48;font-size:12px;">{{ $message }}</span>
                    @endforeach
                </div>

                <div class="contact-form-input">
                    <label for="update_password_password">{{ __('New Password') }}</label>
                    <input
                        type="password"
                        id="update_password_password"
                        name="password"
                        placeholder="{{ __('New Password') }}"
                        autocomplete="new-password"
                    />
                    @foreach ($errors->updatePassword->get('password') as $message)
                        <span style="color:#EA4B48;font-size:12px;">{{ $message }}</span>
                    @endforeach
                </div>

                <div class="contact-form-input">
                    <label for="update_password_password_confirmation">{{ __('Confirm Password') }}</label>
                    <input
                        type="password"
                        id="update_password_password_confirmation"
                        name="password_confirmation"
                        placeholder="{{ __('Confirm Password') }}"
                        autocomplete="new-password"
                    />
                    @foreach ($errors->updatePassword->get('password_confirmation') as $message)
                        <span style="color:#EA4B48;font-size:12px;">{{ $message }}</span>
                    @endforeach
                </div>

                <div class="contact-form-btn">
                    <button class="button button--md" type="submit">{{ __('Save') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
