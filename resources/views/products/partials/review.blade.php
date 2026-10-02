{{-- Один отзыв: автор, оценка, текст, ответ производителя, кнопка жалобы --}}
<div class="review-item">
    <div class="review-item__head">
        <span class="avatar">{{ $review->user->initials() }}</span>
        <div class="flex-grow-1 min-w-0">
            <div class="fw-bold">{{ $review->user->name }}</div>
            <div class="small text-secondary">{{ $review->created_at->translatedFormat('j F Y') }}</div>
        </div>
        <x-rating :value="$review->rating" :show-value="false" />
    </div>

    @isset($showProduct)
        <a href="{{ route('products.show', $review->product->slug) }}" class="small fw-semibold d-inline-block mb-1">{{ $review->product->name }}</a>
    @endisset

    <p class="review-item__text">{{ $review->text }}</p>

    @if ($review->reply)
        <div class="review-reply">
            <div class="fw-bold small mb-1"><i class="bi bi-reply me-1"></i>Ответ производителя</div>
            {{ $review->reply }}
        </div>
    @endif

    @auth
        @can('report', $review)
            @if (in_array($review->id, $reportedIds ?? [], true))
                <div class="small text-secondary mt-2"><i class="bi bi-flag-fill me-1"></i>Вы пожаловались на этот отзыв</div>
            @else
                <button class="btn btn-link btn-sm text-secondary p-0 mt-2" type="button" data-bs-toggle="collapse" data-bs-target="#report{{ $review->id }}">
                    <i class="bi bi-flag"></i> Пожаловаться
                </button>
                <div class="collapse" id="report{{ $review->id }}">
                    <form method="POST" action="{{ route('reviews.report', $review) }}" class="d-flex flex-wrap gap-2 mt-2">
                        @csrf
                        <select name="reason" class="form-select form-select-sm w-auto" aria-label="Причина жалобы">
                            @foreach (\App\Enums\ReportReason::cases() as $reason)
                                <option value="{{ $reason->value }}">{{ $reason->label() }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="comment" class="form-control form-control-sm" style="max-width: 260px" placeholder="Комментарий (необязательно)" maxlength="500">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">Отправить</button>
                    </form>
                </div>
            @endif
        @endcan
    @endauth
</div>
