{{-- Логотип производителя или первая буква названия, если логотипа нет --}}
@props(['producer', 'size' => null])

@if ($producer->logo_url)
    <img src="{{ $producer->logo_url }}" alt="{{ $producer->name }}" loading="lazy"
         {{ $attributes->class(['producer-logo', 'producer-logo-'.$size => $size]) }}>
@else
    <span {{ $attributes->class(['producer-logo', 'producer-logo-'.$size => $size]) }} aria-hidden="true">
        {{ mb_strtoupper(mb_substr(preg_replace('/[«»"\']/u', '', $producer->name), 0, 1)) }}
    </span>
@endif
