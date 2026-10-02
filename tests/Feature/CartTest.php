<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_add_product_to_cart_via_ajax(): void
    {
        $buyer = User::factory()->create();
        $product = Product::factory()->create(['price' => 250]);

        $this->actingAs($buyer)
            ->postJson(route('cart.store'), ['product_id' => $product->id, 'quantity' => 2])
            ->assertOk()
            ->assertJson(['count' => 2]);

        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'quantity' => 2]);

        // Повторное добавление увеличивает количество, а не создаёт новую позицию
        $this->actingAs($buyer)->postJson(route('cart.store'), ['product_id' => $product->id])->assertJson(['count' => 3]);
        $this->assertSame(1, CartItem::count());
    }

    public function test_selected_variant_is_added_to_cart(): void
    {
        $buyer = User::factory()->create();
        $product = Product::factory()->create();
        $small = $product->variants()->create(['name' => '250 г', 'price' => 390, 'in_stock' => true]);
        $big = $product->variants()->create(['name' => '500 г', 'price' => 750, 'in_stock' => true]);

        $this->actingAs($buyer)
            ->postJson(route('cart.store'), ['product_id' => $product->id, 'variant_id' => $big->id])
            ->assertOk();

        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'product_variant_id' => $big->id]);
        $this->assertDatabaseMissing('cart_items', ['product_variant_id' => $small->id]);
    }

    public function test_out_of_stock_or_hidden_product_can_not_be_added(): void
    {
        $buyer = User::factory()->create();

        $outOfStock = Product::factory()->outOfStock()->create();
        $hidden = Product::factory()->create(['is_active' => false]);

        $this->actingAs($buyer)->postJson(route('cart.store'), ['product_id' => $outOfStock->id])->assertUnprocessable();
        $this->actingAs($buyer)->postJson(route('cart.store'), ['product_id' => $hidden->id])->assertUnprocessable();
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_guest_must_login_to_use_cart(): void
    {
        $product = Product::factory()->create();

        $this->post(route('cart.store'), ['product_id' => $product->id])->assertRedirect(route('login'));
        $this->postJson(route('cart.store'), ['product_id' => $product->id])->assertUnauthorized();
    }

    public function test_buyer_can_update_quantity_and_remove_item(): void
    {
        $buyer = User::factory()->create();
        $product = Product::factory()->create();
        $this->actingAs($buyer)->postJson(route('cart.store'), ['product_id' => $product->id]);
        $item = CartItem::firstOrFail();

        $this->actingAs($buyer)
            ->patchJson(route('cart.update', $item), ['quantity' => 5])
            ->assertOk()
            ->assertJson(['count' => 5]);

        $this->actingAs($buyer)->deleteJson(route('cart.destroy', $item))->assertOk()->assertJson(['count' => 0]);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_buyer_can_not_change_cart_of_another_user(): void
    {
        $owner = User::factory()->create();
        $product = Product::factory()->create();
        $this->actingAs($owner)->postJson(route('cart.store'), ['product_id' => $product->id]);
        $item = CartItem::firstOrFail();

        $intruder = User::factory()->create();
        $this->actingAs($intruder)->patchJson(route('cart.update', $item), ['quantity' => 50])->assertForbidden();
        $this->actingAs($intruder)->deleteJson(route('cart.destroy', $item))->assertForbidden();
        $this->assertSame(1, $item->fresh()->quantity);
    }
}
