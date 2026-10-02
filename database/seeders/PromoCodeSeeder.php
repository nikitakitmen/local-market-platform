<?php

namespace Database\Seeders;

use App\Enums\PromoCodeType;
use App\Models\PromoCode;
use Illuminate\Database\Seeder;

class PromoCodeSeeder extends Seeder
{
    public function run(): void
    {
        PromoCode::create([
            'code' => 'WELCOME10',
            'type' => PromoCodeType::Percent,
            'value' => 10,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(6),
            'is_active' => true,
        ]);

        PromoCode::create([
            'code' => 'LOCAL300',
            'type' => PromoCodeType::Fixed,
            'value' => 300,
            'min_order_amount' => 2000,
            'start_date' => now()->subWeek(),
            'end_date' => now()->addMonths(2),
            'is_active' => true,
        ]);

        // Истёкший промокод — для демонстрации проверки сроков
        PromoCode::create([
            'code' => 'SUMMER25',
            'type' => PromoCodeType::Percent,
            'value' => 25,
            'start_date' => now()->subMonths(4),
            'end_date' => now()->subMonth(),
            'is_active' => true,
        ]);
    }
}
