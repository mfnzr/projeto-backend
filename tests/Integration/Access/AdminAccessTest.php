<?php

namespace Tests\Integration\Access;

use App\Models\Product;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    private Client $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client([
            'allow_redirects' => false,
            'base_uri' => 'http://web:8080',
            'cookies' => new CookieJar()
        ]);

        $admin = new User([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => '123456',
            'password_confirmation' => '123456',
            'role' => 'admin'
        ]);
        $admin->save();

        $attendant = new User([
            'name' => 'Fulano',
            'email' => 'fulano@example.com',
            'password' => '123456',
            'password_confirmation' => '123456',
            'role' => 'attendant'
        ]);
        $attendant->save();
    }

    private function login(string $email): void
    {
        $this->client->post('/login', [
            'form_params' => ['user' => ['email' => $email, 'password' => '123456']]
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function adminRoutes(): array
    {
        return [
            'admin panel' => ['/admin/painel'],
            'products list' => ['/admin/products'],
            'new product form' => ['/admin/products/new'],
        ];
    }

    #[DataProvider('adminRoutes')]
    public function test_guest_cannot_access_admin_routes(string $uri): void
    {
        $response = $this->client->get($uri);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertNotEquals($uri, $response->getHeaderLine('Location'));
    }

    #[DataProvider('adminRoutes')]
    public function test_attendant_cannot_access_admin_routes(string $uri): void
    {
        $this->login('fulano@example.com');

        $response = $this->client->get($uri);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/', $response->getHeaderLine('Location'));
    }

    #[DataProvider('adminRoutes')]
    public function test_admin_can_access_admin_routes(string $uri): void
    {
        $this->login('admin@example.com');

        $response = $this->client->get($uri);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_attendant_cannot_create_product(): void
    {
        $this->login('fulano@example.com');

        $response = $this->client->post('/admin/products', [
            'form_params' => ['product' => ['name' => 'Produto proibido']]
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertCount(0, Product::all());
    }

    public function test_attendant_can_access_user_area(): void
    {
        $this->login('fulano@example.com');

        $response = $this->client->get('/');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('área do usuário', (string) $response->getBody());
    }
}
