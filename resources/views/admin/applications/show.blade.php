@extends('layouts.admin')

@section('title', 'Заявка: '.$application->producer->name)

@section('content')
    @php($producer = $application->producer)
    <x-breadcrumbs :items="['Заявки' => route('admin.applications.index'), $producer->name => null]" class="mb-3" />

    <div class="dash-page-head">
        <div class="d-flex align-items-center gap-3">
            <x-producer-logo :producer="$producer" />
            <div>
                <h1>{{ $producer->name }}</h1>
                <p>Заявка от {{ $application->created_at->translatedFormat('j F Y, H:i') }}</p>
            </div>
        </div>
        <x-status-badge :status="$application->status" class="fs-6" />
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">Данные производителя</div>
                <div class="card-body">
                    <div class="key-value"><span>Тип</span><span>{{ $producer->type->label() }}</span></div>
                    <div class="key-value"><span>ИНН</span><span>{{ $producer->inn ?: 'не указан' }}</span></div>
                    <div class="key-value"><span>Город</span><span>{{ $producer->city->name }}</span></div>
                    <div class="key-value"><span>Адрес</span><span>{{ $producer->address }}</span></div>
                    <div class="key-value"><span>Телефон</span><span>{{ $producer->phone }}</span></div>
                    <div class="key-value"><span>Email</span><span>{{ $producer->email }}</span></div>
                    <div class="mt-3">
                        <div class="small fw-semibold text-secondary mb-1">Описание</div>
                        <p class="description-text mb-0">{{ $producer->description ?: '—' }}</p>
                    </div>
                </div>
            </div>
            @if ($application->message)
                <div class="card">
                    <div class="card-header">Сообщение заявителя</div>
                    <div class="card-body"><p class="description-text mb-0">{{ $application->message }}</p></div>
                </div>
            @endif
        </div>
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">Заявитель</div>
                <div class="card-body">
                    <div class="fw-bold">{{ $application->user->name }}</div>
                    <div class="small text-secondary">{{ $application->user->email }} · {{ $application->user->phone }}</div>
                    <div class="small text-secondary mt-1">На платформе с {{ $application->user->created_at->translatedFormat('j F Y') }}</div>
                </div>
            </div>

            @if ($application->isPending())
                <div class="card">
                    <div class="card-header">Решение</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.applications.approve', $application) }}" class="mb-3">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-patch-check"></i> Одобрить заявку</button>
                        </form>
                        <form method="POST" action="{{ route('admin.applications.reject', $application) }}">
                            @csrf
                            <label class="form-label small" for="admin_comment">Причина отказа</label>
                            <textarea id="admin_comment" name="admin_comment" rows="3" class="form-control @error('admin_comment') is-invalid @enderror" placeholder="Что нужно исправить">{{ old('admin_comment') }}</textarea>
                            @error('admin_comment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <button type="submit" class="btn btn-outline-danger w-100 mt-2"><i class="bi bi-x-octagon"></i> Отклонить</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body">
                        <div class="small text-secondary">Рассмотрена {{ $application->reviewed_at?->translatedFormat('j F Y, H:i') }}{{ $application->reviewer ? ' · '.$application->reviewer->name : '' }}</div>
                        @if ($application->admin_comment)
                            <div class="mt-2">{{ $application->admin_comment }}</div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
