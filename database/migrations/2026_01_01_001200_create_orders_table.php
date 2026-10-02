<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Заказы. Корзина с товарами нескольких производителей при оформлении
 * разделяется на несколько заказов с общим checkout_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('checkout_id')->index();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('producer_id')->constrained()->restrictOnDelete();
            $table->foreignId('promo_code_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pickup_location_id')->nullable()->constrained('producer_locations')->nullOnDelete();
            // new | accepted | completed | cancelled (App\Enums\OrderStatus)
            $table->string('status', 20)->default('new')->index();
            $table->string('delivery_method', 20);
            $table->string('payment_method', 20);
            $table->string('payment_status', 20)->default('unpaid');
            $table->string('recipient_name');
            $table->string('recipient_phone', 32);
            // Снимок адреса доставки или адреса точки самовывоза на момент заказа
            $table->string('address');
            $table->unsignedSmallInteger('delivery_distance_km')->nullable();
            $table->text('comment')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('delivery_cost', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            // Комиссия платформы фиксируется в момент оформления
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->decimal('commission_amount', 10, 2)->default(0);
            $table->string('cancel_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            // Название и цена сохраняются, чтобы заказ не менялся при редактировании товара
            $table->string('product_name');
            $table->string('variant_name', 100)->nullable();
            $table->decimal('price', 10, 2);
            $table->unsignedSmallInteger('quantity');
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
