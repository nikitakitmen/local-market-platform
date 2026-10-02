<?php

namespace Tests\Feature;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\City;
use App\Models\Order;
use App\Models\Producer;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Полный цикл: покупатель → производитель → курьер → отзыв.
 */
class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;

    private Producer $producer;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $city = City::factory()->create();
        $this->producer = Producer::factory()->create(['city_id' => $city->id]);
        $this->product = Product::factory()->create(['producer_id' => $this->producer->id, 'price' => 600]);
        $this->buyer = User::factory()->create();
        Address::factory()->create(['user_id' => $this->buyer->id, 'city_id' => $city->id]);
    }

    private function placeOrder(): Order
    {
        $this->actingAs($this->buyer)->postJson(route('cart.store'), ['product_id' => $this->product->id]);
        $this->actingAs($this->buyer)->post(route('checkout.store'), [
            'delivery_method' => 'delivery',
            'address_id' => $this->buyer->addresses()->value('id'),
            'payment_method' => 'cash',
            'recipient_name' => $this->buyer->name,
            'recipient_phone' => '+7 (900) 111-22-33',
        ])->assertRedirect();

        return Order::latest('id')->firstOrFail();
    }

    public function test_order_goes_from_producer_to_courier_and_buyer_can_review(): void
    {
        $order = $this->placeOrder();
        $courier = User::factory()->courier()->create(['city_id' => $this->producer->city_id]);

        // Пока производитель не принял заказ, курьеры его не видят
        $this->actingAs($courier)->get(route('courier.available'))->assertDontSee($order->number);

        // Производитель принимает заказ
        $this->actingAs($this->producer->user)->post(route('producer.orders.accept', $order))->assertRedirect();
        $this->assertSame(OrderStatus::Accepted, $order->fresh()->status);
        $this->assertTrue($this->buyer->notifications->contains(fn ($n) => str_contains($n->data['title'], 'принят')));

        // Курьер видит заказ и берёт его
        $this->actingAs($courier)->get(route('courier.available'))->assertSee($order->number);
        $this->actingAs($courier)->post(route('courier.take', $order->delivery))->assertRedirect(route('courier.my'));
        $this->assertSame($courier->id, $order->delivery->fresh()->courier_id);

        // Второй курьер уже не может взять этот заказ
        $otherCourier = User::factory()->courier()->create();
        $this->actingAs($otherCourier)->post(route('courier.take', $order->delivery))->assertSessionHasErrors('delivery');

        // Забрал → в пути → доставлено
        $this->actingAs($courier)->post(route('courier.pickup', $order->delivery))->assertRedirect();
        $this->assertSame(DeliveryStatus::InTransit, $order->delivery->fresh()->status);
        $this->actingAs($courier)->post(route('courier.deliver', $order->delivery))->assertRedirect();

        $order->refresh();
        $this->assertSame(OrderStatus::Completed, $order->status);
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertSame(1, $this->product->fresh()->sales_count);

        // После выполненного заказа покупатель может оставить отзыв
        $this->actingAs($this->buyer)->post(route('reviews.store', $this->product), [
            'rating' => 5,
            'text' => 'Отличное молоко, очень вкусное!',
        ])->assertRedirect();

        $this->assertDatabaseHas('reviews', ['product_id' => $this->product->id, 'rating' => 5, 'order_id' => $order->id]);
        $this->assertEquals(5.0, $this->product->fresh()->rating);
        $this->assertEquals(5.0, $this->producer->fresh()->rating);
    }

    public function test_review_is_not_allowed_before_order_is_completed(): void
    {
        $this->placeOrder();

        $this->actingAs($this->buyer)->post(route('reviews.store', $this->product), [
            'rating' => 4,
            'text' => 'Пытаюсь оставить отзыв заранее',
        ])->assertSessionHasErrors('text');

        $this->assertSame(0, Review::count());
    }

    public function test_buyer_can_cancel_only_new_order(): void
    {
        $order = $this->placeOrder();

        $this->actingAs($this->buyer)->post(route('account.orders.cancel', $order))->assertRedirect();
        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        // У отменённого заказа нет доставки
        $this->assertNull($order->fresh()->delivery);

        $second = $this->placeOrder();
        $this->actingAs($this->producer->user)->post(route('producer.orders.accept', $second));
        $this->actingAs($this->buyer)->post(route('account.orders.cancel', $second));
        $this->assertSame(OrderStatus::Accepted, $second->fresh()->status);
    }

    public function test_other_users_can_not_see_or_manage_order(): void
    {
        $order = $this->placeOrder();
        $stranger = User::factory()->create();
        $otherProducer = Producer::factory()->create();

        $this->actingAs($stranger)->get(route('account.orders.show', $order))->assertForbidden();
        $this->actingAs($otherProducer->user)->get(route('producer.orders.show', $order))->assertForbidden();
        $this->actingAs($otherProducer->user)->post(route('producer.orders.accept', $order))->assertForbidden();
    }
}
