<?php

namespace App\Http\Controllers\Courier;

use App\Enums\DeliveryStatus;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Services\DeliveryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Кабинет курьера: доступные доставки, мои доставки, завершённые.
 * Без GPS и карт: курьер видит адреса и меняет статус кнопками.
 */
class DeliveryController extends Controller
{
    private const RELATIONS = ['order.producer.locations', 'order.buyer', 'order.items'];

    public function __construct(private DeliveryService $deliveries) {}

    public function available(Request $request): View
    {
        $courier = $request->user();

        return view('courier.index', [
            'tab' => 'available',
            'deliveries' => Delivery::availableFor($courier)->with(self::RELATIONS)->oldest()->paginate(12),
            'counts' => $this->counts($request),
        ]);
    }

    public function my(Request $request): View
    {
        return view('courier.index', [
            'tab' => 'my',
            'deliveries' => $request->user()->deliveries()
                ->whereIn('status', [DeliveryStatus::Assigned, DeliveryStatus::InTransit])
                ->with(self::RELATIONS)
                ->orderBy('assigned_at')
                ->paginate(12),
            'counts' => $this->counts($request),
        ]);
    }

    public function completed(Request $request): View
    {
        return view('courier.index', [
            'tab' => 'completed',
            'deliveries' => $request->user()->deliveries()
                ->where('status', DeliveryStatus::Delivered)
                ->with(self::RELATIONS)
                ->latest('delivered_at')
                ->paginate(12),
            'counts' => $this->counts($request),
        ]);
    }

    /** «Взять заказ» — доставка закрепляется за курьером. */
    public function take(Request $request, Delivery $delivery): RedirectResponse
    {
        $this->deliveries->take($delivery, $request->user());

        return redirect()->route('courier.my')->with('success', 'Заказ '.$delivery->order->number.' закреплён за вами.');
    }

    /** Курьер забрал заказ у производителя. */
    public function pickup(Request $request, Delivery $delivery): RedirectResponse
    {
        $this->ensureOwnDelivery($request, $delivery);
        $this->deliveries->startTransit($delivery);

        return back()->with('success', 'Статус обновлён: заказ в пути.');
    }

    /** Заказ передан покупателю. */
    public function deliver(Request $request, Delivery $delivery): RedirectResponse
    {
        $this->ensureOwnDelivery($request, $delivery);
        $this->deliveries->markDelivered($delivery);

        return back()->with('success', 'Заказ '.$delivery->order->number.' доставлен. Спасибо!');
    }

    private function ensureOwnDelivery(Request $request, Delivery $delivery): void
    {
        abort_unless($delivery->courier_id === $request->user()->id, 403, 'Эта доставка закреплена за другим курьером.');
    }

    private function counts(Request $request): array
    {
        $courier = $request->user();

        return [
            'available' => Delivery::availableFor($courier)->count(),
            'my' => $courier->deliveries()->whereIn('status', [DeliveryStatus::Assigned, DeliveryStatus::InTransit])->count(),
            'completed' => $courier->deliveries()->where('status', DeliveryStatus::Delivered)->count(),
            'today' => $courier->deliveries()->where('status', DeliveryStatus::Delivered)->whereDate('delivered_at', today())->count(),
        ];
    }
}
