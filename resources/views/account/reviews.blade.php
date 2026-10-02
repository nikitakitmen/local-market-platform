@extends('layouts.account')

@section('title', 'Мои отзывы')

@section('page')
    <div class="dash-page-head">
        <div>
            <h1>Мои отзывы</h1>
            <p>Отзывы можно оставлять на товары из выполненных заказов.</p>
        </div>
    </div>

    @if ($reviews->isEmpty())
        <x-empty-state icon="star" title="Отзывов пока нет" text="После получения заказа откройте его в разделе «Мои заказы» и оцените товары.">
            <a href="{{ route('account.orders.index', ['status' => 'completed']) }}" class="btn btn-primary">Выполненные заказы</a>
        </x-empty-state>
    @else
        <div class="d-grid gap-3">
            @foreach ($reviews as $review)
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex gap-3">
                            <img src="{{ $review->product->image_url }}" alt="" class="table-thumb">
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex flex-wrap justify-content-between gap-2">
                                    <div class="min-w-0">
                                        @if ($review->product->trashed())
                                            <span class="fw-bold">{{ $review->product->name }}</span>
                                        @else
                                            <a href="{{ route('products.show', $review->product->slug) }}" class="fw-bold text-reset">{{ $review->product->name }}</a>
                                        @endif
                                        <div class="small text-secondary">{{ $review->producer->name }} · {{ $review->created_at->translatedFormat('j F Y') }}</div>
                                    </div>
                                    <div class="d-flex align-items-start gap-2">
                                        @if ($review->is_hidden)
                                            <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">Скрыт модератором</span>
                                        @endif
                                        <x-rating :value="$review->rating" :show-value="false" />
                                    </div>
                                </div>
                                <p class="mt-2 mb-0 description-text">{{ $review->text }}</p>
                                @if ($review->reply)
                                    <div class="review-reply mt-2"><span class="fw-bold small d-block">Ответ производителя</span>{{ $review->reply }}</div>
                                @endif
                                <form method="POST" action="{{ route('reviews.destroy', $review) }}" class="mt-2" data-confirm="Удалить этот отзыв?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link btn-sm text-danger p-0"><i class="bi bi-trash3"></i> Удалить</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $reviews->links() }}</div>
    @endif
@endsection
