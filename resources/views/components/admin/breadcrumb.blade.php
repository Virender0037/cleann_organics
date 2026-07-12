@props(['items' => []])

@php
    $crumbs = array_merge(
        [['label' => 'Dashboard', 'url' => route('admin.dashboard')]],
        $items
    );
    $lastIndex = count($crumbs) - 1;
@endphp

<div class="mb-3">
    @foreach ($crumbs as $index => $crumb)
        @if ($index === $lastIndex || empty($crumb['url']))
            <span>{{ $crumb['label'] }}</span>
        @else
            <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
        @endif

        @if ($index !== $lastIndex)
            <span class="mx-2">›</span>
        @endif
    @endforeach
</div>
