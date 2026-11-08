<?php

namespace Tests\Acceptance\Products;

use App\Models\Product;
use App\Models\ProductPhoto;
use App\Services\ProductPhotoUpload;
use Tests\Acceptance\BaseAcceptanceCest;
use PHPUnit\Framework\Assert;
use Tests\Support\AcceptanceTester;

class ProductPhotosCest extends BaseAcceptanceCest
{
    private Product $product;

    public function _before(AcceptanceTester $page): void
    {
        parent::_before($page);

        $this->product = new Product(['name' => 'Frango assado']);
        $this->product->save();
    }

    public function _after(AcceptanceTester $page): void
    {
        foreach (ProductPhoto::all() as $photo) {
            $upload = new ProductPhotoUpload($photo);
            if (is_file($upload->filePath())) {
                unlink($upload->filePath());
            }
        }

        parent::_after($page);
    }

    public function adminUploadsAndSeesPhoto(AcceptanceTester $page): void
    {
        $page->login('admin@example.com', '123456');
        $page->amOnPage('/admin/products/' . $this->product->id);
        $page->see('Nenhuma foto cadastrada.');

        $page->attachFile('photo', '../../files/avatar_test.jpg');
        $page->click('Enviar foto');

        $page->see('Foto enviada com sucesso!');
        $page->dontSee('Nenhuma foto cadastrada.');

        $photo = ProductPhoto::all()[0];
        $page->seeElement('img[src="' . $photo->url() . '"]');

        $upload = new ProductPhotoUpload($photo);
        Assert::assertFileExists($upload->filePath());
    }

    public function adminCannotUploadPhpScriptAsPhoto(AcceptanceTester $page): void
    {
        $page->login('admin@example.com', '123456');
        $page->amOnPage('/admin/products/' . $this->product->id);

        $page->attachFile('photo', '../../files/malicious.php.jpg');
        $page->click('Enviar foto');

        $page->see('Não foi possível enviar a foto: a imagem deve ser JPEG ou PNG!');
        $page->see('Nenhuma foto cadastrada.');
    }

    public function adminRemovesPhoto(AcceptanceTester $page): void
    {
        $page->login('admin@example.com', '123456');
        $page->amOnPage('/admin/products/' . $this->product->id);
        $page->attachFile('photo', '../../files/photo_test.png');
        $page->click('Enviar foto');

        $upload = new ProductPhotoUpload(ProductPhoto::all()[0]);
        $filePath = $upload->filePath();

        $page->click('button[title="Excluir foto"]');
        $page->acceptPopup();

        $page->see('Foto excluída com sucesso!');
        $page->see('Nenhuma foto cadastrada.');
        Assert::assertFileDoesNotExist($filePath);
    }
}
