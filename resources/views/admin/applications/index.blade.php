@extends('layouts.admin')

@section('title', 'Заявки производителей')

@section('content')
    <div class="dash-page-head">
        <div>
            <h1>Заявки производителей</h1>
            <p>После одобрения пользователь получает роль производителя и может публиковать товары.</p>
        </div>
    </div>

    <div class="status-tabs">
        @foreach (\App\Enums\ProducerStatus::cases() as $case)
            <a href="{{ route('admin.applications.index', ['status' => $case->value]) }}" class="{{ $status === $case ? 'active' : '' }}">
                {{ $case === \App\Enums\ProducerStatus::Approved ? 'Одобренные' : ($case === \App\Enums\ProducerStatus::Rejected ? 'Отклонённые' : 'На проверке') }}
                <span class="badge rounded-pill text-bg-light">{{ $counts[$case->value] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <div class="table-card">
        @if ($applications->isEmpty())
            <x-empty-state icon="person-check" title="Заявок нет" text="Новые заявки появятся здесь." :compact="true" />
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Производитель</th><th>Заявитель</th><th>Тип и город</th><th>Подана</th><th>Статус</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($applications as $application)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <x-producer-logo :producer="$application->producer" size="sm" />
                                        <span class="fw-semibold">{{ $application->producer->name }}</span>
                                    </div>
                                </td>
                                <td><div>{{ $application->user->name }}</div><div class="small text-secondary">{{ $application->user->email }}</div></td>
                                <td class="small">{{ $application->producer->type->label() }} · {{ $application->producer->city->name }}</td>
                                <td class="small text-secondary text-nowrap">{{ $application->created_at->translatedFormat('j M Y, H:i') }}</td>
                                <td><x-status-badge :status="$application->status" /></td>
                                <td class="text-end"><a href="{{ route('admin.applications.show', $application) }}" class="btn {{ $application->isPending() ? 'btn-primary' : 'btn-light' }} btn-sm">{{ $application->isPending() ? 'Рассмотреть' : 'Открыть' }}</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($applications->hasPages())
                <div class="table-card__foot">{{ $applications->links() }}</div>
            @endif
        @endif
    </div>
@endsection
