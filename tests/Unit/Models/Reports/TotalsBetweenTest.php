<?php

namespace Tests\Unit\Models\Reports;

use App\Models\Product;
use App\Models\Production;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use Tests\TestCase;

class TotalsBetweenTest extends TestCase
{
    private Product $product;

    public function setUp(): void
    {
        parent::setUp();

        $this->product = new Product(['name' => 'Frango assado']);
        $this->product->save();

        $production = new Production([
            'product_id' => $this->product->id,
            'production_date' => date('Y-m-d'),
            'quantity' => '10',
            'price_per_kg' => '50.00'
        ]);
        $production->save();
    }

    public function test_sale_items_totals_should_sum_quantity_amount_and_weight(): void
    {
        $sale = new Sale(['payment_method' => 'pix', 'created_at' => date('Y-m-d H:i:s')]);
        $sale->setItems([$this->product->id => ['quantity' => 2, 'amount' => '100.00']]);
        $sale->save();

        $totals = SaleItem::totalsBetween(date('Y-m-d'), date('Y-m-d'));

        $this->assertEquals(2, $totals['quantity']);
        $this->assertEquals(100.0, $totals['amount']);
        $this->assertEquals(2.0, $totals['weight']);
    }

    public function test_sale_items_totals_should_ignore_sales_outside_the_period(): void
    {
        $sale = new Sale(['payment_method' => 'pix', 'created_at' => date('Y-m-d H:i:s')]);
        $sale->setItems([$this->product->id => ['quantity' => 2, 'amount' => '100.00']]);
        $sale->save();

        $totals = SaleItem::totalsBetween('2020-01-01', '2020-01-31');

        $this->assertEquals(0, $totals['quantity']);
        $this->assertEquals(0.0, $totals['amount']);
    }

    public function test_purchases_totals_should_only_count_weight_when_price_per_kg_exists(): void
    {
        $withPrice = new Purchase([
            'product_id' => $this->product->id,
            'purchase_date' => '2026-11-01',
            'quantity' => '10',
            'total_amount' => '200.00',
            'price_per_kg' => '20.00'
        ]);
        $withPrice->save();

        $withoutPrice = new Purchase([
            'product_id' => $this->product->id,
            'purchase_date' => '2026-11-01',
            'quantity' => '5',
            'total_amount' => '100.00'
        ]);
        $withoutPrice->save();

        $totals = Purchase::totalsBetween('2026-11-01', '2026-11-01');

        $this->assertEquals(15, $totals['quantity']);
        $this->assertEquals(300.0, $totals['amount']);
        $this->assertEquals(10.0, $totals['weight']);
        $this->assertEquals(200.0, $totals['amount_with_weight']);
    }
}
