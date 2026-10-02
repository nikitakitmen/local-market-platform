<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProducerStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Producer;
use App\Notifications\SiteNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Производители. Оператор может только смотреть, администратор — менять статус проверки.
 */
class ProducerController extends Controller
{
    public function index(Request $request): View
    {
        $status = ProducerStatus::tryFrom((string) $request->query('status'));
        $search = trim((string) $request->query('q'));

        $producers = Producer::query()
            ->with(['city', 'user'])
            ->withCount(['products', 'orders'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search, fn ($q) => $q->where('name', 'like', '%'.addcslashes($search, '%_\\').'%'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.producers.index', compact('producers', 'status', 'search'));
    }

    public function show(Producer $producer): View
    {
        $producer->load(['city', 'user', 'locations', 'applications.reviewer']);

        return view('admin.producers.show', [
            'producer' => $producer,
            'stats' => [
                'products' => $producer->products()->count(),
                'orders' => $producer->orders()->count(),
                'completed' => $producer->orders()->completed()->count(),
                'turnover' => (float) $producer->orders()->completed()->sum('total'),
                'commission' => (float) $producer->orders()->completed()->sum('commission_amount'),
            ],
            'recentOrders' => $producer->orders()->with('buyer')->latest()->limit(5)->get(),
        ]);
    }

    /** Подтвердить или заблокировать (отклонить) производителя. */
    public function updateStatus(Request $request, Producer $producer): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(ProducerStatus::class)->only([ProducerStatus::Approved, ProducerStatus::Rejected])],
        ]);

        $status = ProducerStatus::from($data['status']);
        $producer->update([
            'status' => $status,
            'approved_at' => $status === ProducerStatus::Approved ? ($producer->approved_at ?? now()) : $producer->approved_at,
        ]);

        if ($status === ProducerStatus::Approved) {
            // Подтверждение из этого раздела равносильно одобрению заявки
            if ($producer->user->role === UserRole::Buyer) {
                $producer->user->update(['role' => UserRole::Producer]);
            }

            $producer->applications()->where('status', ProducerStatus::Pending)->update([
                'status' => ProducerStatus::Approved,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);
        }

        $producer->user->notify(new SiteNotification(
            $status === ProducerStatus::Approved ? 'Профиль производителя подтверждён' : 'Профиль производителя заблокирован',
            $status === ProducerStatus::Approved
                ? 'Ваши товары снова доступны покупателям.'
                : 'Ваши товары скрыты из каталога. Свяжитесь с поддержкой платформы.',
            route('producer.dashboard'),
            $status === ProducerStatus::Approved ? 'bi-patch-check' : 'bi-slash-circle'
        ));

        return back()->with('success', 'Статус производителя «'.$producer->name.'»: '.mb_strtolower($status->label()).'.');
    }
}
