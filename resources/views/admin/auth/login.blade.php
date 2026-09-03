<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login — Cleann Organics</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="{{ asset('assets/images/favicon.svg') }}">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/fonts/phosphor/regular/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/admin-custom.css') }}" />
</head>
<body class="admin-login-body">

    <div class="admin-login-card">
        <div class="admin-login-accent" aria-hidden="true"></div>

        <img src="{{ asset('assets/images/vertical-logo.jpeg') }}" alt="Cleann Organics" class="admin-login-logo">

        <h1 class="admin-login-heading">Admin Sign In</h1>
        <p class="admin-login-subtext">Sign in to manage Cleann Organics</p>

        @if (session('error'))
            <div class="admin-login-alert" role="alert">{{ session('error') }}</div>
        @endif

        @if ($errors->any() && ! $errors->has('email') && ! $errors->has('password'))
            <div class="admin-login-alert" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.login.store') }}" novalidate>
            @csrf

            <div class="admin-login-field">
                <label for="email" class="admin-login-label">Email</label>
                <div class="admin-login-input-wrap">
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="admin-login-input @error('email') is-invalid @enderror"
                        autocomplete="username"
                        autofocus
                        required
                        aria-required="true"
                        @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                    >
                </div>
                @error('email')
                    <p id="email-error" class="admin-login-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="admin-login-field">
                <label for="password" class="admin-login-label">Password</label>
                <div class="admin-login-input-wrap">
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="admin-login-input @error('password') is-invalid @enderror"
                        autocomplete="current-password"
                        required
                        aria-required="true"
                        @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                    >
                    <button type="button" class="admin-login-toggle-password" id="togglePassword" aria-label="Show password" aria-pressed="false">
                        <i class="ph ph-eye" id="togglePasswordIcon" aria-hidden="true"></i>
                    </button>
                </div>
                @error('password')
                    <p id="password-error" class="admin-login-error">{{ $message }}</p>
                @enderror
            </div>

            <label class="admin-login-remember">
                <input type="checkbox" name="remember">
                Remember me
            </label>

            <button type="submit" class="admin-login-submit">Login</button>
        </form>
    </div>

    <script>
        (function () {
            var toggle = document.getElementById('togglePassword');
            var icon = document.getElementById('togglePasswordIcon');
            var input = document.getElementById('password');

            toggle.addEventListener('click', function () {
                var isPassword = input.getAttribute('type') === 'password';
                input.setAttribute('type', isPassword ? 'text' : 'password');
                toggle.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
                toggle.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
                icon.className = isPassword ? 'ph ph-eye-slash' : 'ph ph-eye';
            });
        })();
    </script>
</body>
</html>
