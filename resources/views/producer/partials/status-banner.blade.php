{{-- Предупреждение, если профиль производителя не подтверждён или заблокирован --}}
@php($producer = auth()->user()->producer)
@if ($producer && ! $producer->isApproved())
    <div class="alert alert-warning d-flex gap-2">
        <i class="bi bi-exclamation-triangle mt-1"></i>
        <div>
            <strong>Профиль «{{ $producer->name }}»: {{ mb_strtolower($producer->status->label()) }}.</strong>
            Товары скрыты из каталога, публикация новых товаров недоступна. Обратитесь в поддержку платформы.
        </div>
    </div>
@endif
