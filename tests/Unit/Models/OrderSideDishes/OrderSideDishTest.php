<?php

namespace Tests\Unit\Models\OrderSideDishes;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderSideDish;
use App\Models\Product;
use App\Models\Production;
use App\Models\SideDish;
use Core\Database\Database;
use PDOException;
use Tests\TestCase;

class OrderSideDishTest extends TestCase
{
    private Order $order;
    private SideDish $farofa;
    private SideDish $maionese;

    public function setUp(): void
    {
        parent::setUp();

        $customer = new Customer(['name' => 'Maria', 'phone' => '42999990000']);
        $customer->save();

        $product = new Product(['name' => 'Frango assado']);
        $product->save();

        $production = new Production([
            'product_id' => $product->id,
            'production_date' => date('Y-m-d'),
            'quantity' => '10',
            'price_per_kg' => '45.90'
        ]);
        $production->save();

        $this->order = new Order([
            'customer_id' => $customer->id,
            'status' => 'ordered',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $this->order->setQuantities([$product->id => 2]);
        $this->order->save();

        $this->farofa = new SideDish(['name' => 'Farofa', 'price' => '8.00']);
        $this->farofa->save();

        $this->maionese = new SideDish(['name' => 'Maionese', 'price' => '12.00']);
        $this->maionese->save();
    }

    private function addSideDish(SideDish $sideDish): OrderSideDish
    {
        $orderSideDish = new OrderSideDish([
            'order_id' => $this->order->id,
            'side_dish_id' => $sideDish->id
        ]);
        $orderSideDish->save();

        return $orderSideDish;
    }

    public function test_should_add_side_dish_to_order(): void
    {
        $orderSideDish = new OrderSideDish([
            'order_id' => $this->order->id,
            'side_dish_id' => $this->farofa->id
        ]);

        $this->assertTrue($orderSideDish->save());
        $this->assertCount(1, OrderSideDish::all());
    }

    public function test_should_not_add_without_order(): void
    {
        $orderSideDish = new OrderSideDish(['side_dish_id' => $this->farofa->id]);

        $this->assertFalse($orderSideDish->save());
        $this->assertEquals('não pode ser vazio!', $orderSideDish->errors('order_id'));
    }

    public function test_should_not_add_without_side_dish(): void
    {
        $orderSideDish = new OrderSideDish(['order_id' => $this->order->id]);

        $this->assertFalse($orderSideDish->save());
        $this->assertEquals('não pode ser vazio!', $orderSideDish->errors('side_dish_id'));
    }

    public function test_should_not_add_to_an_order_that_does_not_exist(): void
    {
        $orderSideDish = new OrderSideDish(['order_id' => 999, 'side_dish_id' => $this->farofa->id]);

        $this->assertFalse($orderSideDish->save());
        $this->assertEquals('pedido não encontrado!', $orderSideDish->errors('order_id'));
    }

    public function test_should_not_add_a_side_dish_that_does_not_exist(): void
    {
        $orderSideDish = new OrderSideDish(['order_id' => $this->order->id, 'side_dish_id' => 999]);

        $this->assertFalse($orderSideDish->save());
        $this->assertEquals('acompanhamento não encontrado!', $orderSideDish->errors('side_dish_id'));
    }

    public function test_should_not_add_the_same_side_dish_twice(): void
    {
        $this->addSideDish($this->farofa);

        $orderSideDish = new OrderSideDish([
            'order_id' => $this->order->id,
            'side_dish_id' => $this->farofa->id
        ]);

        $this->assertFalse($orderSideDish->save());
        $this->assertEquals('esse acompanhamento já está no pedido!', $orderSideDish->errors('side_dish_id'));
        $this->assertCount(1, OrderSideDish::all());
    }

    public function test_database_should_reject_the_same_side_dish_twice(): void
    {
        $this->addSideDish($this->farofa);

        $this->expectException(PDOException::class);

        Database::getDatabaseConn()->exec(
            "INSERT INTO order_side_dishes (order_id, side_dish_id) "
            . "VALUES ({$this->order->id}, {$this->farofa->id})"
        );
    }

    public function test_database_should_reject_an_order_that_does_not_exist(): void
    {
        $this->expectException(PDOException::class);

        Database::getDatabaseConn()->exec(
            "INSERT INTO order_side_dishes (order_id, side_dish_id) VALUES (999, {$this->farofa->id})"
        );
    }

    public function test_order_should_have_many_side_dishes(): void
    {
        $this->addSideDish($this->farofa);
        $this->addSideDish($this->maionese);

        $sideDishes = $this->order->sideDishes;

        $this->assertCount(2, $sideDishes);
        $this->assertEquals('Farofa', $sideDishes[0]->name);
        $this->assertEquals('Maionese', $sideDishes[1]->name);
        $this->assertEquals(2, $this->order->sideDishes()->count());
    }

    public function test_side_dish_should_belong_to_many_orders(): void
    {
        $this->addSideDish($this->farofa);

        $orders = $this->farofa->orders;

        $this->assertCount(1, $orders);
        $this->assertEquals($this->order->id, $orders[0]->id);
    }

    public function test_order_side_dish_should_belong_to_order_and_side_dish(): void
    {
        $orderSideDish = $this->addSideDish($this->farofa);

        $this->assertEquals($this->order->id, $orderSideDish->order->id);
        $this->assertEquals('Farofa', $orderSideDish->sideDish->name);
    }

    public function test_side_dish_names_should_list_the_side_dishes(): void
    {
        $this->assertEquals('-', $this->order->sideDishNames());

        $this->addSideDish($this->farofa);
        $this->addSideDish($this->maionese);

        $this->assertEquals('Farofa, Maionese', $this->order->sideDishNames());
    }

    public function test_has_orders_should_be_true_only_for_side_dish_in_orders(): void
    {
        $this->addSideDish($this->farofa);

        $this->assertTrue($this->farofa->hasOrders());
        $this->assertFalse($this->maionese->hasOrders());
    }

    public function test_destroy_order_should_remove_its_side_dishes_from_the_relation(): void
    {
        $this->addSideDish($this->farofa);
        $this->addSideDish($this->maionese);

        $this->order->destroy();

        $this->assertCount(0, OrderSideDish::all());
        $this->assertCount(2, SideDish::all());
    }

    public function test_database_should_not_delete_side_dish_used_in_orders(): void
    {
        $this->addSideDish($this->farofa);

        $this->expectException(PDOException::class);

        $this->farofa->destroy();
    }
}
