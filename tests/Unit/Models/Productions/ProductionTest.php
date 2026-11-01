<?php

namespace Tests\Unit\Models\Productions;

use App\Models\Product;
use App\Models\Production;
use Tests\TestCase;

class ProductionTest extends TestCase
{
    private Product $product;
    private Production $production;

    public function setUp(): void
    {
        parent::setUp();

        $this->product = new Product(['name' => 'Frango assado']);
        $this->product->save();

        $this->production = new Production([
            'product_id' => $this->product->id,
            'production_date' => '2026-11-01',
            'quantity' => '20',
            'price_per_kg' => '45.90'
        ]);
        $this->production->save();
    }

    public function test_should_create_new_production(): void
    {
        $this->assertCount(1, Production::all());
        $this->assertEquals('Frango assado', $this->production->product->name);
    }

    public function test_should_not_create_production_with_invalid_data(): void
    {
        $production = new Production([
            'product_id' => '',
            'production_date' => '2026-13-45',
            'quantity' => '0',
            'price_per_kg' => '-1'
        ]);

        $this->assertFalse($production->save());
        $this->assertEquals('não pode ser vazio!', $production->errors('product_id'));
        $this->assertEquals('deve ser uma data válida!', $production->errors('production_date'));
        $this->assertEquals('deve ser um número inteiro maior que zero!', $production->errors('quantity'));
        $this->assertEquals('deve ser um valor maior que zero!', $production->errors('price_per_kg'));
    }

    public function test_should_not_create_production_for_a_product_that_does_not_exist(): void
    {
        $production = new Production([
            'product_id' => 999,
            'production_date' => '2026-11-01',
            'quantity' => '10',
            'price_per_kg' => '40.00'
        ]);

        $this->assertFalse($production->save());
        $this->assertEquals('produto não encontrado!', $production->errors('product_id'));
    }

    public function test_should_not_create_two_productions_of_the_same_product_on_the_same_day(): void
    {
        $production = new Production([
            'product_id' => $this->product->id,
            'production_date' => '2026-11-01',
            'quantity' => '10',
            'price_per_kg' => '40.00'
        ]);

        $this->assertFalse($production->save());
        $this->assertEquals('já existe produção desse produto nessa data!', $production->errors('production_date'));
    }

    public function test_should_update_production_keeping_the_same_product_and_date(): void
    {
        $production = Production::findById($this->production->id);
        $production->product_id = (string) $this->product->id;
        $production->quantity = '25';

        $this->assertTrue($production->save());
        $this->assertEquals(25, Production::findById($this->production->id)->quantity);
    }

    public function test_destroy_should_remove_the_production(): void
    {
        $this->production->destroy();

        $this->assertCount(0, Production::all());
    }
}
