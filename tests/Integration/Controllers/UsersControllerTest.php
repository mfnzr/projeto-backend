<?php

namespace Tests\Integration\Controllers;

use App\Models\User;

class UsersControllerTest extends ControllerTestCase
{
    private User $admin;
    private User $attendant;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = new User([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => '123456',
            'password_confirmation' => '123456',
            'role' => 'admin'
        ]);
        $this->admin->save();
        $_SESSION['user']['id'] = $this->admin->id;

        $this->attendant = new User([
            'name' => 'Fulano',
            'email' => 'fulano@example.com',
            'password' => '123456',
            'password_confirmation' => '123456',
            'role' => 'attendant'
        ]);
        $this->attendant->save();
    }

    public function test_index_should_list_users(): void
    {
        $response = $this->get(
            action: 'index',
            controllerName: 'App\Controllers\UsersController'
        );

        $this->assertStringContainsString('admin@example.com', $response);
        $this->assertStringContainsString('fulano@example.com', $response);
        $this->assertStringContainsString('Novo usuário', $response);
    }

    public function test_index_should_paginate_users(): void
    {
        for ($i = 1; $i <= 9; $i++) {
            $user = new User([
                'name' => 'Atendente ' . $i,
                'email' => 'atendente' . $i . '@example.com',
                'password' => '123456',
                'password_confirmation' => '123456',
                'role' => 'attendant'
            ]);
            $user->save();
        }

        $response = $this->get(
            action: 'index',
            controllerName: 'App\Controllers\UsersController',
            params: ['page' => 2]
        );

        $this->assertStringContainsString('atendente9@example.com', $response);
        $this->assertStringNotContainsString('fulano@example.com', $response);
        $this->assertStringContainsString('Mostrando 11 - 11 de 11', $response);
    }

    public function test_show_should_render_the_user(): void
    {
        $response = $this->get(
            action: 'show',
            controllerName: 'App\Controllers\UsersController',
            params: ['id' => $this->attendant->id]
        );

        $this->assertStringContainsString('fulano@example.com', $response);
        $this->assertStringContainsString('Atendente', $response);
    }

    public function test_new_should_render_form_with_password_fields(): void
    {
        $response = $this->get(
            action: 'new',
            controllerName: 'App\Controllers\UsersController'
        );

        $this->assertStringContainsString('action="/admin/users"', $response);
        $this->assertStringContainsString('name="user[password]"', $response);
        $this->assertStringContainsString('name="user[password_confirmation]"', $response);
    }

    public function test_create_should_save_and_redirect(): void
    {
        $response = $this->post(
            action: 'create',
            controllerName: 'App\Controllers\UsersController',
            params: [
                'user' => [
                    'name' => 'Ciclano',
                    'email' => 'ciclano@example.com',
                    'role' => 'attendant',
                    'password' => '123456',
                    'password_confirmation' => '123456'
                ]
            ]
        );

        $this->assertEquals('Location: /admin/users', $response);
        $this->assertTrue(User::findByEmail('ciclano@example.com')->authenticate('123456'));
    }

    public function test_create_with_invalid_data_should_render_form_with_errors(): void
    {
        $response = $this->post(
            action: 'create',
            controllerName: 'App\Controllers\UsersController',
            params: [
                'user' => [
                    'name' => 'Ciclano',
                    'email' => 'email-invalido',
                    'role' => 'attendant',
                    'password' => '123456',
                    'password_confirmation' => '654321'
                ]
            ]
        );

        $this->assertStringContainsString('deve ser um e-mail válido!', $response);
        $this->assertStringContainsString('as senhas devem ser idênticas!', $response);
        $this->assertStringContainsString('value="Ciclano"', $response);
        $this->assertCount(2, User::all());
    }

    public function test_edit_should_render_form_without_password_fields(): void
    {
        $response = $this->get(
            action: 'edit',
            controllerName: 'App\Controllers\UsersController',
            params: ['id' => $this->attendant->id]
        );

        $this->assertStringContainsString('value="fulano@example.com"', $response);
        $this->assertStringContainsString('name="_method" value="PUT"', $response);
        $this->assertStringNotContainsString('name="user[password]"', $response);
    }

    public function test_update_should_save_and_redirect(): void
    {
        $response = $this->put(
            action: 'update',
            controllerName: 'App\Controllers\UsersController',
            params: [
                'id' => $this->attendant->id,
                'user' => ['name' => 'Fulano de Tal', 'email' => 'fulano@example.com', 'role' => 'admin']
            ]
        );

        $this->assertEquals('Location: /admin/users', $response);
        $this->assertEquals('Fulano de Tal', User::findById($this->attendant->id)->name);
        $this->assertTrue(User::findById($this->attendant->id)->isAdmin());
    }

    public function test_update_with_invalid_data_should_render_edit_form_with_errors(): void
    {
        $response = $this->put(
            action: 'update',
            controllerName: 'App\Controllers\UsersController',
            params: [
                'id' => $this->attendant->id,
                'user' => ['name' => '', 'email' => 'admin@example.com', 'role' => 'attendant']
            ]
        );

        $this->assertStringContainsString('não pode ser vazio!', $response);
        $this->assertStringContainsString('já existe um registro com esse dado', $response);
        $this->assertEquals('Fulano', User::findById($this->attendant->id)->name);
    }

    public function test_destroy_should_remove_and_redirect(): void
    {
        $response = $this->post(
            action: 'destroy',
            controllerName: 'App\Controllers\UsersController',
            params: ['id' => $this->attendant->id]
        );

        $this->assertEquals('Location: /admin/users', $response);
        $this->assertNull(User::findById($this->attendant->id));
    }

    public function test_destroy_should_not_remove_the_logged_user(): void
    {
        $response = $this->post(
            action: 'destroy',
            controllerName: 'App\Controllers\UsersController',
            params: ['id' => $this->admin->id]
        );

        $this->assertEquals('Location: /admin/users', $response);
        $this->assertNotNull(User::findById($this->admin->id));
    }
}
