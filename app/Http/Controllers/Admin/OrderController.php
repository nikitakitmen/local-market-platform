<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Producer;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Заказы платформы. Оператор помогает обрабатывать проблемные заказы:
 * может принять, завершить или отменить заказ от имени платформы.
 */
class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $status = OrderStatus::tryFrom((string) $request->query('status'));
        $search = trim((string) $request->query('q'));
        $problem = $request->boolean('problem');

        $orders = Order::query()
            ->with(['buyer', 'producer', 'delivery'])
            ->status($status)
            ->when($problem, fn ($q) => $q->problem())
            ->when((int) $request->query('producer'), fn ($q, $id) => $q->where('producer_id', $id))
            ->when($search, function ($query) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $query->where(fn ($q) => $q->number($search)
                    ->orWhereHas('buyer', fn ($b) => $b->where('name', 'like', $like)->orWhere('email', 'like', $like)));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'status' => $status,
            'search' => $search,
            'problem' => $problem,
            'problemCount' => Order::problem()->count(),
            'producers' => Producer::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['buyer', 'producer.user', 'items.product', 'delivery.courier', 'pickupLocation', 'promoCode']);

        return view('admin.orders.show', compact('order'));
    }

    /** Смена статуса заказа сотрудником: accept | complete | cancel. */
    public function updateStatus(Request $request, Order $order, OrderService $orders): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:accept,complete,cancel'],
            'reason' => ['required_if:action,cancel', 'nullable', 'string', 'max:255'],
        ], [], ['reason' => 'причина отмены']);

        match ($data['action']) {
            'accept' => $orders->accept($order),
            'complete' => $orders->complete($order),
            'cancel' => $orders->cancel($order, $request->user(), 'Отменён оператором: '.$data['reason']),
        };

        return back()->with('success', 'Статус заказа '.$order->number.' обновлён: '.mb_strtolower($order->fresh()->status->label()).'.');
    }
}
