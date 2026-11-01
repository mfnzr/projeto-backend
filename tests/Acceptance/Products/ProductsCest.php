<?php

namespace Tests\Acceptance\Products;

use App\Models\Product;
use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class ProductsCest extends BaseAcceptanceCest
{
    public function adminCreatesProduct(AcceptanceTester $page): void
    {
        $page->login('admin@example.com', '123456');
        $page->click('Gerenciar produtos');
        $page->click('Novo produto');

        $page->fillField('product[name]', 'Frango assado');
        $page->fillField('product[description]', 'Frango inteiro na brasa');
        $page->click('Salvar');

        $page->seeInCurrentUrl('/admin/products');
        $page->see('Produto criado com sucesso!');
        $page->see('Frango assado');
    }

    public function adminCannotCreateProductWithoutName(AcceptanceTester $page): void
    {
        $page->login('admin@example.com', '123456');
        $page->amOnPage('/admin/products/new');

        $page->click('Salvar');

        $page->see('não pode ser vazio!');
    }

    public function adminEditsProduct(AcceptanceTester $page): void
    {
        $product = new Product(['name' => 'Pernil']);
        $product->save();

        $page->login('admin@example.com', '123456');
        $page->amOnPage('/admin/products/' . $product->id . '/edit');

        $page->fillField('product[name]', 'Pernil suíno');
        $page->click('Salvar');

        $page->see('Produto atualizado com sucesso!');
        $page->see('Pernil suíno');
    }

    public function adminCannotUpdateProductWithInvalidData(AcceptanceTester $page): void
    {
        $product = new Product(['name' => 'Pernil']);
        $product->save();

        $page->login('admin@example.com', '123456');
        $page->amOnPage('/admin/products/' . $product->id . '/edit');

        $page->fillField('product[name]', '');
        $page->click('Salvar');

        $page->see('Não foi possível atualizar o produto!');
        $page->see('não pode ser vazio!');
    }

    public function adminListsProducts(AcceptanceTester $page): void
    {
        $first = new Product(['name' => 'Pernil']);
        $first->save();
        $second = new Product(['name' => 'Costela bovina']);
        $second->save();

        $page->login('admin@example.com', '123456');
        $page->amOnPage('/admin/products');

        $page->see('Pernil');
        $page->see('Costela bovina');
    }

    public function adminPaginatesProducts(AcceptanceTester $page): void
    {
        for ($i = 1; $i <= 11; $i++) {
            $product = new Product(['name' => 'Produto ' . $i]);
            $product->save();
        }

        $page->login('admin@example.com', '123456');
        $page->amOnPage('/admin/products');

        $page->see('Mostrando 1 - 10 de 11');
        $page->click('Próxima');

        $page->seeInCurrentUrl('page=2');
        $page->see('Mostrando 11 - 11 de 11');
        $page->see('Produto 11');
    }

    public function adminDeletesProduct(AcceptanceTester $page): void
    {
        $product = new Product(['name' => 'Linguiça']);
        $product->save();

        $page->login('admin@example.com', '123456');
        $page->amOnPage('/admin/products');

        $page->click('button[title="Excluir"]');
        $page->acceptPopup();

        $page->see('Produto excluido com sucesso!');
        $page->see('Nenhum produto cadastrado.');
    }

    public function attendantCannotManageProducts(AcceptanceTester $page): void
    {
        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/admin/products');

        $page->dontSeeInCurrentUrl('/admin/products');
        $page->see('Você deve ser administrador para acessar essa página');
    }
}
