<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Торговые точки производителя (магазин, лавка, место на рынке).
 * Точка может быть пунктом самовывоза.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producer_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producer_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('address');
            $table->string('working_hours')->nullable();
            $table->string('phone', 32)->nullable();
            $table->boolean('is_pickup_point')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producer_locations');
    }
};
