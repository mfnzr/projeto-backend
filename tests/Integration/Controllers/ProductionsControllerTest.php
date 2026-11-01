<?php

namespace Tests\Integration\Controllers;

use App\Models\Product;
use App\Models\Production;
use App\Models\User;

class ProductionsControllerTest extends ControllerTestCase
{
    private Product $product;
    private Production $production;

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
            'production_date' => '2026-11-01',
            'quantity' => '20',
            'price_per_kg' => '45.90'
        ]);
        $this->production->save();
    }

    public function test_index_should_list_productions(): void
    {
        $response = $this->get(
            action: 'index',
            controllerName: 'App\Controllers\ProductionsController'
        );

        $this->assertStringContainsString('Frango assado', $response);
        $this->assertStringContainsString('01/11/2026', $response);
        $this->assertStringContainsString('R$ 45,90', $response);
    }

    public function test_index_should_paginate_productions(): void
    {
        for ($day = 10; $day <= 19; $day++) {
            $production = new Production([
                'product_id' => $this->product->id,
                'production_date' => '2026-10-' . $day,
                'quantity' => '10',
                'price_per_kg' => '40.00'
            ]);
            $production->save();
        }

        $response = $this->get(
            action: 'index',
            controllerName: 'App\Controllers\ProductionsController',
            params: ['page' => 2]
        );

        $this->assertStringContainsString('19/10/2026', $response);
        $this->assertStringNotContainsString('01/11/2026', $response);
        $this->assertStringContainsString('Mostrando 11 - 11 de 11', $response);
    }

    public function test_new_should_render_form_with_products_and_today_date(): void
    {
        $response = $this->get(
            action: 'new',
            controllerName: 'App\Controllers\ProductionsController'
        );

        $this->assertStringContainsString('Frango assado', $response);
        $this->assertStringContainsString('value="' . date('Y-m-d') . '"', $response);
    }

    public function test_create_should_save_and_redirect(): void
    {
        $response = $this->post(
            action: 'create',
            controllerName: 'App\Controllers\ProductionsController',
            params: [
                'production' => [
                    'product_id' => $this->product->id,
                    'production_date' => '2026-11-02',
                    'quantity' => '15',
                    'price_per_kg' => '46.90'
                ]
            ]
        );

        $this->assertEquals('Location: /productions', $response);
        $this->assertCount(2, Production::all());
    }

    public function test_create_with_invalid_data_should_render_form_with_errors(): void
    {
        $response = $this->post(
            action: 'create',
            controllerName: 'App\Controllers\ProductionsController',
            params: [
                'production' => [
                    'product_id' => $this->product->id,
                    'production_date' => '2026-11-01',
                    'quantity' => '15',
                    'price_per_kg' => '46.90'
                ]
            ]
        );

        $this->assertStringContainsString('já existe produção desse produto nessa data!', $response);
        $this->assertCount(1, Production::all());
    }

    public function test_update_should_save_and_redirect(): void
    {
        $response = $this->put(
            action: 'update',
            controllerName: 'App\Controllers\ProductionsController',
            params: [
                'id' => $this->production->id,
                'production' => [
                    'product_id' => (string) $this->product->id,
                    'production_date' => '2026-11-01',
                    'quantity' => '30',
                    'price_per_kg' => '45.90'
                ]
            ]
        );

        $this->assertEquals('Location: /productions', $response);
        $this->assertEquals(30, Production::findById($this->production->id)->quantity);
    }

    public function test_update_with_invalid_data_should_render_edit_form_with_errors(): void
    {
        $response = $this->put(
            action: 'update',
            controllerName: 'App\Controllers\ProductionsController',
            params: [
                'id' => $this->production->id,
                'production' => [
                    'product_id' => (string) $this->product->id,
                    'production_date' => '2026-11-01',
                    'quantity' => '-5',
                    'price_per_kg' => '45.90'
                ]
            ]
        );

        $this->assertStringContainsString('deve ser um número inteiro maior que zero!', $response);
        $this->assertStringContainsString('name="_method" value="PUT"', $response);
        $this->assertEquals(20, Production::findById($this->production->id)->quantity);
    }

    public function test_destroy_should_remove_and_redirect(): void
    {
        $response = $this->post(
            action: 'destroy',
            controllerName: 'App\Controllers\ProductionsController',
            params: ['id' => $this->production->id]
        );

        $this->assertEquals('Location: /productions', $response);
        $this->assertCount(0, Production::all());
    }
}
