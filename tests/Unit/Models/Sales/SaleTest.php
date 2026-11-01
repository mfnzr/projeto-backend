<?php

namespace Tests\Unit\Models\Sales;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Production;
use App\Models\Sale;
use Tests\TestCase;

class SaleTest extends TestCase
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
            'production_date' => date('Y-m-d'),
            'quantity' => '10',
            'price_per_kg' => '50.00'
        ]);
        $this->production->save();
    }

    private function newSale(mixed $quantity, mixed $amount): Sale
    {
        $sale = new Sale([
            'payment_method' => 'cash',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $sale->setItems([$this->product->id => ['quantity' => $quantity, 'amount' => $amount]]);

        return $sale;
    }

    public function test_should_create_sale_and_calculate_the_weight(): void
    {
        $sale = $this->newSale('2', '75.00');

        $this->assertTrue($sale->save());
        $this->assertCount(1, $sale->items);
        $this->assertEquals('1.500', $sale->items[0]->weight);
        $this->assertEquals(75.0, $sale->total());
        $this->assertEquals('Dinheiro', $sale->paymentMethodName());
    }

    public function test_should_not_create_sale_without_payment_method(): void
    {
        $sale = $this->newSale('2', '75.00');
        $sale->payment_method = '';

        $this->assertFalse($sale->save());
        $this->assertEquals('escolha a forma de pagamento!', $sale->errors('payment_method'));
    }

    public function test_should_not_create_sale_without_products(): void
    {
        $sale = $this->newSale('', '');

        $this->assertFalse($sale->save());
        $this->assertEquals('escolha pelo menos um produto!', $sale->errors('items'));
    }

    public function test_should_not_create_sale_without_scale_amount(): void
    {
        $sale = $this->newSale('2', '');

        $this->assertFalse($sale->save());
        $this->assertEquals('o valor da balança deve ser maior que zero!', $sale->errors('item_' . $this->product->id));
    }

    public function test_should_not_create_sale_with_more_than_available(): void
    {
        $sale = $this->newSale('11', '500.00');

        $this->assertFalse($sale->save());
        $this->assertEquals('só tem 10 disponível!', $sale->errors('item_' . $this->product->id));
    }

    public function test_counter_sale_should_lower_the_available(): void
    {
        $sale = $this->newSale('3', '120.00');
        $sale->save();

        $this->assertEquals(7, $this->production->available());
    }

    public function test_sale_from_order_should_not_lower_the_available_again(): void
    {
        $customer = new Customer(['name' => 'Maria', 'phone' => '42999990000']);
        $customer->save();

        $order = new Order([
            'customer_id' => $customer->id,
            'status' => 'ordered',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $order->setQuantities([$this->product->id => 4]);
        $order->save();

        $this->assertEquals(6, $this->production->available());

        $sale = $this->newSale(4, '160.00');
        $sale->order_id = $order->id;

        $this->assertTrue($sale->save());
        $this->assertEquals(6, $this->production->available());
    }

    public function test_update_should_change_amount_and_recalculate_weight(): void
    {
        $sale = $this->newSale('2', '75.00');
        $sale->save();

        $sale = Sale::findById($sale->id);
        $sale->loadItems();
        $sale->setAmounts([$this->product->id => ['amount' => '100.00']]);

        $this->assertTrue($sale->save());
        $this->assertEquals('2.000', Sale::findById($sale->id)->items[0]->weight);
    }

    public function test_destroy_should_remove_sale_and_give_back_the_units(): void
    {
        $sale = $this->newSale('3', '120.00');
        $sale->save();

        $sale->destroy();

        $this->assertCount(0, Sale::all());
        $this->assertEquals(10, $this->production->available());
    }

    public function test_empty_order_id_should_become_null(): void
    {
        $sale = new Sale(['order_id' => '']);

        $this->assertNull($sale->order_id);
        $this->assertFalse($sale->isFromOrder());
    }
}
