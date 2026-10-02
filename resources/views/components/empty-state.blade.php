{{-- Пустое состояние списка: <x-empty-state icon="bag" title="Корзина пуста" text="..."> кнопка </x-empty-state> --}}
@props(['icon' => 'inbox', 'title', 'text' => null, 'compact' => false])

<div {{ $attributes->class(['empty-state', 'empty-state-compact' => $compact]) }}>
    <div class="empty-state__icon"><i class="bi bi-{{ $icon }}"></i></div>
    <h3>{{ $title }}</h3>
    @if ($text)
        <p>{{ $text }}</p>
    @endif
    {{ $slot }}
</div>
