<?php

namespace Tests\Integration\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Production;
use App\Models\Sale;
use App\Models\User;

class SalesControllerTest extends ControllerTestCase
{
    private Product $product;
    private Production $production;
    private Sale $sale;

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

        $this->product = new Product(['name' => 'Frango assado']);
        $this->product->save();

        $this->production = new Production([
            'product_id' => $this->product->id,
            'production_date' => date('Y-m-d'),
            'quantity' => '20',
            'price_per_kg' => '50.00'
        ]);
        $this->production->save();

        $this->sale = $this->createSale();
    }

    private function createSale(): Sale
    {
        $sale = new Sale(['payment_method' => 'cash', 'created_at' => date('Y-m-d H:i:s')]);
        $sale->setItems([$this->product->id => ['quantity' => 1, 'amount' => '50.00']]);
        $sale->save();

        return $sale;
    }

    private function createOrder(int $quantity): Order
    {
        $customer = new Customer(['name' => 'Maria', 'phone' => '42999990000']);
        $customer->save();

        $order = new Order([
            'customer_id' => $customer->id,
            'status' => 'ordered',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $order->setQuantities([$this->product->id => $quantity]);
        $order->save();

        return $order;
    }

    public function test_index_should_list_sales(): void
    {
        $response = $this->get(
            action: 'index',
            controllerName: 'App\Controllers\SalesController'
        );

        $this->assertStringContainsString('Balcão', $response);
        $this->assertStringContainsString('Dinheiro', $response);
        $this->assertStringContainsString('R$ 50,00', $response);
    }

    public function test_index_should_paginate_sales(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $this->createSale();
        }

        $response = $this->get(
            action: 'index',
            controllerName: 'App\Controllers\SalesController',
            params: ['page' => 2]
        );

        $this->assertStringContainsString('Mostrando 11 - 11 de 11', $response);
    }

    public function test_show_should_render_the_sale_with_weight(): void
    {
        $response = $this->get(
            action: 'show',
            controllerName: 'App\Controllers\SalesController',
            params: ['id' => $this->sale->id]
        );

        $this->assertStringContainsString('Venda #' . $this->sale->id, $response);
        $this->assertStringContainsString('1,000 kg', $response);
        $this->assertStringContainsString('venda no balcão', $response);
    }

    public function test_new_should_render_counter_sale_form(): void
    {
        $response = $this->get(
            action: 'new',
            controllerName: 'App\Controllers\SalesController'
        );

        $this->assertStringContainsString('name="items[' . $this->product->id . '][quantity]"', $response);
        $this->assertStringContainsString('name="items[' . $this->product->id . '][amount]"', $response);
    }

    public function test_new_with_order_should_render_pickup_form(): void
    {
        $order = $this->createOrder(3);

        $response = $this->get(
            action: 'new',
            controllerName: 'App\Controllers\SalesController',
            params: ['order_id' => $order->id]
        );

        $this->assertStringContainsString('Retirada do pedido #' . $order->id, $response);
        $this->assertStringContainsString('name="sale[order_id]" value="' . $order->id . '"', $response);
        $this->assertStringNotContainsString('[quantity]', $response);
    }

    public function test_create_should_save_and_redirect(): void
    {
        $response = $this->post(
            action: 'create',
            controllerName: 'App\Controllers\SalesController',
            params: [
                'sale' => ['order_id' => '', 'payment_method' => 'pix'],
                'items' => [$this->product->id => ['quantity' => '2', 'amount' => '75.00']]
            ]
        );

        $this->assertEquals('Location: /sales', $response);
        $this->assertCount(2, Sale::all());
        $this->assertEquals(17, $this->production->available());
    }

    public function test_create_with_invalid_data_should_render_form_with_errors(): void
    {
        $response = $this->post(
            action: 'create',
            controllerName: 'App\Controllers\SalesController',
            params: [
                'sale' => ['order_id' => ''],
                'items' => [$this->product->id => ['quantity' => '30', 'amount' => '75.00']]
            ]
        );

        $this->assertStringContainsString('escolha a forma de pagamento!', $response);
        $this->assertStringContainsString('só tem 19 disponível!', $response);
        $this->assertCount(1, Sale::all());
    }

    public function test_create_from_order_should_mark_the_order_as_picked_up(): void
    {
        $order = $this->createOrder(3);

        $response = $this->post(
            action: 'create',
            controllerName: 'App\Controllers\SalesController',
            params: [
                'sale' => ['order_id' => (string) $order->id, 'payment_method' => 'card'],
                'items' => [$this->product->id => ['amount' => '150.00']]
            ]
        );

        $this->assertEquals('Location: /sales', $response);
        $this->assertEquals('picked_up', Order::findById($order->id)->status);
        $this->assertEquals(16, $this->production->available());
    }

    public function test_edit_should_render_form_with_sale_amounts(): void
    {
        $response = $this->get(
            action: 'edit',
            controllerName: 'App\Controllers\SalesController',
            params: ['id' => $this->sale->id]
        );

        $this->assertStringContainsString('name="_method" value="PUT"', $response);
        $this->assertStringContainsString('value="50.00"', $response);
        $this->assertStringContainsString('value="cash" checked', $response);
    }

    public function test_update_should_save_and_redirect(): void
    {
        $response = $this->put(
            action: 'update',
            controllerName: 'App\Controllers\SalesController',
            params: [
                'id' => $this->sale->id,
                'sale' => ['payment_method' => 'pix'],
                'items' => [$this->product->id => ['amount' => '60.00']]
            ]
        );

        $this->assertEquals('Location: /sales', $response);
        $sale = Sale::findById($this->sale->id);
        $this->assertEquals('pix', $sale->payment_method);
        $this->assertEquals(60.0, $sale->total());
    }

    public function test_update_with_invalid_data_should_render_edit_form_with_errors(): void
    {
        $response = $this->put(
            action: 'update',
            controllerName: 'App\Controllers\SalesController',
            params: [
                'id' => $this->sale->id,
                'sale' => ['payment_method' => 'cash'],
                'items' => [$this->product->id => ['amount' => '0']]
            ]
        );

        $this->assertStringContainsString('o valor da balança deve ser maior que zero!', $response);
        $this->assertEquals(50.0, Sale::findById($this->sale->id)->total());
    }

    public function test_destroy_should_remove_and_redirect(): void
    {
        $response = $this->post(
            action: 'destroy',
            controllerName: 'App\Controllers\SalesController',
            params: ['id' => $this->sale->id]
        );

        $this->assertEquals('Location: /sales', $response);
        $this->assertCount(0, Sale::all());
        $this->assertEquals(20, $this->production->available());
    }

    public function test_destroy_sale_from_order_should_reopen_the_order(): void
    {
        $order = $this->createOrder(3);
        $sale = new Sale([
            'order_id' => $order->id,
            'payment_method' => 'pix',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $sale->setItems([$this->product->id => ['quantity' => 3, 'amount' => '150.00']]);
        $sale->save();
        $order->update(['status' => 'picked_up']);

        $this->post(
            action: 'destroy',
            controllerName: 'App\Controllers\SalesController',
            params: ['id' => $sale->id]
        );

        $this->assertEquals('ordered', Order::findById($order->id)->status);
    }
}
