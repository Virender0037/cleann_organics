@props(['icon' => null])

<div class="admin-empty-state">
    @if ($icon)
        <i class="{{ $icon }}"></i>
    @endif
    {{ $slot }}
</div>
