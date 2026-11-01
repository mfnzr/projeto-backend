<?php

namespace Tests\Integration\Controllers;

use App\Models\Product;
use App\Models\Production;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\User;

class ReportsControllerTest extends ControllerTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $admin = new User([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => '123456',
            'password_confirmation' => '123456',
            'role' => 'admin'
        ]);
        $admin->save();
        $_SESSION['user']['id'] = $admin->id;

        $product = new Product(['name' => 'Frango assado']);
        $product->save();

        $production = new Production([
            'product_id' => $product->id,
            'production_date' => date('Y-m-d'),
            'quantity' => '20',
            'price_per_kg' => '50.00'
        ]);
        $production->save();

        $sale = new Sale(['payment_method' => 'cash', 'created_at' => date('Y-m-d H:i:s')]);
        $sale->setItems([$product->id => ['quantity' => 4, 'amount' => '200.00']]);
        $sale->save();

        $purchase = new Purchase([
            'product_id' => $product->id,
            'purchase_date' => date('Y-m-d'),
            'quantity' => '10',
            'total_amount' => '200.00',
            'price_per_kg' => '20.00'
        ]);
        $purchase->save();
    }

    public function test_index_should_show_sales_purchases_and_margin(): void
    {
        $response = $this->get(
            action: 'index',
            controllerName: 'App\Controllers\ReportsController',
            params: ['from' => date('Y-m-d'), 'to' => date('Y-m-d')]
        );

        $this->assertStringContainsString('<strong>Unidades:</strong> 4', $response);
        $this->assertStringContainsString('<strong>Unidades:</strong> 10', $response);
        $this->assertStringContainsString('<strong>Total:</strong> R$ 0,00', $response);
        $this->assertStringContainsString('R$ 30,00', $response);
        $this->assertStringNotContainsString('sem vendas ou compras no período', $response);
    }

    public function test_index_with_period_without_movements_should_show_zeros(): void
    {
        $response = $this->get(
            action: 'index',
            controllerName: 'App\Controllers\ReportsController',
            params: ['from' => '2020-01-01', 'to' => '2020-01-31']
        );

        $this->assertStringContainsString('<strong>Total:</strong> R$ 0,00', $response);
        $this->assertStringContainsString('sem vendas ou compras no período', $response);
    }

    public function test_index_with_invalid_dates_should_use_the_current_month(): void
    {
        $response = $this->get(
            action: 'index',
            controllerName: 'App\Controllers\ReportsController',
            params: ['from' => '31/12/2026', 'to' => 'ontem']
        );

        $this->assertStringContainsString('value="' . date('Y-m-01') . '"', $response);
        $this->assertStringContainsString('value="' . date('Y-m-d') . '"', $response);
    }
}
