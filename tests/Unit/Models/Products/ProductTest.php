<?php

namespace Tests\Unit\Models\Products;

use App\Models\Product;
use App\Models\Production;
use App\Models\Purchase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    private Product $product;

    public function setUp(): void
    {
        parent::setUp();

        $this->product = new Product([
            'name' => 'Frango assado',
            'description' => 'Frango inteiro assado na brasa.'
        ]);
        $this->product->save();
    }

    public function test_should_create_new_product(): void
    {
        $this->assertCount(1, Product::all());
        $this->assertGreaterThan(0, $this->product->id);
    }

    public function test_should_create_product_without_description(): void
    {
        $product = new Product(['name' => 'Linguiça']);

        $this->assertTrue($product->save());
        $this->assertNull(Product::findById($product->id)->description);
    }

    public function test_should_not_create_product_without_name(): void
    {
        $product = new Product(['description' => 'Sem nome']);

        $this->assertFalse($product->save());
        $this->assertEquals('não pode ser vazio!', $product->errors('name'));
        $this->assertCount(1, Product::all());
    }

    public function test_should_not_create_product_with_duplicated_name(): void
    {
        $product = new Product(['name' => 'Frango assado']);

        $this->assertFalse($product->save());
        $this->assertEquals('já existe um registro com esse dado', $product->errors('name'));
        $this->assertCount(1, Product::all());
    }

    public function test_should_update_product(): void
    {
        $this->product->name = 'Frango recheado';
        $this->product->description = 'Com farofa.';

        $this->assertTrue($this->product->save());

        $product = Product::findById($this->product->id);
        $this->assertEquals('Frango recheado', $product->name);
        $this->assertEquals('Com farofa.', $product->description);
    }

    public function test_should_update_product_keeping_the_same_name(): void
    {
        $this->product->description = 'Nova descrição';

        $this->assertTrue($this->product->save());
    }

    public function test_should_not_update_product_with_name_of_another_product(): void
    {
        $other = new Product(['name' => 'Pernil']);
        $other->save();

        $other->name = 'Frango assado';

        $this->assertFalse($other->save());
        $this->assertEquals('Pernil', Product::findById($other->id)->name);
    }

    public function test_destroy_should_remove_the_product(): void
    {
        $this->product->destroy();

        $this->assertCount(0, Product::all());
    }

    public function test_find_by_id_should_return_the_product(): void
    {
        $this->assertEquals('Frango assado', Product::findById($this->product->id)->name);
    }

    public function test_has_movements_should_return_false_without_productions_and_purchases(): void
    {
        $this->assertFalse($this->product->hasMovements());
    }

    public function test_has_movements_should_return_true_with_a_production(): void
    {
        $production = new Production([
            'product_id' => $this->product->id,
            'production_date' => '2026-11-01',
            'quantity' => '10',
            'price_per_kg' => '45.90'
        ]);
        $production->save();

        $this->assertTrue($this->product->hasMovements());
    }

    public function test_has_movements_should_return_true_with_a_purchase(): void
    {
        $purchase = new Purchase([
            'product_id' => $this->product->id,
            'purchase_date' => '2026-11-01',
            'quantity' => '10',
            'total_amount' => '300.00'
        ]);
        $purchase->save();

        $this->assertTrue($this->product->hasMovements());
    }

    public function test_paginate_should_return_ten_products_per_page(): void
    {
        for ($i = 1; $i <= 11; $i++) {
            $product = new Product(['name' => 'Produto ' . $i]);
            $product->save();
        }

        $firstPage = Product::paginate(page: 1, per_page: 10);
        $secondPage = Product::paginate(page: 2, per_page: 10);

        $this->assertCount(10, $firstPage->registers());
        $this->assertCount(2, $secondPage->registers());
        $this->assertEquals(2, $firstPage->totalOfPages());
        $this->assertEquals(12, $firstPage->totalOfRegisters());
    }

    public function test_find_by_id_should_return_null(): void
    {
        $this->assertNull(Product::findById(999));
    }
}
