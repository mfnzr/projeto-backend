<?php

namespace Tests\Integration\Controllers;

use App\Models\Product;
use App\Models\ProductPhoto;
use App\Models\User;
use App\Services\ProductPhotoUpload;
use Core\Constants\Constants;

class ProductPhotosControllerTest extends ControllerTestCase
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

        $this->product = new Product(['name' => 'Frango assado']);
        $this->product->save();
    }

    public function tearDown(): void
    {
        foreach (ProductPhoto::all() as $photo) {
            $upload = new ProductPhotoUpload($photo);
            if (is_file($upload->filePath())) {
                unlink($upload->filePath());
            }
        }

        unset($_FILES['photo']);
        parent::tearDown();
    }

    /**
     * Cria uma foto no banco e copia o arquivo para a pasta de uploads
     */
    private function createPhoto(): ProductPhoto
    {
        $photo = new ProductPhoto([
            'product_id' => $this->product->id,
            'file_name' => 'product_' . $this->product->id . '_teste.jpg'
        ]);
        $photo->save();

        $upload = new ProductPhotoUpload($photo);
        copy(Constants::rootPath()->join('tests/files/avatar_test.jpg'), $upload->filePath());

        return $photo;
    }

    public function test_create_should_redirect_when_product_does_not_exist(): void
    {
        $response = $this->post(
            action: 'create',
            controllerName: 'App\Controllers\ProductPhotosController',
            params: ['product_id' => 999]
        );

        $this->assertEquals('Location: /admin/products', $response);
        $this->assertCount(0, ProductPhoto::all());
    }

    public function test_create_without_file_should_not_save(): void
    {
        $response = $this->post(
            action: 'create',
            controllerName: 'App\Controllers\ProductPhotosController',
            params: ['product_id' => $this->product->id]
        );

        $this->assertEquals('Location: /admin/products/' . $this->product->id, $response);
        $this->assertEquals('Não foi possível enviar a foto: selecione uma imagem!', $_SESSION['flash']['danger']);
        $this->assertCount(0, ProductPhoto::all());
    }

    public function test_create_with_php_script_should_not_save(): void
    {
        $path = (string) Constants::rootPath()->join('tests/files/malicious.php.jpg');
        $_FILES['photo'] = [
            'name' => 'malicious.php.jpg',
            'tmp_name' => $path,
            'size' => filesize($path),
            'error' => UPLOAD_ERR_OK,
        ];

        $response = $this->post(
            action: 'create',
            controllerName: 'App\Controllers\ProductPhotosController',
            params: ['product_id' => $this->product->id]
        );

        $this->assertEquals('Location: /admin/products/' . $this->product->id, $response);
        $this->assertEquals('Não foi possível enviar a foto: a imagem deve ser JPEG ou PNG!', $_SESSION['flash']['danger']);
        $this->assertCount(0, ProductPhoto::all());
    }

    public function test_destroy_should_remove_the_photo_and_the_file(): void
    {
        $photo = $this->createPhoto();
        $upload = new ProductPhotoUpload($photo);

        $response = $this->post(
            action: 'destroy',
            controllerName: 'App\Controllers\ProductPhotosController',
            params: ['product_id' => $this->product->id, 'id' => $photo->id]
        );

        $this->assertEquals('Location: /admin/products/' . $this->product->id, $response);
        $this->assertCount(0, ProductPhoto::all());
        $this->assertFileDoesNotExist($upload->filePath());
    }

    public function test_destroy_should_not_remove_photo_of_another_product(): void
    {
        $photo = $this->createPhoto();
        $other = new Product(['name' => 'Pernil']);
        $other->save();

        $this->post(
            action: 'destroy',
            controllerName: 'App\Controllers\ProductPhotosController',
            params: ['product_id' => $other->id, 'id' => $photo->id]
        );

        $this->assertEquals('Foto não encontrada', $_SESSION['flash']['danger']);
        $this->assertCount(1, ProductPhoto::all());
    }

    public function test_show_product_should_list_the_photos(): void
    {
        $photo = $this->createPhoto();

        $response = $this->get(
            action: 'show',
            controllerName: 'App\Controllers\ProductsController',
            params: ['id' => $this->product->id]
        );

        $this->assertStringContainsString('src="' . $photo->url() . '"', $response);
        $this->assertStringContainsString('enctype="multipart/form-data"', $response);
    }

    public function test_destroy_product_should_remove_the_photo_files(): void
    {
        $photo = $this->createPhoto();
        $upload = new ProductPhotoUpload($photo);

        $this->post(
            action: 'destroy',
            controllerName: 'App\Controllers\ProductsController',
            params: ['id' => $this->product->id]
        );

        $this->assertCount(0, Product::all());
        $this->assertCount(0, ProductPhoto::all());
        $this->assertFileDoesNotExist($upload->filePath());
    }
}
