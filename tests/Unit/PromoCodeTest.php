<?php

namespace Tests\Unit;

use App\Enums\PromoCodeType;
use App\Models\PromoCode;
use Tests\TestCase;

class PromoCodeTest extends TestCase
{
    public function test_percent_discount(): void
    {
        $promo = new PromoCode(['type' => PromoCodeType::Percent, 'value' => 10, 'is_active' => true]);

        $this->assertEquals(130, $promo->discountFor(1300));
    }

    public function test_fixed_discount_is_not_bigger_than_amount(): void
    {
        $promo = new PromoCode(['type' => PromoCodeType::Fixed, 'value' => 300, 'is_active' => true]);

        $this->assertEquals(300, $promo->discountFor(2000));
        $this->assertEquals(250, $promo->discountFor(250));
    }

    public function test_promo_code_respects_dates_and_minimum_amount(): void
    {
        $promo = new PromoCode([
            'type' => PromoCodeType::Fixed,
            'value' => 300,
            'min_order_amount' => 2000,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'is_active' => true,
        ]);

        $this->assertNull($promo->errorFor(2500));
        $this->assertNotNull($promo->errorFor(1500));

        $promo->end_date = now()->subDay();
        $this->assertNotNull($promo->errorFor(2500));
    }
}
