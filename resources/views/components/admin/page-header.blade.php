@props(['title', 'subtitle' => null])

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="admin-page-title">{{ $title }}</h4>
        @if ($subtitle)
            <p class="admin-page-subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($actions)
        <div>{{ $actions }}</div>
    @endisset
</div>
