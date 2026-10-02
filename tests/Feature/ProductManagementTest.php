<?php

namespace Tests\Feature;

use App\Enums\ProducerStatus;
use App\Models\Category;
use App\Models\Producer;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_producer_can_create_product_with_image_and_variants(): void
    {
        Storage::fake('public');

        $producer = Producer::factory()->create();
        $category = Category::factory()->create();
        $subcategory = Category::factory()->childOf($category)->create();

        $response = $this->actingAs($producer->user)->post(route('producer.products.store'), [
            'name' => 'Сыр «Тестовый»',
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'description' => 'Выдержанный сыр из коровьего молока.',
            'unit' => 'кусок',
            'in_stock' => '1',
            'is_active' => '1',
            'image' => UploadedFile::fake()->image('cheese.jpg', 800, 800),
            'variants' => [
                ['name' => '250 г', 'price' => 390, 'in_stock' => '1'],
                ['name' => '500 г', 'price' => 750, 'in_stock' => '1'],
            ],
        ]);

        $response->assertRedirect(route('producer.products.index'));

        $product = Product::where('name', 'Сыр «Тестовый»')->firstOrFail();
        $this->assertSame($producer->id, $product->producer_id);
        $this->assertCount(2, $product->variants);
        // Цена товара — минимальная цена варианта
        $this->assertEquals(390, (float) $product->price);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_product_form_is_validated(): void
    {
        Storage::fake('public');
        $producer = Producer::factory()->create();

        $this->actingAs($producer->user)->post(route('producer.products.store'), [
            'name' => '',
            'category_id' => 999,
            'image' => UploadedFile::fake()->create('virus.pdf', 100, 'application/pdf'),
        ])->assertSessionHasErrors(['name', 'category_id', 'price', 'image']);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_blocked_producer_can_not_publish_products(): void
    {
        $producer = Producer::factory()->create(['status' => ProducerStatus::Rejected]);
        $category = Category::factory()->create();

        $this->actingAs($producer->user)->post(route('producer.products.store'), [
            'name' => 'Товар',
            'category_id' => $category->id,
            'price' => 100,
        ])->assertForbidden();
    }

    public function test_producer_can_not_manage_products_of_another_producer(): void
    {
        $producer = Producer::factory()->create();
        $foreignProduct = Product::factory()->create();

        $this->actingAs($producer->user)->get(route('producer.products.edit', $foreignProduct))->assertForbidden();
        $this->actingAs($producer->user)->delete(route('producer.products.destroy', $foreignProduct))->assertForbidden();
        $this->assertNotSoftDeleted($foreignProduct);
    }

    public function test_producer_can_hide_product_and_toggle_stock(): void
    {
        $product = Product::factory()->create();
        $user = $product->producer->user;

        $this->actingAs($user)->patch(route('producer.products.toggle-active', $product))->assertRedirect();
        $this->actingAs($user)->patch(route('producer.products.toggle-stock', $product))->assertRedirect();

        $product->refresh();
        $this->assertFalse($product->is_active);
        $this->assertFalse($product->in_stock);

        // Скрытый товар не виден в каталоге
        $this->get(route('catalog'))->assertDontSee($product->name);
    }
}
