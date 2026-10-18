<?php

namespace Tests\Integration\Controllers;

use App\Models\Product;
use App\Models\User;

class ProductsControllerTest extends ControllerTestCase
{
    private Product $product;

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

        $this->product = new Product(['name' => 'Frango assado', 'description' => 'Na brasa']);
        $this->product->save();
    }

    public function test_index_should_list_products(): void
    {
        $other = new Product(['name' => 'Pernil']);
        $other->save();

        $response = $this->get(
            action: 'index',
            controllerName: 'App\Controllers\ProductsController'
        );

        $this->assertStringContainsString('Frango assado', $response);
        $this->assertStringContainsString('Pernil', $response);
        $this->assertStringContainsString('Novo produto', $response);
    }

    public function test_show_should_render_the_product(): void
    {
        $response = $this->get(
            action: 'show',
            controllerName: 'App\Controllers\ProductsController',
            params: ['id' => $this->product->id]
        );

        $this->assertStringContainsString('Frango assado', $response);
        $this->assertStringContainsString('Na brasa', $response);
    }

    public function test_new_should_render_empty_form(): void
    {
        $response = $this->get(
            action: 'new',
            controllerName: 'App\Controllers\ProductsController'
        );

        $this->assertStringContainsString('name="product[name]"', $response);
        $this->assertStringContainsString('action="/admin/products"', $response);
    }

    public function test_create_should_save_and_redirect(): void
    {
        $response = $this->post(
            action: 'create',
            controllerName: 'App\Controllers\ProductsController',
            params: ['product' => ['name' => 'Costela', 'description' => 'Bovina']]
        );

        $this->assertEquals('Location: /admin/products', $response);
        $this->assertCount(2, Product::all());
    }

    public function test_create_with_invalid_data_should_render_form_with_errors(): void
    {
        $response = $this->post(
            action: 'create',
            controllerName: 'App\Controllers\ProductsController',
            params: ['product' => ['name' => '', 'description' => 'Sem nome']]
        );

        $this->assertStringContainsString('não pode ser vazio!', $response);
        $this->assertStringContainsString('Sem nome', $response);
        $this->assertCount(1, Product::all());
    }

    public function test_edit_should_render_form_with_product_data(): void
    {
        $response = $this->get(
            action: 'edit',
            controllerName: 'App\Controllers\ProductsController',
            params: ['id' => $this->product->id]
        );

        $this->assertStringContainsString('value="Frango assado"', $response);
        $this->assertStringContainsString('name="_method" value="PUT"', $response);
    }

    public function test_update_should_save_and_redirect(): void
    {
        $response = $this->put(
            action: 'update',
            controllerName: 'App\Controllers\ProductsController',
            params: [
                'id' => $this->product->id,
                'product' => ['name' => 'Frango recheado', 'description' => 'Com farofa']
            ]
        );

        $this->assertEquals('Location: /admin/products', $response);
        $this->assertEquals('Frango recheado', Product::findById($this->product->id)->name);
    }

    public function test_update_with_invalid_data_should_render_edit_form_with_errors(): void
    {
        $response = $this->put(
            action: 'update',
            controllerName: 'App\Controllers\ProductsController',
            params: [
                'id' => $this->product->id,
                'product' => ['name' => '', 'description' => 'Na brasa']
            ]
        );

        $this->assertStringContainsString('não pode ser vazio!', $response);
        $this->assertStringContainsString('name="_method" value="PUT"', $response);
        $this->assertEquals('Frango assado', Product::findById($this->product->id)->name);
    }

    public function test_destroy_should_remove_and_redirect(): void
    {
        $response = $this->post(
            action: 'destroy',
            controllerName: 'App\Controllers\ProductsController',
            params: ['id' => $this->product->id]
        );

        $this->assertEquals('Location: /admin/products', $response);
        $this->assertCount(0, Product::all());
    }
}
