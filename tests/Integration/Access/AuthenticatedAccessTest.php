<?php

namespace Tests\Integration\Access;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderSideDish;
use App\Models\Product;
use App\Models\Production;
use App\Models\SideDish;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthenticatedAccessTest extends TestCase
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
    public static function authenticatedRoutes(): array
    {
        return [
            'customers list' => ['/customers'],
            'new customer form' => ['/customers/new'],
            'productions list' => ['/productions'],
            'new production form' => ['/productions/new'],
            'purchases list' => ['/purchases'],
            'new purchase form' => ['/purchases/new'],
            'orders list' => ['/orders'],
            'new order form' => ['/orders/new'],
            'sales list' => ['/sales'],
            'new sale form' => ['/sales/new'],
        ];
    }

    #[DataProvider('authenticatedRoutes')]
    public function test_guest_is_redirected_to_login(string $uri): void
    {
        $response = $this->client->get($uri);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    #[DataProvider('authenticatedRoutes')]
    public function test_attendant_can_access(string $uri): void
    {
        $this->login('fulano@example.com');

        $response = $this->client->get($uri);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_guest_cannot_create_customer(): void
    {
        $response = $this->client->post('/customers', [
            'form_params' => ['customer' => ['name' => 'Maria', 'phone' => '42999990000']]
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
        $this->assertCount(0, Customer::all());
    }

    private function createOrder(): Order
    {
        $customer = new Customer(['name' => 'Maria', 'phone' => '42999990000']);
        $customer->save();

        $product = new Product(['name' => 'Frango assado']);
        $product->save();

        $production = new Production([
            'product_id' => $product->id,
            'production_date' => date('Y-m-d'),
            'quantity' => '10',
            'price_per_kg' => '45.90'
        ]);
        $production->save();

        $order = new Order([
            'customer_id' => $customer->id,
            'status' => 'ordered',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $order->setQuantities([$product->id => 2]);
        $order->save();

        return $order;
    }

    public function test_guest_cannot_add_side_dish_to_order(): void
    {
        $order = $this->createOrder();
        $sideDish = new SideDish(['name' => 'Farofa', 'price' => '8.00']);
        $sideDish->save();

        $response = $this->client->post('/orders/' . $order->id . '/side-dishes', [
            'headers' => ['Accept' => 'application/json'],
            'form_params' => ['side_dish_id' => $sideDish->id]
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
        $this->assertCount(0, OrderSideDish::all());
    }

    public function test_guest_cannot_remove_side_dish_from_order(): void
    {
        $order = $this->createOrder();
        $sideDish = new SideDish(['name' => 'Farofa', 'price' => '8.00']);
        $sideDish->save();
        $orderSideDish = new OrderSideDish(['order_id' => $order->id, 'side_dish_id' => $sideDish->id]);
        $orderSideDish->save();

        $response = $this->client->post('/orders/' . $order->id . '/side-dishes/' . $sideDish->id, [
            'headers' => ['Accept' => 'application/json'],
            'form_params' => ['_method' => 'DELETE']
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
        $this->assertCount(1, OrderSideDish::all());
    }
}
