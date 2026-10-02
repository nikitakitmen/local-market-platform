@extends('layouts.admin')

@section('title', 'Промокоды')

@section('content')
    <div class="dash-page-head">
        <div>
            <h1>Промокоды</h1>
            <p>Скидка применяется к товарам корзины и распределяется между заказами разных производителей.</p>
        </div>
        <a href="{{ route('admin.promo-codes.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Новый промокод</a>
    </div>

    <div class="table-card">
        @if ($promoCodes->isEmpty())
            <x-empty-state icon="ticket-perforated" title="Промокодов нет" :compact="true">
                <a href="{{ route('admin.promo-codes.create') }}" class="btn btn-primary">Создать промокод</a>
            </x-empty-state>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Код</th><th>Скидка</th><th>Мин. сумма</th><th>Период действия</th><th>Использован</th><th>Статус</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($promoCodes as $promo)
                            <tr>
                                <td><code class="fs-6 fw-bold">{{ $promo->code }}</code></td>
                                <td class="fw-semibold text-nowrap">{{ $promo->label }}</td>
                                <td class="text-nowrap">{{ $promo->min_order_amount ? money($promo->min_order_amount) : '—' }}</td>
                                <td class="small text-nowrap">{{ $promo->start_date?->format('d.m.Y') ?? '…' }} — {{ $promo->end_date?->format('d.m.Y') ?? '…' }}</td>
                                <td>{{ $promo->used_count }}</td>
                                <td>
                                    @if ($promo->isCurrentlyActive())
                                        <span class="badge rounded-pill badge-status bg-success-subtle text-success-emphasis">Действует</span>
                                    @elseif (! $promo->is_active)
                                        <span class="badge rounded-pill badge-status bg-secondary-subtle text-secondary-emphasis">Отключён</span>
                                    @else
                                        <span class="badge rounded-pill badge-status bg-warning-subtle text-warning-emphasis">Вне срока</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('admin.promo-codes.edit', $promo) }}" class="btn btn-light btn-icon btn-sm" title="Редактировать"><i class="bi bi-pencil"></i></a>
                                    <form method="POST" action="{{ route('admin.promo-codes.destroy', $promo) }}" class="d-inline" data-confirm="Удалить промокод {{ $promo->code }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-light btn-icon btn-sm text-danger" title="Удалить"><i class="bi bi-trash3"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($promoCodes->hasPages())
                <div class="table-card__foot">{{ $promoCodes->links() }}</div>
            @endif
        @endif
    </div>
@endsection
