<?php

namespace Tests\Unit\Models\SideDishes;

use App\Models\SideDish;
use Tests\TestCase;

class SideDishTest extends TestCase
{
    private SideDish $sideDish;

    public function setUp(): void
    {
        parent::setUp();

        $this->sideDish = new SideDish(['name' => 'Farofa', 'price' => '8.00']);
        $this->sideDish->save();
    }

    public function test_should_create_new_side_dish(): void
    {
        $this->assertCount(1, SideDish::all());
        $this->assertEquals('8.00', SideDish::findById($this->sideDish->id)->price);
    }

    public function test_should_not_create_side_dish_without_name(): void
    {
        $sideDish = new SideDish(['price' => '5.00']);

        $this->assertFalse($sideDish->save());
        $this->assertEquals('não pode ser vazio!', $sideDish->errors('name'));
    }

    public function test_should_not_create_side_dish_with_invalid_price(): void
    {
        $sideDish = new SideDish(['name' => 'Maionese', 'price' => '0']);

        $this->assertFalse($sideDish->save());
        $this->assertEquals('deve ser um valor maior que zero!', $sideDish->errors('price'));
    }

    public function test_should_not_create_side_dish_with_duplicated_name(): void
    {
        $sideDish = new SideDish(['name' => 'Farofa', 'price' => '9.00']);

        $this->assertFalse($sideDish->save());
        $this->assertEquals('já existe um registro com esse dado', $sideDish->errors('name'));
    }

    public function test_should_update_side_dish(): void
    {
        $this->sideDish->price = '9.50';

        $this->assertTrue($this->sideDish->save());
        $this->assertEquals('9.50', SideDish::findById($this->sideDish->id)->price);
    }

    public function test_destroy_should_remove_the_side_dish(): void
    {
        $this->sideDish->destroy();

        $this->assertCount(0, SideDish::all());
    }
}
