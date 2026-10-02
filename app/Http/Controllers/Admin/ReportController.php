<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\ReviewReport;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Жалобы на отзывы. Решение «скрыть отзыв» или «отклонить жалобу».
 */
class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $status = ReportStatus::tryFrom((string) $request->query('status', ReportStatus::Pending->value));

        $reports = ReviewReport::query()
            ->with(['review.user', 'review.product', 'user', 'handler'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = ReviewReport::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.reports.index', compact('reports', 'status', 'counts'));
    }

    public function update(Request $request, ReviewReport $report, ReviewService $reviews): RedirectResponse
    {
        $data = $request->validate(['decision' => ['required', 'in:hide,dismiss']]);

        if ($data['decision'] === 'hide') {
            $reviews->setHidden($report->review, true);
        }

        // Все жалобы на этот отзыв закрываются одним решением
        ReviewReport::where('review_id', $report->review_id)
            ->where('status', ReportStatus::Pending)
            ->update([
                'status' => $data['decision'] === 'hide' ? ReportStatus::Resolved : ReportStatus::Dismissed,
                'handled_by' => $request->user()->id,
                'handled_at' => now(),
            ]);

        return back()->with('success', $data['decision'] === 'hide' ? 'Отзыв скрыт, жалоба закрыта.' : 'Жалоба отклонена.');
    }
}
