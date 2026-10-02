<?php

namespace App\Http\Controllers\Account;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Заказы покупателя.
 */
class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $status = OrderStatus::tryFrom((string) $request->query('status'));

        $orders = $user->orders()
            ->with(['producer', 'items', 'delivery'])
            ->status($status)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $counts = $user->orders()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('account.orders.index', compact('orders', 'status', 'counts'));
    }

    public function show(Request $request, Order $order): View
    {
        // В кабинете покупателя открываются только свои заказы
        abort_unless($order->user_id === $request->user()->id, 403);

        $order->load(['producer.city', 'items.product', 'delivery.courier', 'pickupLocation', 'promoCode']);

        $reviewedProductIds = Review::where('user_id', $request->user()->id)
            ->whereIn('product_id', $order->items->pluck('product_id')->filter())
            ->pluck('product_id')
            ->all();

        return view('account.orders.show', compact('order', 'reviewedProductIds'));
    }

    public function cancel(Request $request, Order $order, OrderService $orders): RedirectResponse
    {
        $this->authorize('cancel', $order);

        if (! $order->canBeCancelledByBuyer()) {
            return back()->with('error', 'Отменить можно только новый заказ. Напишите производителю, если планы изменились.');
        }

        $request->validate(['reason' => ['nullable', 'string', 'max:255']]);
        $orders->cancel($order, $request->user(), $request->input('reason') ?: 'Отменён покупателем');

        return back()->with('success', 'Заказ '.$order->number.' отменён.');
    }
}
