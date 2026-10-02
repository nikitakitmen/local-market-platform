<?php

namespace App\Http\Controllers\Producer;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Заказы производителя: просмотр, принятие, выполнение (самовывоз), отмена.
 */
class OrderController extends BaseController
{
    public function __construct(private OrderService $orders) {}

    public function index(Request $request): View
    {
        $producer = $this->producer($request);
        $status = OrderStatus::tryFrom((string) $request->query('status'));
        $search = trim((string) $request->query('q'));

        $orders = $producer->orders()
            ->with(['buyer', 'items', 'delivery'])
            ->status($status)
            ->when($search, function ($query) use ($search) {
                $query->where(fn ($q) => $q->number($search)
                    ->orWhereHas('buyer', fn ($b) => $b->where('name', 'like', '%'.addcslashes($search, '%_\\').'%')));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = $producer->orders()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('producer.orders.index', compact('orders', 'status', 'counts', 'search'));
    }

    public function show(Order $order): View
    {
        $this->authorize('manage', $order);

        $order->load(['buyer', 'items.product', 'delivery.courier', 'pickupLocation', 'promoCode']);

        return view('producer.orders.show', compact('order'));
    }

    public function accept(Order $order): RedirectResponse
    {
        $this->authorize('manage', $order);
        $this->orders->accept($order);

        $message = $order->isDelivery()
            ? 'Заказ принят. Он появится у курьеров в списке доступных доставок.'
            : 'Заказ принят. Подготовьте его к самовывозу.';

        return back()->with('success', $message);
    }

    /** Отметить выдачу заказа покупателю (для самовывоза). */
    public function complete(Order $order): RedirectResponse
    {
        $this->authorize('manage', $order);

        if (! $order->canBeCompletedByProducer()) {
            return back()->with('error', 'Заказ с доставкой будет выполнен автоматически, когда курьер его доставит.');
        }

        $this->orders->complete($order);

        return back()->with('success', 'Заказ '.$order->number.' выполнен.');
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('manage', $order);
        $data = $request->validate(['reason' => ['required', 'string', 'max:255']], [], ['reason' => 'причина отмены']);

        $this->orders->cancel($order, $request->user(), $data['reason']);

        return back()->with('success', 'Заказ '.$order->number.' отменён. Покупатель получил уведомление.');
    }
}
