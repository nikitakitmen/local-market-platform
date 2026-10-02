<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\User;
use App\Services\DeliveryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Доставки: просмотр, ручное назначение и снятие курьера.
 */
class DeliveryController extends Controller
{
    public function __construct(private DeliveryService $deliveries) {}

    public function index(Request $request): View
    {
        $status = DeliveryStatus::tryFrom((string) $request->query('status'));

        $deliveries = Delivery::query()
            ->with(['order.buyer', 'order.producer.city', 'courier'])
            ->whereHas('order', fn ($q) => $q->where('status', '!=', OrderStatus::New))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when((int) $request->query('courier'), fn ($q, $id) => $q->where('courier_id', $id))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $couriers = User::where('role', UserRole::Courier)->where('is_active', true)->with('city')->orderBy('name')->get();
        $counts = Delivery::whereHas('order', fn ($q) => $q->where('status', '!=', OrderStatus::New))
            ->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.deliveries.index', compact('deliveries', 'status', 'couriers', 'counts'));
    }

    public function assign(Request $request, Delivery $delivery): RedirectResponse
    {
        $data = $request->validate([
            'courier_id' => ['required', Rule::exists('users', 'id')->where('role', UserRole::Courier->value)->where('is_active', true)],
        ], [], ['courier_id' => 'курьер']);

        if ($delivery->order->status !== OrderStatus::Accepted) {
            return back()->with('error', 'Назначить курьера можно только для принятого заказа.');
        }

        $courier = User::findOrFail($data['courier_id']);
        $this->deliveries->assign($delivery, $courier);

        return back()->with('success', 'Курьер '.$courier->name.' назначен на заказ '.$delivery->order->number.'.');
    }

    public function unassign(Delivery $delivery): RedirectResponse
    {
        $this->deliveries->unassign($delivery);

        return back()->with('success', 'Курьер снят, доставка снова доступна курьерам.');
    }
}
