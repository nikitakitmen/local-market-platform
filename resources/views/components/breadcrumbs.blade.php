{{-- Хлебные крошки: <x-breadcrumbs :items="['Каталог' => route('catalog'), 'Сыр' => null]" /> --}}
@props(['items' => []])

<nav aria-label="Навигационная цепочка" {{ $attributes }}>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bi bi-house"></i></a></li>
        @foreach ($items as $label => $url)
            @if ($url && ! $loop->last)
                <li class="breadcrumb-item"><a href="{{ $url }}">{{ $label }}</a></li>
            @else
                <li class="breadcrumb-item active" aria-current="page">{{ $label }}</li>
            @endif
        @endforeach
    </ol>
</nav>
