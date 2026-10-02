@extends('layouts.producer')

@section('title', 'Отзывы')

@section('content')
    <div class="dash-page-head">
        <div>
            <h1>Отзывы</h1>
            <p>Рейтинг магазина: <strong>{{ $producer->reviews_count ? number_format($producer->rating, 1, ',', '') : '—' }}</strong> на основе {{ $producer->reviews_count }} {{ plural($producer->reviews_count, 'отзыва', 'отзывов', 'отзывов') }}.</p>
        </div>
    </div>

    <div class="status-tabs">
        @foreach (['' => 'Все', 'unanswered' => 'Без ответа', 'negative' => 'Оценка 3 и ниже'] as $value => $label)
            <a href="{{ route('producer.reviews.index', array_filter(['filter' => $value])) }}" class="{{ (string) $filter === (string) $value ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    @if ($reviews->isEmpty())
        <x-empty-state icon="star" title="Отзывов нет" text="Отзывы появятся после выполненных заказов." />
    @else
        <div class="d-grid gap-3">
            @foreach ($reviews as $review)
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar">{{ $review->user->initials() }}</span>
                                <div>
                                    <div class="fw-bold">{{ $review->user->name }}</div>
                                    <div class="small text-secondary">{{ $review->created_at->translatedFormat('j F Y') }} · {{ $review->product->name }}</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-start gap-2">
                                @if ($review->is_hidden)
                                    <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">Скрыт модератором</span>
                                @endif
                                <x-rating :value="$review->rating" :show-value="false" />
                            </div>
                        </div>
                        <p class="description-text mb-3">{{ $review->text }}</p>

                        <form method="POST" action="{{ route('producer.reviews.reply', $review) }}">
                            @csrf
                            <label class="form-label small" for="reply{{ $review->id }}">{{ $review->reply ? 'Ваш ответ' : 'Ответить покупателю' }}</label>
                            <div class="d-flex gap-2 align-items-start">
                                <textarea id="reply{{ $review->id }}" name="reply" rows="2" maxlength="1000" class="form-control" placeholder="Спасибо за отзыв!">{{ $review->reply }}</textarea>
                                <button type="submit" class="btn btn-soft text-nowrap">{{ $review->reply ? 'Обновить' : 'Ответить' }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $reviews->links() }}</div>
    @endif
@endsection
