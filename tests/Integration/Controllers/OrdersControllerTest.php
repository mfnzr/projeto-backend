<?php

namespace Tests\Integration\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Production;
use App\Models\User;

class OrdersControllerTest extends ControllerTestCase
{
    private Customer $customer;
    private Product $product;
    private Order $order;

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

        $this->customer = new Customer(['name' => 'Maria', 'phone' => '42999990000']);
        $this->customer->save();

        $this->product = new Product(['name' => 'Frango assado']);
        $this->product->save();

        $production = new Production([
            'product_id' => $this->product->id,
            'production_date' => date('Y-m-d'),
            'quantity' => '20',
            'price_per_kg' => '45.90'
        ]);
        $production->save();

        $this->order = $this->createOrder(2);
    }

    private function createOrder(int $quantity): Order
    {
        $order = new Order([
            'customer_id' => $this->customer->id,
            'status' => 'ordered',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $order->setQuantities([$this->product->id => $quantity]);
        $order->save();

        return $order;
    }

    public function test_index_should_list_orders(): void
    {
        $response = $this->get(
            action: 'index',
            controllerName: 'App\Controllers\OrdersController'
        );

        $this->assertStringContainsString('Maria', $response);
        $this->assertStringContainsString('Encomendado', $response);
        $this->assertStringContainsString('Novo pedido', $response);
    }

    public function test_index_should_paginate_orders(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $this->createOrder(1);
        }

        $response = $this->get(
            action: 'index',
            controllerName: 'App\Controllers\OrdersController',
            params: ['page' => 2]
        );

        $this->assertStringContainsString('Mostrando 11 - 11 de 11', $response);
    }

    public function test_show_should_render_the_order_with_pickup_link(): void
    {
        $response = $this->get(
            action: 'show',
            controllerName: 'App\Controllers\OrdersController',
            params: ['id' => $this->order->id]
        );

        $this->assertStringContainsString('Pedido #' . $this->order->id, $response);
        $this->assertStringContainsString('Frango assado', $response);
        $this->assertStringContainsString('/sales/new?order_id=' . $this->order->id, $response);
    }

    public function test_new_should_render_form_with_products_of_the_day(): void
    {
        $response = $this->get(
            action: 'new',
            controllerName: 'App\Controllers\OrdersController'
        );

        $this->assertStringContainsString('Frango assado', $response);
        $this->assertStringContainsString('name="quantities[' . $this->product->id . ']"', $response);
        $this->assertStringContainsString('Maria - 42999990000', $response);
    }

    public function test_create_should_save_and_redirect(): void
    {
        $response = $this->post(
            action: 'create',
            controllerName: 'App\Controllers\OrdersController',
            params: [
                'order' => ['customer_id' => $this->customer->id],
                'quantities' => [$this->product->id => '3']
            ]
        );

        $this->assertEquals('Location: /orders', $response);
        $this->assertCount(2, Order::all());
    }

    public function test_create_with_invalid_data_should_render_form_with_errors(): void
    {
        $response = $this->post(
            action: 'create',
            controllerName: 'App\Controllers\OrdersController',
            params: [
                'order' => ['customer_id' => ''],
                'quantities' => [$this->product->id => '50']
            ]
        );

        $this->assertStringContainsString('não pode ser vazio!', $response);
        $this->assertStringContainsString('só tem 18 disponível!', $response);
        $this->assertCount(1, Order::all());
    }

    public function test_edit_should_render_form_with_order_quantities(): void
    {
        $response = $this->get(
            action: 'edit',
            controllerName: 'App\Controllers\OrdersController',
            params: ['id' => $this->order->id]
        );

        $this->assertStringContainsString('name="_method" value="PUT"', $response);
        $this->assertStringContainsString('value="2"', $response);
    }

    public function test_update_should_save_and_redirect(): void
    {
        $response = $this->put(
            action: 'update',
            controllerName: 'App\Controllers\OrdersController',
            params: [
                'id' => $this->order->id,
                'order' => ['customer_id' => $this->customer->id],
                'quantities' => [$this->product->id => '5']
            ]
        );

        $this->assertEquals('Location: /orders', $response);
        $this->assertEquals(5, Order::findById($this->order->id)->totalUnits());
    }

    public function test_update_with_invalid_data_should_render_edit_form_with_errors(): void
    {
        $response = $this->put(
            action: 'update',
            controllerName: 'App\Controllers\OrdersController',
            params: [
                'id' => $this->order->id,
                'order' => ['customer_id' => $this->customer->id],
                'quantities' => [$this->product->id => 'abc']
            ]
        );

        $this->assertStringContainsString('deve ser um número inteiro maior que zero!', $response);
        $this->assertStringContainsString('name="_method" value="PUT"', $response);
        $this->assertEquals(2, Order::findById($this->order->id)->totalUnits());
    }

    public function test_cancel_should_change_status_and_redirect(): void
    {
        $response = $this->put(
            action: 'cancel',
            controllerName: 'App\Controllers\OrdersController',
            params: ['id' => $this->order->id]
        );

        $this->assertEquals('Location: /orders', $response);
        $this->assertEquals('cancelled', Order::findById($this->order->id)->status);
    }

    public function test_destroy_should_remove_and_redirect(): void
    {
        $response = $this->post(
            action: 'destroy',
            controllerName: 'App\Controllers\OrdersController',
            params: ['id' => $this->order->id]
        );

        $this->assertEquals('Location: /orders', $response);
        $this->assertCount(0, Order::all());
    }

    public function test_destroy_should_not_remove_a_picked_up_order(): void
    {
        $this->order->update(['status' => 'picked_up']);

        $response = $this->post(
            action: 'destroy',
            controllerName: 'App\Controllers\OrdersController',
            params: ['id' => $this->order->id]
        );

        $this->assertEquals('Location: /orders', $response);
        $this->assertCount(1, Order::all());
    }
}
