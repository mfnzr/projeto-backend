<?php

namespace Tests\Integration\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;

class PurchasesControllerTest extends ControllerTestCase
{
    private Product $product;
    private Purchase $purchase;

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

        $this->product = new Product(['name' => 'Costela bovina']);
        $this->product->save();

        $this->purchase = new Purchase([
            'product_id' => $this->product->id,
            'purchase_date' => '2026-11-01',
            'quantity' => '10',
            'total_amount' => '500.00',
            'price_per_kg' => '25.00'
        ]);
        $this->purchase->save();
    }

    public function test_index_should_list_purchases_with_weight(): void
    {
        $response = $this->get(
            action: 'index',
            controllerName: 'App\Controllers\PurchasesController'
        );

        $this->assertStringContainsString('Costela bovina', $response);
        $this->assertStringContainsString('R$ 500,00', $response);
        $this->assertStringContainsString('20,000 kg', $response);
    }

    public function test_index_should_paginate_purchases(): void
    {
        for ($day = 10; $day <= 19; $day++) {
            $purchase = new Purchase([
                'product_id' => $this->product->id,
                'purchase_date' => '2026-10-' . $day,
                'quantity' => '5',
                'total_amount' => '100.00'
            ]);
            $purchase->save();
        }

        $response = $this->get(
            action: 'index',
            controllerName: 'App\Controllers\PurchasesController',
            params: ['page' => 2]
        );

        $this->assertStringContainsString('19/10/2026', $response);
        $this->assertStringNotContainsString('01/11/2026', $response);
        $this->assertStringContainsString('Mostrando 11 - 11 de 11', $response);
    }

    public function test_show_should_render_the_purchase(): void
    {
        $response = $this->get(
            action: 'show',
            controllerName: 'App\Controllers\PurchasesController',
            params: ['id' => $this->purchase->id]
        );

        $this->assertStringContainsString('Costela bovina', $response);
        $this->assertStringContainsString('20,000 kg', $response);
    }

    public function test_create_should_save_and_redirect(): void
    {
        $response = $this->post(
            action: 'create',
            controllerName: 'App\Controllers\PurchasesController',
            params: [
                'purchase' => [
                    'product_id' => $this->product->id,
                    'purchase_date' => '2026-11-02',
                    'quantity' => '8',
                    'total_amount' => '320.00',
                    'price_per_kg' => ''
                ]
            ]
        );

        $this->assertEquals('Location: /purchases', $response);
        $this->assertCount(2, Purchase::all());
    }

    public function test_create_with_invalid_data_should_render_form_with_errors(): void
    {
        $response = $this->post(
            action: 'create',
            controllerName: 'App\Controllers\PurchasesController',
            params: [
                'purchase' => [
                    'product_id' => '',
                    'purchase_date' => '2026-11-02',
                    'quantity' => '8',
                    'total_amount' => '',
                    'price_per_kg' => ''
                ]
            ]
        );

        $this->assertStringContainsString('não pode ser vazio!', $response);
        $this->assertStringContainsString('deve ser um valor maior que zero!', $response);
        $this->assertCount(1, Purchase::all());
    }

    public function test_update_should_save_and_redirect(): void
    {
        $response = $this->put(
            action: 'update',
            controllerName: 'App\Controllers\PurchasesController',
            params: [
                'id' => $this->purchase->id,
                'purchase' => [
                    'product_id' => (string) $this->product->id,
                    'purchase_date' => '2026-11-01',
                    'quantity' => '12',
                    'total_amount' => '600.00',
                    'price_per_kg' => ''
                ]
            ]
        );

        $this->assertEquals('Location: /purchases', $response);
        $purchase = Purchase::findById($this->purchase->id);
        $this->assertEquals(12, $purchase->quantity);
        $this->assertNull($purchase->price_per_kg);
    }

    public function test_update_with_invalid_data_should_render_edit_form_with_errors(): void
    {
        $response = $this->put(
            action: 'update',
            controllerName: 'App\Controllers\PurchasesController',
            params: [
                'id' => $this->purchase->id,
                'purchase' => [
                    'product_id' => (string) $this->product->id,
                    'purchase_date' => '2026-02-30',
                    'quantity' => '12',
                    'total_amount' => '600.00',
                    'price_per_kg' => ''
                ]
            ]
        );

        $this->assertStringContainsString('deve ser uma data válida!', $response);
        $this->assertStringContainsString('name="_method" value="PUT"', $response);
        $this->assertEquals(10, Purchase::findById($this->purchase->id)->quantity);
    }

    public function test_destroy_should_remove_and_redirect(): void
    {
        $response = $this->post(
            action: 'destroy',
            controllerName: 'App\Controllers\PurchasesController',
            params: ['id' => $this->purchase->id]
        );

        $this->assertEquals('Location: /purchases', $response);
        $this->assertCount(0, Purchase::all());
    }
}
