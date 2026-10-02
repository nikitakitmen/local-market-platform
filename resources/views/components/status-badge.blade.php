{{-- Бейдж статуса для любого enum с методами label() и color() --}}
@props(['status'])

<span {{ $attributes->class(['badge rounded-pill badge-status', 'bg-'.$status->color().'-subtle', 'text-'.$status->color().'-emphasis']) }}>
    {{ $status->label() }}
</span>
