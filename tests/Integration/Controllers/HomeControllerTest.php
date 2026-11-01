<?php

namespace Tests\Integration\Controllers;

use App\Models\User;

class HomeControllerTest extends ControllerTestCase
{
    public function test_render_home_page(): void
    {
        $user = new User([
            'name' => 'Fulano',
            'email' => 'fulano@example.com',
            'password' => '123456',
            'password_confirmation' => '123456',
            'role' => 'attendant'
        ]);
        $user->save();
        $_SESSION['user']['id'] = $user->id;

        $response = $this->get(
            action: 'index',
            controllerName: 'App\Controllers\HomeController'
        );

        $this->assertMatchesRegularExpression('/<h1>\s*Home Page\s*<\/h1>/', $response);
        $this->assertStringContainsString('Fulano', $response);
        $this->assertStringContainsString('área do usuário', $response);
        $this->assertStringNotContainsString('Painel Admin', $response);
    }
}
