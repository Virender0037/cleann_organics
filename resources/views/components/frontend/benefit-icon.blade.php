{{--
    Built-in benefit-strip icons (keys = HomeBanner::ICONS). Decorative: the
    card's title carries the meaning, so the SVG is aria-hidden. Inherits
    colour via currentColor. Also used for the admin list/form previews.
--}}
@props(['name' => 'leaf', 'size' => 40])
@php $attrs = 'width="'.(int) $size.'" height="'.(int) $size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"'; @endphp
@switch($name)
    @case('truck')
        <svg {!! $attrs !!}><path d="M3 6.5h11v9H3z"/><path d="M14 9.5h4l3 3v3h-7z"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17" cy="17.5" r="1.8"/></svg>
        @break
    @case('headset')
        <svg {!! $attrs !!}><path d="M4 14v-2a8 8 0 0 1 16 0v2"/><rect x="3" y="14" width="4" height="6" rx="1.5"/><rect x="17" y="14" width="4" height="6" rx="1.5"/><path d="M19 20c0 1.4-2.2 2-5 2h-1"/></svg>
        @break
    @case('shield')
        <svg {!! $attrs !!}><path d="M12 3l7.5 3v5.5c0 4.6-3.1 8.2-7.5 9.5-4.4-1.3-7.5-4.9-7.5-9.5V6z"/><path d="M8.8 12.2l2.3 2.3 4.2-4.6"/></svg>
        @break
    @case('money-back')
        <svg {!! $attrs !!}><path d="M4 12a8 8 0 1 0 2.4-5.7"/><path d="M4 4.5v3.8h3.8"/><path d="M14.5 9.2c-.4-.7-1.2-1.1-2.2-1.1-1.3 0-2.2.7-2.2 1.7 0 2.3 4.5 1.1 4.5 3.5 0 1-.9 1.8-2.3 1.8-1.1 0-2-.5-2.5-1.3"/><path d="M12.2 7v1.1M12.2 15.1v1.1"/></svg>
        @break
    @case('gift')
        <svg {!! $attrs !!}><rect x="3.5" y="9" width="17" height="11" rx="1.5"/><path d="M2.5 6.5h19V9h-19zM12 6.5V20"/><path d="M12 6.5C10 6.5 8 5.6 8 4.2 8 3 9 2.6 10 3c1.3.5 2 2.2 2 3.5zM12 6.5c2 0 4-.9 4-2.3 0-1.2-1-1.6-2-1.2-1.3.5-2 2.2-2 3.5z"/></svg>
        @break
    @case('tag')
        <svg {!! $attrs !!}><path d="M3 12.2V4.5A1.5 1.5 0 0 1 4.5 3h7.7l8.5 8.5a1.5 1.5 0 0 1 0 2.1l-7.1 7.1a1.5 1.5 0 0 1-2.1 0z"/><circle cx="8" cy="8" r="1.4"/></svg>
        @break
    @case('star')
        <svg {!! $attrs !!}><path d="M12 3.2l2.6 5.5 6 .8-4.4 4.2 1.1 6-5.3-2.9-5.3 2.9 1.1-6L3.4 9.5l6-.8z"/></svg>
        @break
    @default
        <svg {!! $attrs !!}><path d="M5 19C5 10 10 5 20 4c0 10-5 15-14 15z"/><path d="M5 19c3-4 6-7 10-9"/></svg>
@endswitch
