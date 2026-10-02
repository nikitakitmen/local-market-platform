@extends('layouts.account')

@section('title', 'Стать производителем')

@section('page')
    @php
        $isPending = $producer?->status === \App\Enums\ProducerStatus::Pending;
        $isRejected = $producer?->status === \App\Enums\ProducerStatus::Rejected;
    @endphp

    <div class="dash-page-head">
        <div>
            <h1>Стать производителем</h1>
            <p>Продавайте свои товары жителям города. Публиковать товары можно после проверки заявки администратором.</p>
        </div>
    </div>

    @if ($isPending)
        <div class="card mb-4">
            <div class="card-body d-flex flex-column flex-md-row gap-3 align-items-md-center">
                <div class="stat-card__icon" style="background: rgba(227,162,26,.12); color: #b98208; width: 3.5rem; height: 3.5rem; border-radius: 1rem; display: grid; place-items: center; font-size: 1.5rem">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-bold fs-5">Заявка «{{ $producer->name }}» на проверке</div>
                    <div class="text-secondary">Отправлена {{ $application?->created_at->translatedFormat('j F Y') }}. Обычно проверка занимает 1–2 рабочих дня — мы пришлём уведомление.</div>
                </div>
                <x-status-badge :status="$producer->status" class="fs-6" />
            </div>
        </div>
    @else
        @if ($isRejected && $application?->admin_comment)
            <div class="alert alert-danger">
                <div class="fw-bold mb-1"><i class="bi bi-x-octagon me-1"></i>Предыдущая заявка отклонена</div>
                Комментарий администратора: {{ $application->admin_comment }}
                <div class="small mt-1">Исправьте данные и отправьте заявку повторно.</div>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('producer-application.store') }}" enctype="multipart/form-data" novalidate>
                            @csrf
                            @include('producer.partials.profile-fields', ['producer' => $producer])

                            <div class="mt-3">
                                <label class="form-label" for="message">Сообщение администратору</label>
                                <textarea id="message" name="message" rows="3" maxlength="2000" class="form-control @error('message') is-invalid @enderror" placeholder="Расскажите, что вы производите и как давно работаете">{{ old('message') }}</textarea>
                                @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg mt-4"><i class="bi bi-send"></i> Отправить заявку</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <h2 class="h6">Как это работает</h2>
                        <div class="d-grid gap-3 mt-3">
                            <div class="step-item"><span class="step-item__num">1</span><h3>Заявка</h3><p>Заполните анкету: название, тип, контакты, адрес.</p></div>
                            <div class="step-item"><span class="step-item__num">2</span><h3>Проверка</h3><p>Администратор проверит данные и подтвердит профиль.</p></div>
                            <div class="step-item"><span class="step-item__num">3</span><h3>Продажи</h3><p>Добавляйте товары, принимайте заказы, общайтесь с покупателями.</p></div>
                        </div>
                        <hr>
                        <div class="small text-secondary">Комиссия платформы — {{ rtrim(rtrim(number_format(\App\Models\Setting::number('platform_commission_percent'), 2, ',', ''), '0'), ',') }}% от суммы товаров выполненного заказа.</div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
