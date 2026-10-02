<?php

namespace App\Http\Controllers\Producer;

use App\Models\Review;
use App\Notifications\SiteNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Отзывы на товары производителя и ответы на них.
 */
class ReviewController extends BaseController
{
    public function index(Request $request): View
    {
        $producer = $this->producer($request);
        $filter = $request->query('filter');

        $reviews = $producer->reviews()
            ->with(['user', 'product'])
            ->when($filter === 'unanswered', fn ($q) => $q->whereNull('reply'))
            ->when($filter === 'negative', fn ($q) => $q->where('rating', '<=', 3))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('producer.reviews.index', compact('producer', 'reviews', 'filter'));
    }

    public function reply(Request $request, Review $review): RedirectResponse
    {
        $this->authorize('reply', $review);
        $data = $request->validate(['reply' => ['required', 'string', 'max:1000']], [], ['reply' => 'ответ']);

        $isNew = $review->reply === null;
        $review->update(['reply' => $data['reply'], 'replied_at' => now()]);

        if ($isNew) {
            $review->user->notify(new SiteNotification(
                'Ответ на ваш отзыв',
                'Производитель «'.$review->producer->name.'» ответил на отзыв о товаре «'.$review->product->name.'».',
                route('account.reviews'),
                'bi-reply'
            ));
        }

        return back()->with('success', 'Ответ сохранён.');
    }
}
