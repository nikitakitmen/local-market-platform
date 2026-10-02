<?php

namespace App\Http\Controllers;

use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Http\Requests\ReviewReportRequest;
use App\Http\Requests\ReviewRequest;
use App\Models\Product;
use App\Models\Review;
use App\Models\ReviewReport;
use App\Models\User;
use App\Notifications\SiteNotification;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(private ReviewService $reviews) {}

    public function store(ReviewRequest $request, Product $product): RedirectResponse
    {
        $this->reviews->create($request->user(), $product, $request->integer('rating'), $request->input('text'));

        return redirect()->to(route('products.show', $product->slug).'#reviews')
            ->with('success', 'Спасибо! Ваш отзыв опубликован.');
    }

    /** Жалоба на отзыв — попадает к оператору и администратору. */
    public function report(ReviewReportRequest $request, Review $review): RedirectResponse
    {
        $user = $request->user();

        if (ReviewReport::where('review_id', $review->id)->where('user_id', $user->id)->exists()) {
            return back()->with('info', 'Вы уже отправляли жалобу на этот отзыв.');
        }

        ReviewReport::create([
            'review_id' => $review->id,
            'user_id' => $user->id,
            'reason' => $request->input('reason'),
            'comment' => $request->input('comment'),
            'status' => ReportStatus::Pending,
        ]);

        User::whereIn('role', [UserRole::Admin, UserRole::Operator])->where('is_active', true)->get()
            ->each(fn (User $staff) => $staff->notify(new SiteNotification(
                'Новая жалоба на отзыв',
                $user->name.' пожаловался на отзыв к товару «'.$review->product->name.'».',
                route('admin.reports.index'),
                'bi-flag'
            )));

        return back()->with('success', 'Жалоба отправлена. Модератор проверит отзыв.');
    }

    public function destroy(Request $request, Review $review): RedirectResponse
    {
        $this->authorize('delete', $review);
        $this->reviews->delete($review);

        return back()->with('success', 'Отзыв удалён.');
    }
}
