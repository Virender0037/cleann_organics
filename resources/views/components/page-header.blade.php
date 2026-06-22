<!DOCTYPE html>
<html lang="en">
<head>
    @include('admin.partials.header')
</head>
<body>

    @include('admin.partials.sidebar')

    <main class="pc-container">
        <div class="pc-content">
            {{ $slot }}
        </div>
    </main>

    @include('admin.partials.footer')

</body>
</html>