<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Сохранённые адреса доставки покупателя.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('city_id')->constrained()->restrictOnDelete();
            $table->string('title', 50)->nullable();
            $table->string('street');
            $table->string('apartment', 20)->nullable();
            $table->string('entrance', 10)->nullable();
            $table->string('floor', 10)->nullable();
            // Примерная удалённость от центра города — используется в формуле стоимости доставки
            $table->unsignedSmallInteger('distance_km')->default(5);
            $table->string('comment')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
