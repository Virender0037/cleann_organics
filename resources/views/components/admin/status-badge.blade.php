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

    // success/warning/info are too light for legible white text (WCAG contrast ~1.6-2.1:1);
    // dark text keeps the same fill colors but stays readable, matching the text-dark fix
    // the contact-messages page already applied by hand for its warning badge.
    $needsDarkText = in_array($color, ['success', 'warning', 'info'], true);
    $badgeClass = 'badge bg-'.$color.($needsDarkText ? ' text-dark' : '');
@endphp

<span {{ $attributes->merge(['class' => $badgeClass]) }}>{{ $text }}</span>
