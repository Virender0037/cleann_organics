{{--
    Cart offers — exactly the two customer-facing offers, driven by
    StorefrontSettings::offers() so the thresholds/perks here always match
    the shipping calculation and voucher issuing.

    $offers   list of ['threshold','title','perks','unlocked','remaining']
    $eligible merchandise value after discount, before shipping
--}}
@props(['offers' => [], 'eligible' => 0])

@php
    $next = collect($offers)->first(fn ($offer) => ! $offer['unlocked']);
    $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
@endphp

@if (count($offers))
<div class="offer-bar" role="region" aria-label="Cart offers">
    <p class="offer-bar__headline" aria-live="polite">
        @if ($next)
            <span class="offer-bar__headline-icon" aria-hidden="true">🎁</span>
            Add <strong>₹{{ $fmt($next['remaining']) }}</strong> more to unlock {{ implode(' + ', $next['perks']) }}
        @else
            <span class="offer-bar__headline-icon" aria-hidden="true">🎉</span>
            You've unlocked every offer — {{ implode(' + ', end($offers)['perks']) }}
        @endif
    </p>

    <div class="offer-bar__tiers">
        @foreach ($offers as $offer)
            @php $percent = $offer['threshold'] > 0 ? min(100, round($eligible / $offer['threshold'] * 100)) : 100; @endphp
            <div class="offer-bar__tier {{ $offer['unlocked'] ? 'is-unlocked' : '' }}">
                <div class="offer-bar__tier-head">
                    <h3 class="offer-bar__tier-title">{{ $offer['title'] }}</h3>
                    <span class="offer-bar__tier-state">{{ $offer['unlocked'] ? 'Unlocked' : '₹'.$fmt($offer['remaining']).' to go' }}</span>
                </div>
                <ul class="offer-bar__perks">
                    @foreach ($offer['perks'] as $perk)
                        <li>{{ $perk }}</li>
                    @endforeach
                </ul>
                <div class="offer-bar__progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $percent }}" aria-label="{{ $offer['title'] }} progress">
                    <span style="width: {{ $percent }}%"></span>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif
