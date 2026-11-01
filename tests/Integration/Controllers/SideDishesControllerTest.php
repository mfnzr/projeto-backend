<?php

namespace Tests\Integration\Controllers;

use App\Models\SideDish;
use App\Models\User;

class SideDishesControllerTest extends ControllerTestCase
{
    private SideDish $sideDish;

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

        $this->sideDish = new SideDish(['name' => 'Farofa', 'price' => '8.00']);
        $this->sideDish->save();
    }

    public function test_index_should_list_side_dishes(): void
    {
        $response = $this->get(
            action: 'index',
            controllerName: 'App\Controllers\SideDishesController'
        );

        $this->assertStringContainsString('Farofa', $response);
        $this->assertStringContainsString('R$ 8,00', $response);
        $this->assertStringContainsString('Novo acompanhamento', $response);
    }

    public function test_index_should_paginate_side_dishes(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $sideDish = new SideDish(['name' => 'Acompanhamento ' . $i, 'price' => '5.00']);
            $sideDish->save();
        }

        $response = $this->get(
            action: 'index',
            controllerName: 'App\Controllers\SideDishesController',
            params: ['page' => 2]
        );

        $this->assertStringContainsString('Acompanhamento 10', $response);
        $this->assertStringNotContainsString('Farofa', $response);
        $this->assertStringContainsString('Mostrando 11 - 11 de 11', $response);
    }

    public function test_show_should_render_the_side_dish(): void
    {
        $response = $this->get(
            action: 'show',
            controllerName: 'App\Controllers\SideDishesController',
            params: ['id' => $this->sideDish->id]
        );

        $this->assertStringContainsString('Farofa', $response);
    }

    public function test_create_should_save_and_redirect(): void
    {
        $response = $this->post(
            action: 'create',
            controllerName: 'App\Controllers\SideDishesController',
            params: ['side_dish' => ['name' => 'Maionese', 'price' => '12.00']]
        );

        $this->assertEquals('Location: /admin/side-dishes', $response);
        $this->assertCount(2, SideDish::all());
    }

    public function test_create_with_invalid_data_should_render_form_with_errors(): void
    {
        $response = $this->post(
            action: 'create',
            controllerName: 'App\Controllers\SideDishesController',
            params: ['side_dish' => ['name' => '', 'price' => '0']]
        );

        $this->assertStringContainsString('não pode ser vazio!', $response);
        $this->assertStringContainsString('deve ser um valor maior que zero!', $response);
        $this->assertCount(1, SideDish::all());
    }

    public function test_edit_should_render_form_with_side_dish_data(): void
    {
        $response = $this->get(
            action: 'edit',
            controllerName: 'App\Controllers\SideDishesController',
            params: ['id' => $this->sideDish->id]
        );

        $this->assertStringContainsString('value="Farofa"', $response);
        $this->assertStringContainsString('name="_method" value="PUT"', $response);
    }

    public function test_update_should_save_and_redirect(): void
    {
        $response = $this->put(
            action: 'update',
            controllerName: 'App\Controllers\SideDishesController',
            params: [
                'id' => $this->sideDish->id,
                'side_dish' => ['name' => 'Farofa de bacon', 'price' => '10.00']
            ]
        );

        $this->assertEquals('Location: /admin/side-dishes', $response);
        $this->assertEquals('Farofa de bacon', SideDish::findById($this->sideDish->id)->name);
    }

    public function test_update_with_invalid_data_should_render_edit_form_with_errors(): void
    {
        $response = $this->put(
            action: 'update',
            controllerName: 'App\Controllers\SideDishesController',
            params: [
                'id' => $this->sideDish->id,
                'side_dish' => ['name' => 'Farofa', 'price' => 'abc']
            ]
        );

        $this->assertStringContainsString('deve ser um valor maior que zero!', $response);
        $this->assertEquals('8.00', SideDish::findById($this->sideDish->id)->price);
    }

    public function test_destroy_should_remove_and_redirect(): void
    {
        $response = $this->post(
            action: 'destroy',
            controllerName: 'App\Controllers\SideDishesController',
            params: ['id' => $this->sideDish->id]
        );

        $this->assertEquals('Location: /admin/side-dishes', $response);
        $this->assertCount(0, SideDish::all());
    }
}
