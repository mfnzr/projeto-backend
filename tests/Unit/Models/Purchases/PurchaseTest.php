<?php

namespace Tests\Unit\Models\Purchases;

use App\Models\Product;
use App\Models\Purchase;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    private Product $product;
    private Purchase $purchase;

    public function setUp(): void
    {
        parent::setUp();

        $this->product = new Product(['name' => 'Costela bovina']);
        $this->product->save();

        $this->purchase = new Purchase([
            'product_id' => $this->product->id,
            'purchase_date' => '2026-11-01',
            'quantity' => '10',
            'total_amount' => '500.00',
            'price_per_kg' => '25.00'
        ]);
        $this->purchase->save();
    }

    public function test_should_create_new_purchase(): void
    {
        $this->assertCount(1, Purchase::all());
        $this->assertEquals('Costela bovina', $this->purchase->product->name);
    }

    public function test_should_create_purchase_without_price_per_kg(): void
    {
        $purchase = new Purchase([
            'product_id' => $this->product->id,
            'purchase_date' => '2026-11-01',
            'quantity' => '5',
            'total_amount' => '200.00',
            'price_per_kg' => ''
        ]);

        $this->assertTrue($purchase->save());
        $this->assertNull(Purchase::findById($purchase->id)->price_per_kg);
    }

    public function test_should_not_create_purchase_with_invalid_data(): void
    {
        $purchase = new Purchase([
            'product_id' => '',
            'purchase_date' => '',
            'quantity' => 'abc',
            'total_amount' => '0',
            'price_per_kg' => '-5'
        ]);

        $this->assertFalse($purchase->save());
        $this->assertEquals('não pode ser vazio!', $purchase->errors('product_id'));
        $this->assertEquals('deve ser uma data válida!', $purchase->errors('purchase_date'));
        $this->assertEquals('deve ser um número inteiro maior que zero!', $purchase->errors('quantity'));
        $this->assertEquals('deve ser um valor maior que zero!', $purchase->errors('total_amount'));
        $this->assertEquals('deve ser um valor maior que zero!', $purchase->errors('price_per_kg'));
    }

    public function test_weight_should_be_the_total_divided_by_the_price_per_kg(): void
    {
        $this->assertEquals(20.0, $this->purchase->weight());
    }

    public function test_weight_should_be_null_without_price_per_kg(): void
    {
        $this->purchase->price_per_kg = '';

        $this->assertNull($this->purchase->weight());
    }

    public function test_should_update_purchase(): void
    {
        $this->purchase->total_amount = '550.00';

        $this->assertTrue($this->purchase->save());
        $this->assertEquals('550.00', Purchase::findById($this->purchase->id)->total_amount);
    }

    public function test_destroy_should_remove_the_purchase(): void
    {
        $this->purchase->destroy();

        $this->assertCount(0, Purchase::all());
    }
}
