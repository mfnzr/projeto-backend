<?php

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Models\ProductPhoto;
use App\Services\ProductPhotoUpload;
use Core\Constants\Constants;
use Tests\TestCase;

class ProductPhotoUploadTest extends TestCase
{
    private Product $product;

    public function setUp(): void
    {
        parent::setUp();

        $this->product = new Product(['name' => 'Frango assado']);
        $this->product->save();
    }

    public function tearDown(): void
    {
        // Apaga só os arquivos criados pelo teste
        foreach (ProductPhoto::all() as $photo) {
            $upload = new ProductPhotoUpload($photo);
            if (is_file($upload->filePath())) {
                unlink($upload->filePath());
            }
        }

        parent::tearDown();
    }

    /**
     * Fora de um upload de verdade o move_uploaded_file não funciona,
     * então no teste trocamos ele por um copy.
     */
    private function newUpload(ProductPhoto $photo): ProductPhotoUpload
    {
        $upload = $this->getMockBuilder(ProductPhotoUpload::class)
            ->setConstructorArgs([$photo])
            ->onlyMethods(['moveFile'])
            ->getMock();

        $upload->method('moveFile')->willReturnCallback(function (string $from, string $to) {
            return copy($from, $to);
        });

        return $upload;
    }

    /**
     * Monta um item igual ao que o PHP coloca em $_FILES
     * @return array<string, mixed>
     */
    private function file(string $name): array
    {
        $path = (string) Constants::rootPath()->join('tests/files/' . $name);

        return [
            'name' => $name,
            'tmp_name' => $path,
            'size' => filesize($path),
            'error' => UPLOAD_ERR_OK,
        ];
    }

    public function test_should_save_a_jpeg_photo(): void
    {
        $photo = new ProductPhoto(['product_id' => $this->product->id]);
        $upload = $this->newUpload($photo);

        $this->assertTrue($upload->save($this->file('avatar_test.jpg')));
        $this->assertCount(1, $this->product->photos);
        $this->assertStringEndsWith('.jpg', $photo->file_name);
        $this->assertFileExists($upload->filePath());
    }

    public function test_should_save_a_png_photo(): void
    {
        $photo = new ProductPhoto(['product_id' => $this->product->id]);
        $upload = $this->newUpload($photo);

        $this->assertTrue($upload->save($this->file('photo_test.png')));
        $this->assertStringEndsWith('.png', $photo->file_name);
        $this->assertFileExists($upload->filePath());
    }

    public function test_should_not_use_the_name_sent_by_the_user(): void
    {
        $photo = new ProductPhoto(['product_id' => $this->product->id]);
        $upload = $this->newUpload($photo);

        $upload->save($this->file('avatar_test.jpg'));

        $this->assertNotEquals('avatar_test.jpg', $photo->file_name);
        $this->assertStringStartsWith('product_' . $this->product->id . '_', $photo->file_name);
    }

    public function test_should_not_save_without_a_file(): void
    {
        $photo = new ProductPhoto(['product_id' => $this->product->id]);
        $upload = $this->newUpload($photo);

        $this->assertFalse($upload->save([]));
        $this->assertEquals('selecione uma imagem!', $photo->errors('file'));
        $this->assertCount(0, ProductPhoto::all());
    }

    public function test_should_not_save_a_file_with_upload_error(): void
    {
        $file = $this->file('avatar_test.jpg');
        $file['error'] = UPLOAD_ERR_NO_FILE;

        $photo = new ProductPhoto(['product_id' => $this->product->id]);
        $upload = $this->newUpload($photo);

        $this->assertFalse($upload->save($file));
        $this->assertEquals('selecione uma imagem!', $photo->errors('file'));
    }

    public function test_should_not_save_a_file_bigger_than_2mb(): void
    {
        $file = $this->file('avatar_test.jpg');
        $file['size'] = 3 * 1024 * 1024;

        $photo = new ProductPhoto(['product_id' => $this->product->id]);
        $upload = $this->newUpload($photo);

        $this->assertFalse($upload->save($file));
        $this->assertEquals('a imagem deve ter no máximo 2 MB!', $photo->errors('file'));
        $this->assertCount(0, ProductPhoto::all());
    }

    public function test_should_not_save_a_php_script_renamed_to_jpg(): void
    {
        $photo = new ProductPhoto(['product_id' => $this->product->id]);
        $upload = $this->newUpload($photo);

        $this->assertFalse($upload->save($this->file('malicious.php.jpg')));
        $this->assertEquals('a imagem deve ser JPEG ou PNG!', $photo->errors('file'));
        $this->assertCount(0, ProductPhoto::all());
    }

    public function test_should_not_save_a_photo_for_a_product_that_does_not_exist(): void
    {
        $photo = new ProductPhoto(['product_id' => 999]);
        $upload = $this->newUpload($photo);

        $this->assertFalse($upload->save($this->file('avatar_test.jpg')));
        $this->assertEquals('produto não encontrado!', $photo->errors('file'));
        $this->assertCount(0, ProductPhoto::all());
    }

    public function test_remove_should_delete_the_file_and_the_record(): void
    {
        $photo = new ProductPhoto(['product_id' => $this->product->id]);
        $upload = $this->newUpload($photo);
        $upload->save($this->file('avatar_test.jpg'));
        $filePath = $upload->filePath();

        $this->assertTrue($upload->remove());
        $this->assertFileDoesNotExist($filePath);
        $this->assertCount(0, ProductPhoto::all());
    }
}
