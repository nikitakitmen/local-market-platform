{{-- Карточка товара для каталога, главной страницы, страницы производителя и избранного --}}
@props(['product'])

@php
    $available = $product->isAvailable();
    $hasVariants = $product->hasVariants();
    $defaultVariant = $hasVariants ? $product->defaultVariant() : null;
    $canBuy = ! auth()->check() || auth()->user()->canShop();
    $isNew = $product->created_at?->gt(now()->subDays(14));
@endphp

<article {{ $attributes->class(['product-card', 'is-unavailable' => ! $available]) }} data-variant-scope>
    <a href="{{ route('products.show', $product->slug) }}" class="product-card__image" aria-label="{{ $product->name }}">
        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
        <span class="product-card__badges">
            @unless ($available)
                <span class="badge badge-floating text-bg-light">Нет в наличии</span>
            @endunless
            @if ($isNew && $available)
                <span class="badge badge-floating text-bg-primary">Новинка</span>
            @endif
        </span>
    </a>

    <x-favorite-button :product="$product" />

    <div class="product-card__body">
        <a href="{{ route('producers.show', $product->producer->slug) }}" class="product-card__producer">{{ $product->producer->name }}</a>
        <a href="{{ route('products.show', $product->slug) }}" class="product-card__title">{{ $product->name }}</a>

        <div class="product-card__meta">
            @if ($product->reviews_count > 0)
                <x-rating :value="$product->rating" :count="$product->reviews_count" />
            @else
                <span class="text-muted-2">Нет отзывов</span>
            @endif
            @if ($product->unit && ! $hasVariants)
                <span class="divider-dot">{{ $product->unit }}</span>
            @endif
        </div>

        <div class="product-card__footer">
            <div class="product-card__price" data-variant-price>
                @if ($hasVariants)
                    {{ money($defaultVariant->price) }}
                @else
                    {{ money($product->price) }}
                @endif
            </div>

            @if ($canBuy)
                <form action="{{ route('cart.store') }}" method="POST" data-cart-form>
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <div class="product-card__actions flex-column">
                        @if ($hasVariants)
                            <select name="variant_id" class="form-select form-select-sm" data-variant-input aria-label="Вариант товара" @disabled(! $available)>
                                @foreach ($product->variants as $variant)
                                    <option value="{{ $variant->id }}" data-price="{{ money($variant->price) }}"
                                            @selected($variant->id === $defaultVariant?->id) @disabled(! $variant->in_stock)>
                                        {{ $variant->name }}{{ $variant->in_stock ? '' : ' — нет' }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        <button type="submit" class="btn btn-primary btn-cart" @disabled(! $available)>
                            <i class="bi bi-bag-plus"></i><span>{{ $available ? 'В корзину' : 'Нет в наличии' }}</span>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</article>
