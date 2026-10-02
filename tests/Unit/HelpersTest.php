<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    public function test_money_formats_rubles(): void
    {
        $this->assertSame("1\u{00A0}250\u{00A0}₽", money(1250));
        $this->assertSame("99,50\u{00A0}₽", money(99.5));
    }

    public function test_plural_forms(): void
    {
        $this->assertSame('товар', plural(1, 'товар', 'товара', 'товаров'));
        $this->assertSame('товара', plural(3, 'товар', 'товара', 'товаров'));
        $this->assertSame('товаров', plural(11, 'товар', 'товара', 'товаров'));
        $this->assertSame('товар', plural(21, 'товар', 'товара', 'товаров'));
    }
}
