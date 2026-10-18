<?php

namespace Tests\Integration\Controllers;

use App\Models\User;

class AdminControllerTest extends ControllerTestCase
{
    public function test_render_admin_page(): void
    {
        $admin = new User([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => '123456',
            'password_confirmation' => '123456',
            'role' => 'admin'
        ]);
        $admin->save();
        $_SESSION['user']['id'] = $admin->id;

        $response = $this->get(
            action: 'index',
            controllerName: 'App\Controllers\AdminController'
        );

        $this->assertStringContainsString('área administrativa', $response);
        $this->assertStringContainsString('Painel Admin', $response);
        $this->assertStringContainsString('Gerenciar produtos', $response);
    }
}
