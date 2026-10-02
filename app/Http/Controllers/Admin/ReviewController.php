<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Модерация отзывов: скрыть / показать (оператор и администратор), удалить (администратор).
 */
class ReviewController extends Controller
{
    public function __construct(private ReviewService $reviews) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));

        $reviews = Review::query()
            ->with(['user', 'product', 'producer'])
            ->withCount('reports')
            ->when($request->query('visibility') === 'hidden', fn ($q) => $q->where('is_hidden', true))
            ->when((int) $request->query('rating'), fn ($q, $rating) => $q->where('rating', $rating))
            ->when($search, fn ($q) => $q->where('text', 'like', '%'.addcslashes($search, '%_\\').'%'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reviews.index', compact('reviews', 'search'));
    }

    public function toggleVisibility(Review $review): RedirectResponse
    {
        $this->reviews->setHidden($review, ! $review->is_hidden);

        return back()->with('success', $review->is_hidden ? 'Отзыв скрыт.' : 'Отзыв снова опубликован.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $this->reviews->delete($review);

        return back()->with('success', 'Отзыв удалён.');
    }
}
