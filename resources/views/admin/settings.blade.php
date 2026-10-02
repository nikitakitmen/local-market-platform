@extends('layouts.admin')

@section('title', 'Настройки')

@section('content')
    <div class="dash-page-head">
        <div>
            <h1>Настройки сайта</h1>
            <p>Доступны только администратору. Изменения применяются к новым заказам.</p>
        </div>
    </div>

    @php
        $groups = [
            'Общие' => ['site_name' => 'text', 'support_email' => 'email', 'support_phone' => 'text'],
            'Заказы и комиссия' => ['platform_commission_percent' => 'number', 'min_order_amount' => 'number'],
            'Доставка' => ['delivery_base_cost' => 'number', 'delivery_cost_per_km' => 'number', 'delivery_free_from' => 'number'],
        ];
    @endphp

    <form method="POST" action="{{ route('admin.settings.update') }}" novalidate style="max-width: 960px">
        @csrf
        @method('PUT')
        <div class="row g-4">
            @foreach ($groups as $title => $fields)
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">{{ $title }}</div>
                        <div class="card-body">
                            <div class="row g-3">
                                @foreach ($fields as $key => $type)
                                    <div class="col-md-4">
                                        <label class="form-label" for="{{ $key }}">{{ \App\Models\Setting::LABELS[$key] }}</label>
                                        <input id="{{ $key }}" type="{{ $type }}" name="{{ $key }}" @if ($type === 'number') step="0.01" min="0" @endif
                                               value="{{ old($key, $values[$key]) }}" class="form-control @error($key) is-invalid @enderror">
                                        @error($key)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                @endforeach
                            </div>
                            @if ($title === 'Доставка')
                                <div class="alert alert-info small mt-3 mb-0">
                                    <i class="bi bi-calculator me-1"></i>
                                    Формула: <strong>базовая стоимость + стоимость за км × удалённость адреса</strong>.
                                    Если сумма товаров заказа не меньше порога «Бесплатная доставка от», доставка бесплатна.
                                    Пример: {{ money($values['delivery_base_cost']) }} + {{ money($values['delivery_cost_per_km']) }} × 7 км = {{ money($values['delivery_base_cost'] + $values['delivery_cost_per_km'] * 7) }}.
                                </div>
                            @endif
                            @if ($title === 'Заказы и комиссия')
                                <div class="small text-secondary mt-3">Комиссия платформы удерживается с суммы товаров выполненного заказа (после скидки). Минимальная сумма проверяется для каждого производителя отдельно.</div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <button type="submit" class="btn btn-primary btn-lg mt-4"><i class="bi bi-check2"></i> Сохранить настройки</button>
    </form>
@endsection
