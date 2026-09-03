@props(['status', 'label' => null, 'map' => []])

@php
    $defaultMap = [
        'paid' => 'success',
        'delivered' => 'success',
        'active' => 'success',
        'replied' => 'success',
        'approved' => 'success',
        'completed' => 'success',
        'in_stock' => 'success',

        'pending' => 'warning',
        'unread' => 'warning',
        'requested' => 'warning',
        'draft' => 'warning',
        'low_stock' => 'warning',

        'failed' => 'danger',
        'cancelled' => 'danger',
        'rejected' => 'danger',
        'out_of_stock' => 'danger',

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

    // Dedicated admin-badge classes (not Bootstrap's bg-{color} utilities)
    // so the exact approved status colors/contrast apply consistently.
    $badgeClass = 'admin-badge admin-badge--'.$color;
@endphp

<span {{ $attributes->merge(['class' => $badgeClass]) }}>{{ $text }}</span>
