@extends('layouts.account')

@section('title', 'Адреса доставки')

@section('page')
    <div class="dash-page-head">
        <div>
            <h1>Адреса доставки</h1>
            <p>Сохранённые адреса подставляются при оформлении заказа.</p>
        </div>
        <a href="{{ route('account.addresses.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Добавить адрес</a>
    </div>

    @if ($addresses->isEmpty())
        <x-empty-state icon="geo-alt" title="Адресов пока нет" text="Добавьте адрес, чтобы быстрее оформлять заказы с доставкой.">
            <a href="{{ route('account.addresses.create') }}" class="btn btn-primary">Добавить адрес</a>
        </x-empty-state>
    @else
        <div class="row g-3">
            @foreach ($addresses as $address)
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div class="fw-bold"><i class="bi bi-geo-alt text-primary me-1"></i>{{ $address->title ?: 'Адрес' }}</div>
                                @if ($address->is_default)
                                    <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis">Основной</span>
                                @endif
                            </div>
                            <div>{{ $address->full_address }}</div>
                            <div class="small text-secondary mt-1">{{ $address->distance_label }}</div>
                            @if ($address->comment)
                                <div class="small text-secondary mt-1">{{ $address->comment }}</div>
                            @endif
                            <div class="d-flex gap-2 mt-auto pt-3">
                                <a href="{{ route('account.addresses.edit', $address) }}" class="btn btn-light btn-sm"><i class="bi bi-pencil"></i> Изменить</a>
                                <form method="POST" action="{{ route('account.addresses.destroy', $address) }}" data-confirm="Удалить адрес «{{ $address->title ?: $address->street }}»?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-light btn-sm text-danger"><i class="bi bi-trash3"></i> Удалить</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
