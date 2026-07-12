@props(['status', 'label' => null, 'map' => []])

@php
    $defaultMap = [
        'paid' => 'success',
        'delivered' => 'success',
        'active' => 'success',
        'replied' => 'success',
        'approved' => 'success',
        'completed' => 'success',

        'pending' => 'warning',
        'unread' => 'warning',
        'requested' => 'warning',
        'draft' => 'warning',

        'failed' => 'danger',
        'cancelled' => 'danger',
        'rejected' => 'danger',

        'refunded' => 'secondary',
        'inactive' => 'secondary',

        'shipped' => 'info',
        'read' => 'info',
        'confirmed' => 'info',
        'packed' => 'info',
        'picked_up' => 'info',
        'received' => 'info',
    ];

    $colorMap = array_merge($defaultMap, $map);
    $color = $colorMap[$status] ?? 'primary';
    $text = $label ?? ucwords(str_replace('_', ' ', (string) $status));
@endphp

<span {{ $attributes->merge(['class' => 'badge bg-'.$color]) }}>{{ $text }}</span>
