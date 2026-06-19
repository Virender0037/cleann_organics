<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Admin Panel' }}</title>

    {{-- Admin CSS --}}
    <link rel="stylesheet" href="{{ asset('admin-dist/css/style.css') }}">
</head>
<body>

    {{-- Sidebar/Header --}}
    @include('admin.partials.sidebar')
    @include('admin.partials.header')

    <main class="admin-content">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    @include('admin.partials.footer')

    {{-- Admin JS --}}
    <script src="{{ asset('admin-dist/js/app.js') }}"></script>
</body>
</html>