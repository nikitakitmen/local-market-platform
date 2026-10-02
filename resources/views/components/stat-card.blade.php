{{-- Карточка показателя для дашбордов --}}
@props(['icon', 'label', 'value', 'hint' => null, 'variant' => null])

<div {{ $attributes->class(['stat-card', 'stat-card-'.$variant => $variant]) }}>
    <div class="stat-card__icon"><i class="bi bi-{{ $icon }}"></i></div>
    <div class="min-w-0">
        <div class="stat-card__label">{{ $label }}</div>
        <div class="stat-card__value">{{ $value }}</div>
        @if ($hint)
            <div class="stat-card__hint">{{ $hint }}</div>
        @endif
    </div>
</div>
