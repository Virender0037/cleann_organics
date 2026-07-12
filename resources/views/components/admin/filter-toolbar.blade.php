@props(['action'])

<form method="GET" action="{{ $action }}" class="row g-3 mb-4">
    {{ $slot }}

    @isset($submit)
        <div class="col-md-2">{{ $submit }}</div>
    @endisset
</form>
