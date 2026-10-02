{{-- Карточка производителя --}}
@props(['producer'])

<a href="{{ route('producers.show', $producer->slug) }}" {{ $attributes->class(['producer-card']) }}>
    <div class="producer-card__head">
        <x-producer-logo :producer="$producer" />
        <div class="min-w-0">
            <div class="producer-card__name">{{ $producer->name }}</div>
            <div class="small text-secondary">{{ $producer->type->label() }} · {{ $producer->city?->name }}</div>
        </div>
    </div>
    <p class="producer-card__desc">{{ $producer->description }}</p>
    <div class="producer-card__stats">
        @if ($producer->reviews_count > 0)
            <x-rating :value="$producer->rating" :count="$producer->reviews_count" />
        @else
            <span><i class="bi bi-star"></i>Пока без отзывов</span>
        @endif
        @isset($producer->products_count)
            <span><i class="bi bi-box-seam"></i>{{ $producer->products_count }} {{ plural($producer->products_count, 'товар', 'товара', 'товаров') }}</span>
        @endisset
    </div>
</a>
