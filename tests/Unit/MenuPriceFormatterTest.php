<?php

namespace Tests\Unit;

use App\Services\MenuPriceFormatter;
use PHPUnit\Framework\TestCase;
use stdClass;

class MenuPriceFormatterTest extends TestCase
{
    public function test_formats_unit_price_with_euro_symbol(): void
    {
        $product = new stdClass();
        $product->price_unit = '7.50';
        $product->price_portion = null;
        $product->weight_sale = false;
        $product->individual_sale = false;

        $formatted = (new MenuPriceFormatter())->format($product);

        $this->assertSame('7,50 ' . MenuPriceFormatter::EURO, $formatted);
        $this->assertStringContainsString('€', $formatted);
        $this->assertStringNotContainsString('�', $formatted);
    }

    public function test_returns_null_when_no_price(): void
    {
        $product = new stdClass();
        $product->price_unit = null;
        $product->price_portion = null;

        $this->assertNull((new MenuPriceFormatter())->format($product));
    }
}
