{{--
    A marketplace's mark: the uploaded logo (public/images/marketplaces/{key}.svg|png|webp|jpg) when one exists,
    otherwise a neutral text wordmark. The box has a fixed height either way, so a missing or oddly-shaped logo
    can never change card height or alignment. Logos are local files only — never hotlinked.
--}}
@props(['label', 'logo' => null])
<span class="compare__mark {{ $logo ? 'compare__mark--logo' : 'compare__mark--text' }}">
    @if ($logo)
        <img src="{{ $logo }}" alt="{{ $label }}" class="compare__mark-img" loading="lazy" decoding="async" />
    @else
        <span class="compare__mark-text">{{ $label }}</span>
    @endif
</span>
