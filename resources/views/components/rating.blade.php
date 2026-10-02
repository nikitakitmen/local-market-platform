{{-- Рейтинг звёздами: <x-rating :value="4.6" :count="12" /> --}}
@props(['value' => 0, 'count' => null, 'size' => null, 'showValue' => true])

@php
    $value = (float) $value;
    $full = (int) floor($value);
    $half = ($value - $full) >= 0.5;
@endphp

<span {{ $attributes->class(['rating', 'rating-lg' => $size === 'lg']) }} title="Рейтинг {{ number_format($value, 1, ',', '') }} из 5">
    <span class="rating__stars" aria-hidden="true">
        @for ($i = 1; $i <= 5; $i++)
            @if ($i <= $full)
                <i class="bi bi-star-fill"></i>
            @elseif ($i === $full + 1 && $half)
                <i class="bi bi-star-half"></i>
            @else
                <i class="bi bi-star"></i>
            @endif
        @endfor
    </span>
    @if ($showValue && $value > 0)
        <span class="rating__value">{{ number_format($value, 1, ',', '') }}</span>
    @endif
    @if ($count !== null)
        <span>({{ $count }})</span>
    @endif
</span>
