<?php

namespace Tests\Unit\Models\ProductPhotos;

use App\Models\Product;
use App\Models\ProductPhoto;
use Core\Database\Database;
use PDOException;
use Tests\TestCase;

class ProductPhotoTest extends TestCase
{
    private Product $product;

    public function setUp(): void
    {
        parent::setUp();

        $this->product = new Product(['name' => 'Frango assado']);
        $this->product->save();
    }

    public function test_should_create_new_photo(): void
    {
        $photo = new ProductPhoto(['product_id' => $this->product->id, 'file_name' => 'frango.jpg']);

        $this->assertTrue($photo->save());
        $this->assertCount(1, ProductPhoto::all());
    }

    public function test_should_not_create_photo_without_product(): void
    {
        $photo = new ProductPhoto(['file_name' => 'frango.jpg']);

        $this->assertFalse($photo->save());
        $this->assertEquals('não pode ser vazio!', $photo->errors('product_id'));
    }

    public function test_should_not_create_photo_for_a_product_that_does_not_exist(): void
    {
        $photo = new ProductPhoto(['product_id' => 999, 'file_name' => 'frango.jpg']);

        $this->assertFalse($photo->save());
        $this->assertEquals('produto não encontrado!', $photo->errors('product_id'));
        $this->assertCount(0, ProductPhoto::all());
    }

    public function test_should_not_create_photo_without_file_name(): void
    {
        $photo = new ProductPhoto(['product_id' => $this->product->id]);

        $this->assertFalse($photo->save());
        $this->assertEquals('não pode ser vazio!', $photo->errors('file_name'));
    }

    public function test_database_should_reject_photo_for_a_product_that_does_not_exist(): void
    {
        $this->expectException(PDOException::class);

        Database::getDatabaseConn()->exec(
            "INSERT INTO product_photos (product_id, file_name) VALUES (999, 'frango.jpg')"
        );
    }

    public function test_url_should_point_to_the_uploads_folder(): void
    {
        $photo = new ProductPhoto(['product_id' => $this->product->id, 'file_name' => 'frango.jpg']);

        $this->assertEquals('/assets/uploads/frango.jpg', $photo->url());
    }

    public function test_photo_should_belong_to_the_product(): void
    {
        $photo = new ProductPhoto(['product_id' => $this->product->id, 'file_name' => 'frango.jpg']);
        $photo->save();

        $this->assertEquals('Frango assado', $photo->product->name);
    }

    public function test_product_should_have_many_photos(): void
    {
        $first = new ProductPhoto(['product_id' => $this->product->id, 'file_name' => 'frango1.jpg']);
        $first->save();
        $second = new ProductPhoto(['product_id' => $this->product->id, 'file_name' => 'frango2.jpg']);
        $second->save();

        $this->assertCount(2, $this->product->photos);
    }

    public function test_destroy_product_should_remove_its_photos_from_the_database(): void
    {
        $photo = new ProductPhoto(['product_id' => $this->product->id, 'file_name' => 'frango.jpg']);
        $photo->save();

        $this->product->destroy();

        $this->assertCount(0, ProductPhoto::all());
    }
}
