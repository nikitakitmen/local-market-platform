@extends('layouts.admin')

@section('title', 'Жалобы')

@section('content')
    <div class="dash-page-head">
        <div>
            <h1>Жалобы на отзывы</h1>
            <p>Проверьте отзыв и примите решение: скрыть его или отклонить жалобу.</p>
        </div>
    </div>

    <div class="status-tabs">
        @foreach (\App\Enums\ReportStatus::cases() as $case)
            <a href="{{ route('admin.reports.index', ['status' => $case->value]) }}" class="{{ $status === $case ? 'active' : '' }}">{{ $case->label() }} <span class="badge rounded-pill text-bg-light">{{ $counts[$case->value] ?? 0 }}</span></a>
        @endforeach
    </div>

    @if ($reports->isEmpty())
        <x-empty-state icon="shield-check" title="Жалоб нет" text="Новые жалобы покупателей появятся здесь." />
    @else
        <div class="d-grid gap-3">
            @foreach ($reports as $report)
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                            <div>
                                <span class="badge rounded-pill bg-danger-subtle text-danger-emphasis me-1">{{ $report->reason->label() }}</span>
                                <span class="small text-secondary">от {{ $report->user->name }} · {{ $report->created_at->diffForHumans() }}</span>
                            </div>
                            <x-status-badge :status="$report->status" />
                        </div>
                        @if ($report->comment)
                            <div class="small mb-2"><i class="bi bi-chat-left-quote me-1 text-secondary"></i>{{ $report->comment }}</div>
                        @endif
                        <div class="p-3 rounded-3" style="background: #f6f7f4">
                            <div class="d-flex justify-content-between gap-2 mb-1">
                                <span class="fw-semibold">{{ $report->review->user->name }} о товаре «{{ $report->review->product->name }}»</span>
                                <x-rating :value="$report->review->rating" :show-value="false" />
                            </div>
                            <div class="description-text">{{ $report->review->text }}</div>
                            @if ($report->review->is_hidden)
                                <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis mt-2">Отзыв скрыт</span>
                            @endif
                        </div>
                        @if ($report->status === \App\Enums\ReportStatus::Pending)
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <form method="POST" action="{{ route('admin.reports.update', $report) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="decision" value="hide">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-eye-slash"></i> Скрыть отзыв</button>
                                </form>
                                <form method="POST" action="{{ route('admin.reports.update', $report) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="decision" value="dismiss">
                                    <button type="submit" class="btn btn-light btn-sm"><i class="bi bi-x-lg"></i> Отклонить жалобу</button>
                                </form>
                            </div>
                        @else
                            <div class="small text-secondary mt-2">Рассмотрена {{ $report->handled_at?->translatedFormat('j F Y, H:i') }}{{ $report->handler ? ' · '.$report->handler->name : '' }}</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $reports->links() }}</div>
    @endif
@endsection
