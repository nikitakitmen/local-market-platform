<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Демонстрационная оплата картой или по СБП.
 * Реальные платёжные системы не подключены, данные карт не передаются на сервер и не хранятся.
 */
class PaymentController extends Controller
{
    public function show(Request $request, string $checkoutId): View|RedirectResponse
    {
        $orders = $this->unpaidOrders($request, $checkoutId);

        if ($orders->isEmpty()) {
            return redirect()->route('checkout.success', $checkoutId)->with('info', 'Эти заказы не требуют онлайн-оплаты.');
        }

        return view('payment.show', [
            'orders' => $orders,
            'checkoutId' => $checkoutId,
            'method' => $orders->first()->payment_method,
            'total' => $orders->sum('total'),
        ]);
    }

    public function pay(Request $request, string $checkoutId): RedirectResponse
    {
        $orders = $this->unpaidOrders($request, $checkoutId);

        foreach ($orders as $order) {
            $order->update(['payment_status' => PaymentStatus::Paid, 'paid_at' => now()]);
        }

        return redirect()->route('checkout.success', $checkoutId)
            ->with('success', 'Оплата прошла успешно (демонстрационный режим).');
    }

    private function unpaidOrders(Request $request, string $checkoutId): Collection
    {
        $orders = $request->user()->orders()->where('checkout_id', $checkoutId)->with('producer')->get();

        abort_if($orders->isEmpty(), 404);

        return $orders->filter->awaitsOnlinePayment()->values();
    }
}
