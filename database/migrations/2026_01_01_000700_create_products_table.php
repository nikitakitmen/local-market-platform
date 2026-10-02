<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('subcategory_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            // Если у товара есть варианты, здесь хранится минимальная цена варианта
            $table->decimal('price', 10, 2);
            // Единица продажи: «шт», «1 кг», «буханка»
            $table->string('unit', 30)->nullable();
            // Основная фотография (путь в Storage)
            $table->string('image')->nullable();
            // Простое наличие: есть / нет (без складского учёта)
            $table->boolean('in_stock')->default(true);
            // Производитель может временно скрыть товар
            $table->boolean('is_active')->default(true);
            // Кэш рейтинга и счётчики для сортировки «популярные»
            $table->decimal('rating', 3, 2)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->unsignedInteger('sales_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'in_stock']);
            $table->index('price');
            $table->index('sales_count');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
