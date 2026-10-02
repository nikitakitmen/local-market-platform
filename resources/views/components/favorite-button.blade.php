{{-- Кнопка «в избранное» (AJAX). Для гостя ведёт на страницу входа. --}}
@props(['product', 'static' => false])

@php($active = in_array($product->id, favorite_ids(), true))

@auth
    @if (auth()->user()->canShop())
        <button type="button"
                {{ $attributes->class(['fav-btn', 'fav-btn-static' => $static, 'is-active' => $active]) }}
                data-favorite-toggle data-url="{{ route('favorites.toggle', $product) }}"
                aria-pressed="{{ $active ? 'true' : 'false' }}"
                title="{{ $active ? 'Убрать из избранного' : 'В избранное' }}">
            <i class="bi bi-heart"></i>
        </button>
    @endif
@else
    <a href="{{ route('login') }}" {{ $attributes->class(['fav-btn', 'fav-btn-static' => $static]) }} title="Войдите, чтобы добавить в избранное">
        <i class="bi bi-heart"></i>
    </a>
@endauth
