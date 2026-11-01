<?php

namespace Tests\Unit\Models\Orders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Production;
use Tests\TestCase;

class OrderTest extends TestCase
{
    private Customer $customer;
    private Product $product;
    private Production $production;

    public function setUp(): void
    {
        parent::setUp();

        $this->customer = new Customer(['name' => 'Maria', 'phone' => '42999990000']);
        $this->customer->save();

        $this->product = new Product(['name' => 'Frango assado']);
        $this->product->save();

        $this->production = new Production([
            'product_id' => $this->product->id,
            'production_date' => date('Y-m-d'),
            'quantity' => '10',
            'price_per_kg' => '45.90'
        ]);
        $this->production->save();
    }

    private function newOrder(int $quantity): Order
    {
        $order = new Order([
            'customer_id' => $this->customer->id,
            'status' => 'ordered',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $order->setQuantities([$this->product->id => $quantity]);

        return $order;
    }

    public function test_should_create_order_with_items(): void
    {
        $order = $this->newOrder(3);

        $this->assertTrue($order->save());
        $this->assertCount(1, $order->items);
        $this->assertEquals(3, $order->totalUnits());
        $this->assertEquals('Encomendado', $order->statusName());
    }

    public function test_should_not_create_order_without_customer(): void
    {
        $order = new Order(['status' => 'ordered', 'created_at' => date('Y-m-d H:i:s')]);
        $order->setQuantities([$this->product->id => 3]);

        $this->assertFalse($order->save());
        $this->assertEquals('não pode ser vazio!', $order->errors('customer_id'));
    }

    public function test_should_not_create_order_without_products(): void
    {
        $order = $this->newOrder(3);
        $order->setQuantities([$this->product->id => '']);

        $this->assertFalse($order->save());
        $this->assertEquals('escolha pelo menos um produto!', $order->errors('items'));
    }

    public function test_should_not_create_order_with_invalid_quantity(): void
    {
        $order = $this->newOrder(3);
        $order->setQuantities([$this->product->id => '-2']);

        $this->assertFalse($order->save());
        $this->assertEquals('deve ser um número inteiro maior que zero!', $order->errors('item_' . $this->product->id));
    }

    public function test_should_not_create_order_with_more_than_available(): void
    {
        $order = $this->newOrder(11);

        $this->assertFalse($order->save());
        $this->assertEquals('só tem 10 disponível!', $order->errors('item_' . $this->product->id));
        $this->assertCount(0, Order::all());
    }

    public function test_should_not_create_order_for_product_without_production_today(): void
    {
        $other = new Product(['name' => 'Pernil']);
        $other->save();

        $order = $this->newOrder(1);
        $order->setQuantities([$other->id => 1]);

        $this->assertFalse($order->save());
        $this->assertEquals('esse produto não foi produzido hoje!', $order->errors('item_' . $other->id));
    }

    public function test_update_should_count_its_own_units_as_available(): void
    {
        $order = $this->newOrder(8);
        $order->save();

        $order = Order::findById($order->id);
        $order->setQuantities([$this->product->id => 10]);

        $this->assertTrue($order->save());
        $this->assertEquals(10, Order::findById($order->id)->totalUnits());
        $this->assertCount(1, Order::findById($order->id)->items);
    }

    public function test_load_quantities_should_fill_the_saved_items(): void
    {
        $order = $this->newOrder(4);
        $order->save();

        $order = Order::findById($order->id);
        $order->loadQuantities();

        $this->assertEquals(4, $order->quantityOf($this->product->id));
    }

    public function test_destroy_should_remove_order_and_items(): void
    {
        $order = $this->newOrder(4);
        $order->save();

        $order->destroy();

        $this->assertCount(0, Order::all());
        $this->assertEquals(10, $this->production->available());
    }
}
