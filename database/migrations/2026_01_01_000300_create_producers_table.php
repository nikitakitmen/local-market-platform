<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Профиль производителя (магазин). Один пользователь — один производитель.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('city_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            // self_employed | individual | organization (App\Enums\ProducerType)
            $table->string('type', 20);
            $table->string('inn', 12)->nullable();
            $table->text('description')->nullable();
            $table->string('phone', 32);
            $table->string('email');
            $table->string('address');
            $table->string('logo')->nullable();
            // pending | approved | rejected (App\Enums\ProducerStatus)
            $table->string('status', 20)->default('pending')->index();
            // Кэш рейтинга: пересчитывается при изменении отзывов
            $table->decimal('rating', 3, 2)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producers');
    }
};
