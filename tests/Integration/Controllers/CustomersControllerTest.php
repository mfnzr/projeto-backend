<?php

namespace Tests\Integration\Controllers;

use App\Models\Customer;
use App\Models\User;

class CustomersControllerTest extends ControllerTestCase
{
    private Customer $customer;

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
    }

    public function test_index_should_list_customers(): void
    {
        $response = $this->get(
            action: 'index',
            controllerName: 'App\Controllers\CustomersController'
        );

        $this->assertStringContainsString('Maria', $response);
        $this->assertStringContainsString('42999990000', $response);
        $this->assertStringContainsString('Novo cliente', $response);
    }

    public function test_index_should_paginate_customers(): void
    {
        for ($i = 10; $i <= 19; $i++) {
            $customer = new Customer(['name' => 'Cliente ' . $i, 'phone' => '429999900' . $i]);
            $customer->save();
        }

        $response = $this->get(
            action: 'index',
            controllerName: 'App\Controllers\CustomersController',
            params: ['page' => 2]
        );

        $this->assertStringContainsString('Cliente 19', $response);
        $this->assertStringNotContainsString('Maria', $response);
        $this->assertStringContainsString('Mostrando 11 - 11 de 11', $response);
    }

    public function test_show_should_render_the_customer(): void
    {
        $response = $this->get(
            action: 'show',
            controllerName: 'App\Controllers\CustomersController',
            params: ['id' => $this->customer->id]
        );

        $this->assertStringContainsString('Maria', $response);
        $this->assertStringContainsString('42999990000', $response);
    }

    public function test_create_should_save_and_redirect(): void
    {
        $response = $this->post(
            action: 'create',
            controllerName: 'App\Controllers\CustomersController',
            params: ['customer' => ['name' => 'João', 'phone' => '4232220000']]
        );

        $this->assertEquals('Location: /customers', $response);
        $this->assertCount(2, Customer::all());
    }

    public function test_create_with_invalid_data_should_render_form_with_errors(): void
    {
        $response = $this->post(
            action: 'create',
            controllerName: 'App\Controllers\CustomersController',
            params: ['customer' => ['name' => 'João', 'phone' => '123']]
        );

        $this->assertStringContainsString('deve ter 10 ou 11 números, com DDD!', $response);
        $this->assertStringContainsString('value="João"', $response);
        $this->assertCount(1, Customer::all());
    }

    public function test_edit_should_render_form_with_customer_data(): void
    {
        $response = $this->get(
            action: 'edit',
            controllerName: 'App\Controllers\CustomersController',
            params: ['id' => $this->customer->id]
        );

        $this->assertStringContainsString('value="Maria"', $response);
        $this->assertStringContainsString('name="_method" value="PUT"', $response);
    }

    public function test_update_should_save_and_redirect(): void
    {
        $response = $this->put(
            action: 'update',
            controllerName: 'App\Controllers\CustomersController',
            params: [
                'id' => $this->customer->id,
                'customer' => ['name' => 'Maria Silva', 'phone' => '42999990000']
            ]
        );

        $this->assertEquals('Location: /customers', $response);
        $this->assertEquals('Maria Silva', Customer::findById($this->customer->id)->name);
    }

    public function test_update_with_invalid_data_should_render_edit_form_with_errors(): void
    {
        $response = $this->put(
            action: 'update',
            controllerName: 'App\Controllers\CustomersController',
            params: [
                'id' => $this->customer->id,
                'customer' => ['name' => '', 'phone' => '42999990000']
            ]
        );

        $this->assertStringContainsString('não pode ser vazio!', $response);
        $this->assertEquals('Maria', Customer::findById($this->customer->id)->name);
    }

    public function test_destroy_should_remove_and_redirect(): void
    {
        $response = $this->post(
            action: 'destroy',
            controllerName: 'App\Controllers\CustomersController',
            params: ['id' => $this->customer->id]
        );

        $this->assertEquals('Location: /customers', $response);
        $this->assertCount(0, Customer::all());
    }
}
