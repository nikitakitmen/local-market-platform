<?php

namespace Tests\Feature;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\ProducerStatus;
use App\Enums\UserRole;
use App\Models\Address;
use App\Models\City;
use App\Models\Order;
use App\Models\Producer;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\ProducerApplicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_approves_producer_application_and_user_becomes_producer(): void
    {
        $applicant = User::factory()->create();
        $city = City::factory()->create();

        $application = app(ProducerApplicationService::class)->submit($applicant, [
            'name' => 'Пасека «Тест»',
            'type' => 'individual',
            'city_id' => $city->id,
            'address' => 'с. Медовое, 1',
            'phone' => '+7 (900) 000-00-00',
            'email' => 'honey@example.com',
        ]);

        $this->assertSame(ProducerStatus::Pending, $application->producer->status);
        $this->actingAs($applicant)->get('/producer')->assertForbidden();

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->post(route('admin.applications.approve', $application))->assertRedirect();

        $this->assertSame(UserRole::Producer, $applicant->fresh()->role);
        $this->assertSame(ProducerStatus::Approved, $application->producer->fresh()->status);
        $this->assertSame(1, $applicant->notifications()->count());
        $this->actingAs($applicant->fresh())->get('/producer')->assertOk();
    }

    public function test_admin_can_reject_application_with_comment(): void
    {
        $applicant = User::factory()->create();
        $producer = Producer::factory()->pending()->create(['user_id' => $applicant->id]);
        $application = $producer->applications()->create(['user_id' => $applicant->id, 'status' => ProducerStatus::Pending]);

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->post(route('admin.applications.reject', $application), [])->assertSessionHasErrors('admin_comment');
        $this->actingAs($admin)->post(route('admin.applications.reject', $application), ['admin_comment' => 'Нет описания продукции'])->assertRedirect();

        $this->assertSame(ProducerStatus::Rejected, $application->fresh()->status);
        $this->assertSame(UserRole::Buyer, $applicant->fresh()->role);
    }

    public function test_blocked_producer_products_disappear_from_catalog(): void
    {
        $product = Product::factory()->create(['name' => 'Уникальный сыр']);
        $admin = User::factory()->admin()->create();

        $this->get(route('catalog'))->assertSee('Уникальный сыр');

        $this->actingAs($admin)->patch(route('admin.producers.status', $product->producer), ['status' => 'rejected'])->assertRedirect();

        $this->get(route('catalog'))->assertDontSee('Уникальный сыр');
    }

    public function test_admin_updates_site_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'site_name' => 'Фермерский рынок',
            'support_email' => 'help@example.com',
            'support_phone' => '+7 (800) 000-00-00',
            'platform_commission_percent' => 12,
            'min_order_amount' => 700,
            'delivery_base_cost' => 200,
            'delivery_cost_per_km' => 20,
            'delivery_free_from' => 5000,
        ])->assertSessionHasNoErrors();

        $this->assertEquals(12, Setting::number('platform_commission_percent'));
        $this->assertEquals(700, Setting::number('min_order_amount'));
    }

    public function test_operator_can_complete_problem_order_and_delivery_is_closed(): void
    {
        $city = City::factory()->create();
        $product = Product::factory()->create([
            'producer_id' => Producer::factory()->create(['city_id' => $city->id])->id,
            'price' => 800,
        ]);
        $buyer = User::factory()->create();
        Address::factory()->create(['user_id' => $buyer->id, 'city_id' => $city->id]);

        $this->actingAs($buyer)->postJson(route('cart.store'), ['product_id' => $product->id]);
        $this->actingAs($buyer)->post(route('checkout.store'), [
            'delivery_method' => 'delivery',
            'address_id' => $buyer->addresses()->value('id'),
            'payment_method' => 'cash',
            'recipient_name' => $buyer->name,
            'recipient_phone' => '+7 (900) 111-22-33',
        ]);
        $order = Order::firstOrFail();

        $operator = User::factory()->operator()->create();
        $this->actingAs($operator)->patch(route('admin.orders.status', $order), ['action' => 'accept'])->assertRedirect();
        $this->actingAs($operator)->patch(route('admin.orders.status', $order), ['action' => 'complete'])->assertRedirect();

        $order->refresh();
        $this->assertSame(OrderStatus::Completed, $order->status);
        $this->assertSame(DeliveryStatus::Delivered, $order->delivery->status);
    }
}
