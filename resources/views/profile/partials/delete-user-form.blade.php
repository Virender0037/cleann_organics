<div class="dashboard__content-card">
    <div class="dashboard__content-card-header">
        <h5 class="font-body--xxl-500">{{ __('Delete Account') }}</h5>
    </div>
    <div class="dashboard__content-card-body">
        <p class="font-body--md-400" style="color:#666666;margin-bottom:24px;">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>

        {{-- Native <details> replaces the Breeze Alpine modal: same
             confirm-before-submit step with no JavaScript dependency. Opens
             automatically when the userDeletion bag has errors, mirroring the
             original modal's :show binding. --}}
        <details @if ($errors->userDeletion->isNotEmpty()) open @endif>
            <summary class="button button--md" style="cursor:pointer;background-color:#EA4B48;display:inline-block;list-style:none;">
                {{ __('Delete Account') }}
            </summary>

            <form method="post" action="{{ route('profile.destroy') }}" style="margin-top:24px;">
                @csrf
                @method('delete')

                <div class="contact-form__content">
                    <p class="font-body--md-400" style="color:#666666;">
                        {{ __('Are you sure you want to delete your account? Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                    </p>

                    <div class="contact-form-input">
                        <label for="delete_account_password">{{ __('Password') }}</label>
                        <input
                            type="password"
                            id="delete_account_password"
                            name="password"
                            placeholder="{{ __('Password') }}"
                            autocomplete="current-password"
                        />
                        @foreach ($errors->userDeletion->get('password') as $message)
                            <span style="color:#EA4B48;font-size:12px;">{{ $message }}</span>
                        @endforeach
                    </div>

                    <div class="contact-form-btn">
                        <button class="button button--md" type="submit" style="background-color:#EA4B48;">
                            {{ __('Delete Account') }}
                        </button>
                    </div>
                </div>
            </form>
        </details>
    </div>
</div>
