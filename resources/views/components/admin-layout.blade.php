
    @include('admin.partials.header')
    @include('admin.partials.sidebar')
        {{ $slot }}
    @include('admin.partials.footer')