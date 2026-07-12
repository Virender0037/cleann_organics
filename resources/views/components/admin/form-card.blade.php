@props(['title', 'action', 'method' => 'POST', 'enctype' => null])

@php
    $httpMethod = strtoupper($method);
    $formMethod = in_array($httpMethod, ['GET', 'POST']) ? $httpMethod : 'POST';
@endphp

<div class="card">
    <div class="card-header">
        <h5>{{ $title }}</h5>
    </div>

    <div class="card-body">
        <form action="{{ $action }}" method="{{ $formMethod }}" @if ($enctype) enctype="{{ $enctype }}" @endif>
            @csrf
            @if (! in_array($httpMethod, ['GET', 'POST']))
                @method($httpMethod)
            @endif

            {{ $slot }}

            @isset($actions)
                <div class="text-end">
                    {{ $actions }}
                </div>
            @endisset
        </form>
    </div>
</div>
