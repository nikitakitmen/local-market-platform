<?php

namespace Tests\Feature;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PromoCodeType;
use App\Models\Address;
use App\Models\City;
use App\Models\Order;
use App\Models\Producer;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Оформление заказа: корзина с товарами разных производителей делится на отдельные заказы.
 */
class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private City $city;

    private User $buyer;

    private Address $address;

    private Product $milk;

    private Product $bread;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::setMany([
            'min_order_amount' => 500,
            'delivery_base_cost' => 150,
            'delivery_cost_per_km' => 15,
            'delivery_free_from' => 3000,
            'platform_commission_percent' => 10,
        ]);

        $this->city = City::factory()->create();
        $farm = Producer::factory()->create(['city_id' => $this->city->id]);
        $bakery = Producer::factory()->create(['city_id' => $this->city->id]);
        $farm->locations()->create(['name' => 'Лавка', 'address' => 'ул. Рыночная, 1', 'is_pickup_point' => true]);
        $bakery->locations()->create(['name' => 'Пекарня', 'address' => 'ул. Хлебная, 2', 'is_pickup_point' => true]);

        $this->milk = Product::factory()->create(['producer_id' => $farm->id, 'price' => 300]);
        $this->bread = Product::factory()->create(['producer_id' => $bakery->id, 'price' => 700]);

        $this->buyer = User::factory()->create();
        $this->address = Address::factory()->create([
            'user_id' => $this->buyer->id,
            'city_id' => $this->city->id,
            'distance_km' => 3,
        ]);
    }

    private function fillCart(int $milkQuantity = 2): void
    {
        $this->actingAs($this->buyer)->postJson(route('cart.store'), ['product_id' => $this->milk->id, 'quantity' => $milkQuantity]);
        $this->actingAs($this->buyer)->postJson(route('cart.store'), ['product_id' => $this->bread->id, 'quantity' => 1]);
    }

    private function checkoutData(array $overrides = []): array
    {
        return array_merge([
            'delivery_method' => 'delivery',
            'address_id' => $this->address->id,
            'payment_method' => 'cash',
            'recipient_name' => 'Анна',
            'recipient_phone' => '+7 (900) 111-22-33',
            'comment' => 'Позвоните заранее',
        ], $overrides);
    }

    public function test_cart_with_two_producers_creates_two_orders(): void
    {
        $this->fillCart();

        $this->actingAs($this->buyer)->post(route('checkout.store'), $this->checkoutData())->assertRedirect();

        $orders = Order::with(['items', 'delivery'])->orderBy('id')->get();
        $this->assertCount(2, $orders);
        $this->assertSame(1, $orders->pluck('checkout_id')->unique()->count());

        [$farmOrder, $bakeryOrder] = $orders;
        $this->assertSame($this->milk->producer_id, $farmOrder->producer_id);
        $this->assertSame(OrderStatus::New, $farmOrder->status);
        $this->assertEquals(600, (float) $farmOrder->subtotal);
        // Доставка: 150 + 15 ₽ × 3 км = 195 ₽
        $this->assertEquals(195, (float) $farmOrder->delivery_cost);
        $this->assertEquals(795, (float) $farmOrder->total);
        // Комиссия платформы 10% от стоимости товаров
        $this->assertEquals(60, (float) $farmOrder->commission_amount);
        $this->assertSame(DeliveryStatus::Waiting, $farmOrder->delivery->status);

        $this->assertEquals(700, (float) $bakeryOrder->subtotal);
        $this->assertCount(1, $bakeryOrder->items);

        // Корзина очищена, продавцы получили уведомления
        $this->assertDatabaseCount('cart_items', 0);
        $this->assertSame(1, $this->milk->producer->user->notifications()->count());
    }

    public function test_delivery_is_free_from_threshold(): void
    {
        $this->actingAs($this->buyer)->postJson(route('cart.store'), ['product_id' => $this->milk->id, 'quantity' => 10]);

        $this->actingAs($this->buyer)->post(route('checkout.store'), $this->checkoutData())->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertEquals(3000, (float) $order->subtotal);
        $this->assertEquals(0, (float) $order->delivery_cost);
    }

    public function test_minimum_order_amount_is_checked_for_each_producer(): void
    {
        $this->fillCart(milkQuantity: 1); // у фермы 300 ₽ < 500 ₽

        $this->actingAs($this->buyer)
            ->post(route('checkout.store'), $this->checkoutData())
            ->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_pickup_order_uses_producer_location_and_has_no_delivery(): void
    {
        $this->fillCart();
        $farmLocation = $this->milk->producer->locations()->first();
        $bakeryLocation = $this->bread->producer->locations()->first();

        $this->actingAs($this->buyer)->post(route('checkout.store'), $this->checkoutData([
            'delivery_method' => 'pickup',
            'address_id' => null,
            'pickup_locations' => [
                $this->milk->producer_id => $farmLocation->id,
                $this->bread->producer_id => $bakeryLocation->id,
            ],
        ]))->assertRedirect();

        $order = Order::where('producer_id', $this->milk->producer_id)->firstOrFail();
        $this->assertSame($farmLocation->id, $order->pickup_location_id);
        $this->assertEquals(0, (float) $order->delivery_cost);
        $this->assertDatabaseCount('deliveries', 0);
    }

    public function test_promo_code_discount_is_split_between_orders(): void
    {
        PromoCode::create(['code' => 'WELCOME10', 'type' => PromoCodeType::Percent, 'value' => 10, 'is_active' => true]);
        $this->fillCart();

        $this->actingAs($this->buyer)->post(route('cart.promo.apply'), ['code' => 'welcome10'])->assertSessionHasNoErrors();
        $this->actingAs($this->buyer)->post(route('checkout.store'), $this->checkoutData())->assertRedirect();

        // Скидка 10% от 1300 ₽ = 130 ₽ делится пропорционально: 60 ₽ и 70 ₽
        $this->assertEquals(60, (float) Order::where('producer_id', $this->milk->producer_id)->value('discount'));
        $this->assertEquals(70, (float) Order::where('producer_id', $this->bread->producer_id)->value('discount'));
        $this->assertSame(1, PromoCode::first()->used_count);
    }

    public function test_expired_promo_code_is_rejected(): void
    {
        PromoCode::create([
            'code' => 'OLD', 'type' => PromoCodeType::Fixed, 'value' => 100, 'is_active' => true,
            'start_date' => now()->subMonths(2), 'end_date' => now()->subMonth(),
        ]);
        $this->fillCart();

        $this->actingAs($this->buyer)->post(route('cart.promo.apply'), ['code' => 'OLD'])->assertSessionHasErrors('code');
    }

    public function test_card_payment_goes_through_demo_payment_page(): void
    {
        $this->fillCart();

        $response = $this->actingAs($this->buyer)->post(route('checkout.store'), $this->checkoutData(['payment_method' => 'card']));
        $checkoutId = Order::value('checkout_id');
        $response->assertRedirect(route('payment.show', $checkoutId));

        $this->actingAs($this->buyer)->get(route('payment.show', $checkoutId))->assertOk()->assertSee('Демонстрационный режим');
        $this->actingAs($this->buyer)->post(route('payment.pay', $checkoutId))->assertRedirect(route('checkout.success', $checkoutId));

        $this->assertSame(2, Order::where('payment_status', PaymentStatus::Paid)->count());
    }
}
