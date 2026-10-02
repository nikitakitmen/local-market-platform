<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProducerStatus;
use App\Http\Controllers\Controller;
use App\Models\ProducerApplication;
use App\Services\ProducerApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Заявки на регистрацию производителей.
 */
class ApplicationController extends Controller
{
    public function __construct(private ProducerApplicationService $service) {}

    public function index(Request $request): View
    {
        $status = ProducerStatus::tryFrom((string) $request->query('status', ProducerStatus::Pending->value));

        $applications = ProducerApplication::query()
            ->with(['producer.city', 'user', 'reviewer'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = ProducerApplication::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.applications.index', compact('applications', 'status', 'counts'));
    }

    public function show(ProducerApplication $application): View
    {
        $application->load(['producer.city', 'user', 'reviewer']);

        return view('admin.applications.show', compact('application'));
    }

    public function approve(Request $request, ProducerApplication $application): RedirectResponse
    {
        if (! $application->isPending()) {
            return back()->with('error', 'Заявка уже рассмотрена.');
        }

        $this->service->approve($application, $request->user());

        return redirect()->route('admin.applications.index')
            ->with('success', 'Заявка «'.$application->producer->name.'» одобрена. Пользователь получил роль производителя.');
    }

    public function reject(Request $request, ProducerApplication $application): RedirectResponse
    {
        if (! $application->isPending()) {
            return back()->with('error', 'Заявка уже рассмотрена.');
        }

        $data = $request->validate(['admin_comment' => ['required', 'string', 'max:1000']], [], ['admin_comment' => 'комментарий']);
        $this->service->reject($application, $request->user(), $data['admin_comment']);

        return redirect()->route('admin.applications.index')->with('success', 'Заявка «'.$application->producer->name.'» отклонена.');
    }
}
