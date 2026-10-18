<?php

namespace Tests\Integration\Controllers;

use App\Models\User;

class AuthenticationsControllerTest extends ControllerTestCase
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

        $this->attendant = new User([
            'name' => 'Fulano',
            'email' => 'fulano@example.com',
            'password' => '123456',
            'password_confirmation' => '123456',
            'role' => 'attendant'
        ]);
        $this->attendant->save();
    }

    public function test_new_should_render_login_form(): void
    {
        $response = $this->get(
            action: 'new',
            controllerName: 'App\Controllers\AuthenticationsController'
        );

        $this->assertStringContainsString('name="user[email]"', $response);
        $this->assertStringContainsString('name="user[password]"', $response);
        $this->assertStringContainsString('Entrar', $response);
    }

    public function test_authenticate_admin_should_redirect_to_admin_area(): void
    {
        $response = $this->post(
            action: 'authenticate',
            controllerName: 'App\Controllers\AuthenticationsController',
            params: ['user' => ['email' => 'admin@example.com', 'password' => '123456']]
        );

        $this->assertEquals('Location: /admin/painel', $response);
        $this->assertEquals($this->admin->id, $_SESSION['user']['id']);
    }

    public function test_authenticate_attendant_should_redirect_to_user_area(): void
    {
        $response = $this->post(
            action: 'authenticate',
            controllerName: 'App\Controllers\AuthenticationsController',
            params: ['user' => ['email' => 'fulano@example.com', 'password' => '123456']]
        );

        $this->assertEquals('Location: /', $response);
        $this->assertEquals($this->attendant->id, $_SESSION['user']['id']);
    }

    public function test_authenticate_with_wrong_password_should_redirect_to_login(): void
    {
        $response = $this->post(
            action: 'authenticate',
            controllerName: 'App\Controllers\AuthenticationsController',
            params: ['user' => ['email' => 'fulano@example.com', 'password' => 'wrong']]
        );

        $this->assertEquals('Location: /login', $response);
        $this->assertArrayNotHasKey('id', $_SESSION['user'] ?? []);
    }

    public function test_authenticate_with_unknown_email_should_redirect_to_login(): void
    {
        $response = $this->post(
            action: 'authenticate',
            controllerName: 'App\Controllers\AuthenticationsController',
            params: ['user' => ['email' => 'nobody@example.com', 'password' => '123456']]
        );

        $this->assertEquals('Location: /login', $response);
        $this->assertArrayNotHasKey('id', $_SESSION['user'] ?? []);
    }

    public function test_destroy_should_logout_and_redirect_to_login(): void
    {
        $_SESSION['user']['id'] = $this->attendant->id;

        $response = $this->post(
            action: 'destroy',
            controllerName: 'App\Controllers\AuthenticationsController'
        );

        $this->assertEquals('Location: /login', $response);
        $this->assertArrayNotHasKey('id', $_SESSION['user']);
    }
}
