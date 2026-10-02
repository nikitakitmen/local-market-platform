<?php

namespace App\Http\Controllers;

use App\Enums\DeliveryMethod;
use App\Enums\PaymentMethod;
use App\Http\Requests\CheckoutRequest;
use App\Models\Address;
use App\Models\City;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cart,
        private CheckoutService $checkout,
    ) {}

    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $summary = $this->cart->summary($user);

        if ($summary['groups']->isEmpty()) {
            return redirect()->route('cart.index')->with('info', 'Корзина пуста — добавьте товары, чтобы оформить заказ.');
        }

        if (! $summary['can_checkout']) {
            return redirect()->route('cart.index')->with('error', 'Проверьте корзину: есть недоступные товары или не набрана минимальная сумма заказа.');
        }

        return view('checkout.create', [
            'summary' => $summary,
            'user' => $user,
            'addresses' => $user->addresses()->with('city')->orderByDesc('is_default')->latest()->get(),
            'cities' => City::active()->get(),
            'delivery' => [
                'base' => Setting::number('delivery_base_cost'),
                'per_km' => Setting::number('delivery_cost_per_km'),
                'free_from' => Setting::number('delivery_free_from'),
            ],
        ]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $address = null;

        if ($data['delivery_method'] === DeliveryMethod::Delivery->value) {
            if ($data['address_id'] === 'new') {
                // Новый адрес: сохраняем в профиль только после успешного оформления
                $address = new Address($data['new_address']);
                $address->user_id = $user->id;
            } else {
                $address = $user->addresses()->with('city')->find((int) $data['address_id']);

                if (! $address) {
                    throw ValidationException::withMessages(['address_id' => 'Выберите адрес доставки.']);
                }
            }
        }

        $orders = $this->checkout->placeOrder($user, $data, $address);

        if ($address && ! $address->exists && $request->boolean('save_address')) {
            $address->is_default = ! $user->addresses()->exists();
            $address->save();
        }

        $checkoutId = $orders->first()->checkout_id;

        // Карта и СБП — демонстрационная страница оплаты
        if (PaymentMethod::from($data['payment_method'])->isOnline()) {
            return redirect()->route('payment.show', $checkoutId);
        }

        return redirect()->route('checkout.success', $checkoutId);
    }

    public function success(Request $request, string $checkoutId): View
    {
        $orders = $request->user()->orders()
            ->where('checkout_id', $checkoutId)
            ->with(['producer', 'items', 'pickupLocation'])
            ->get();

        abort_if($orders->isEmpty(), 404);

        return view('checkout.success', ['orders' => $orders]);
    }
}
