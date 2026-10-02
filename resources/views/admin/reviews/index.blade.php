@extends('layouts.admin')

@section('title', 'Отзывы')

@section('content')
    <div class="dash-page-head">
        <div>
            <h1>Отзывы</h1>
            <p>Скрытые отзывы не показываются покупателям и не учитываются в рейтинге.</p>
        </div>
    </div>

    <div class="table-card">
        <div class="table-card__head">
            <form method="GET" class="filter-bar w-100" data-no-loading>
                <input type="search" name="q" value="{{ $search }}" class="form-control" placeholder="Текст отзыва">
                <select name="rating" class="form-select" data-autosubmit>
                    <option value="">Любая оценка</option>
                    @for ($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" @selected((int) request('rating') === $i)>{{ $i }} ★</option>
                    @endfor
                </select>
                <select name="visibility" class="form-select" data-autosubmit>
                    <option value="">Все</option>
                    <option value="hidden" @selected(request('visibility') === 'hidden')>Скрытые</option>
                </select>
                <button type="submit" class="btn btn-light"><i class="bi bi-search"></i></button>
            </form>
        </div>
        @if ($reviews->isEmpty())
            <x-empty-state icon="chat-square-text" title="Отзывы не найдены" :compact="true" />
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Отзыв</th><th>Товар</th><th>Оценка</th><th>Статус</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($reviews as $review)
                            <tr>
                                <td style="min-width: 280px">
                                    <div class="fw-semibold">{{ $review->user->name }} <span class="small text-secondary fw-normal">· {{ $review->created_at->translatedFormat('j M Y') }}</span></div>
                                    <div class="small">{{ \Illuminate\Support\Str::limit($review->text, 140) }}</div>
                                    @if ($review->reports_count)
                                        <span class="badge rounded-pill bg-danger-subtle text-danger-emphasis mt-1"><i class="bi bi-flag me-1"></i>Жалоб: {{ $review->reports_count }}</span>
                                    @endif
                                </td>
                                <td class="small">{{ $review->product->name }}<div class="text-secondary">{{ $review->producer->name }}</div></td>
                                <td class="text-nowrap"><x-rating :value="$review->rating" :show-value="false" /></td>
                                <td>
                                    @if ($review->is_hidden)
                                        <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">Скрыт</span>
                                    @else
                                        <span class="badge rounded-pill bg-success-subtle text-success-emphasis">Опубликован</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <form method="POST" action="{{ route('admin.reviews.visibility', $review) }}" class="d-inline" data-no-loading>
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-light btn-icon btn-sm" title="{{ $review->is_hidden ? 'Показать' : 'Скрыть' }}"><i class="bi {{ $review->is_hidden ? 'bi-eye' : 'bi-eye-slash' }}"></i></button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" class="d-inline" data-confirm="Удалить отзыв пользователя {{ $review->user->name }}?">
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
            @if ($reviews->hasPages())
                <div class="table-card__foot">{{ $reviews->links() }}</div>
            @endif
        @endif
    </div>
@endsection
