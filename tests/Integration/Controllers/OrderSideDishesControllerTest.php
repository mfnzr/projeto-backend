<?php

namespace Tests\Integration\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderSideDish;
use App\Models\Product;
use App\Models\Production;
use App\Models\SideDish;
use App\Models\User;

class OrderSideDishesControllerTest extends ControllerTestCase
{
    private Order $order;
    private SideDish $sideDish;

    public function setUp(): void
    {
        parent::setUp();

        $attendant = new User([
            'name' => 'Fulano',
            'email' => 'fulano@example.com',
            'password' => '123456',
            'password_confirmation' => '123456',
            'role' => 'attendant'
        ]);
        $attendant->save();
        $_SESSION['user']['id'] = $attendant->id;

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

        $this->sideDish = new SideDish(['name' => 'Farofa', 'price' => '8.00']);
        $this->sideDish->save();
    }

    private function addSideDish(): void
    {
        $orderSideDish = new OrderSideDish([
            'order_id' => $this->order->id,
            'side_dish_id' => $this->sideDish->id
        ]);
        $orderSideDish->save();
    }

    public function test_create_should_add_the_side_dish_and_return_json(): void
    {
        $response = $this->post(
            action: 'create',
            controllerName: 'App\Controllers\OrderSideDishesController',
            params: ['order_id' => $this->order->id, 'side_dish_id' => $this->sideDish->id]
        );

        $json = json_decode($response, true);

        $this->assertEquals($this->sideDish->id, $json['side_dish']['id']);
        $this->assertEquals('Farofa', $json['side_dish']['name']);
        $this->assertEquals('R$ 8,00', $json['side_dish']['price']);
        $this->assertCount(1, OrderSideDish::all());
    }

    public function test_create_should_not_add_the_same_side_dish_twice(): void
    {
        $this->addSideDish();

        $response = $this->post(
            action: 'create',
            controllerName: 'App\Controllers\OrderSideDishesController',
            params: ['order_id' => $this->order->id, 'side_dish_id' => $this->sideDish->id]
        );

        $json = json_decode($response, true);

        $this->assertEquals('Não foi possível adicionar: esse acompanhamento já está no pedido!', $json['error']);
        $this->assertCount(1, OrderSideDish::all());
    }

    public function test_create_should_return_error_when_order_does_not_exist(): void
    {
        $response = $this->post(
            action: 'create',
            controllerName: 'App\Controllers\OrderSideDishesController',
            params: ['order_id' => 999, 'side_dish_id' => $this->sideDish->id]
        );

        $json = json_decode($response, true);

        $this->assertEquals('Pedido não encontrado', $json['error']);
        $this->assertCount(0, OrderSideDish::all());
    }

    public function test_create_should_not_change_an_order_that_was_picked_up(): void
    {
        $this->order->update(['status' => 'picked_up']);

        $response = $this->post(
            action: 'create',
            controllerName: 'App\Controllers\OrderSideDishesController',
            params: ['order_id' => $this->order->id, 'side_dish_id' => $this->sideDish->id]
        );

        $json = json_decode($response, true);

        $this->assertEquals('Só é possível alterar os acompanhamentos de pedidos encomendados', $json['error']);
        $this->assertCount(0, OrderSideDish::all());
    }

    public function test_destroy_should_remove_the_side_dish_and_return_json(): void
    {
        $this->addSideDish();

        $response = $this->post(
            action: 'destroy',
            controllerName: 'App\Controllers\OrderSideDishesController',
            params: ['order_id' => $this->order->id, 'side_dish_id' => $this->sideDish->id]
        );

        $json = json_decode($response, true);

        $this->assertEquals($this->sideDish->id, $json['side_dish_id']);
        $this->assertCount(0, OrderSideDish::all());
        $this->assertCount(1, SideDish::all());
    }

    public function test_destroy_should_return_error_when_side_dish_is_not_in_the_order(): void
    {
        $response = $this->post(
            action: 'destroy',
            controllerName: 'App\Controllers\OrderSideDishesController',
            params: ['order_id' => $this->order->id, 'side_dish_id' => $this->sideDish->id]
        );

        $json = json_decode($response, true);

        $this->assertEquals('Esse acompanhamento não está no pedido', $json['error']);
    }

    public function test_show_order_should_list_its_side_dishes(): void
    {
        $this->addSideDish();

        $response = $this->get(
            action: 'show',
            controllerName: 'App\Controllers\OrdersController',
            params: ['id' => $this->order->id]
        );

        $this->assertStringContainsString('Farofa - R$ 8,00', $response);
        $this->assertStringContainsString('order_side_dishes.js', $response);
    }

    public function test_index_orders_should_show_the_side_dishes(): void
    {
        $this->addSideDish();

        $response = $this->get(
            action: 'index',
            controllerName: 'App\Controllers\OrdersController'
        );

        $this->assertStringContainsString('<td>Farofa</td>', $response);
    }

    public function test_destroy_side_dish_used_in_orders_should_not_remove_it(): void
    {
        $this->addSideDish();

        $response = $this->post(
            action: 'destroy',
            controllerName: 'App\Controllers\SideDishesController',
            params: ['id' => $this->sideDish->id]
        );

        $this->assertEquals('Location: /admin/side-dishes', $response);
        $this->assertCount(1, SideDish::all());
        $this->assertCount(1, OrderSideDish::all());
    }
}
