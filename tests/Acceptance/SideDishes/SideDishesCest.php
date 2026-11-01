<?php

namespace Tests\Acceptance\SideDishes;

use App\Models\SideDish;
use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class SideDishesCest extends BaseAcceptanceCest
{
    public function adminCreatesSideDish(AcceptanceTester $page): void
    {
        $page->login('admin@example.com', '123456');
        $page->click('Gerenciar acompanhamentos');
        $page->click('Novo acompanhamento');

        $page->fillField('side_dish[name]', 'Maionese');
        $page->fillField('side_dish[price]', '12.50');
        $page->click('Salvar');

        $page->seeInCurrentUrl('/admin/side-dishes');
        $page->see('Acompanhamento criado com sucesso!');
        $page->see('R$ 12,50');
    }

    public function adminCannotCreateSideDishWithInvalidData(AcceptanceTester $page): void
    {
        $page->login('admin@example.com', '123456');
        $page->amOnPage('/admin/side-dishes/new');

        $page->fillField('side_dish[price]', '0');
        $page->click('Salvar');

        $page->see('Não foi possível criar o acompanhamento');
        $page->see('não pode ser vazio!');
        $page->see('deve ser um valor maior que zero!');
    }

    public function adminEditsSideDish(AcceptanceTester $page): void
    {
        $sideDish = new SideDish(['name' => 'Farofa', 'price' => '8.00']);
        $sideDish->save();

        $page->login('admin@example.com', '123456');
        $page->amOnPage('/admin/side-dishes/' . $sideDish->id . '/edit');

        $page->fillField('side_dish[name]', 'Farofa de bacon');
        $page->click('Salvar');

        $page->see('Acompanhamento atualizado com sucesso!');
        $page->see('Farofa de bacon');
    }

    public function adminCannotUpdateSideDishWithInvalidData(AcceptanceTester $page): void
    {
        $sideDish = new SideDish(['name' => 'Farofa', 'price' => '8.00']);
        $sideDish->save();

        $page->login('admin@example.com', '123456');
        $page->amOnPage('/admin/side-dishes/' . $sideDish->id . '/edit');

        $page->fillField('side_dish[name]', '');
        $page->click('Salvar');

        $page->see('Não foi possível atualizar o acompanhamento!');
        $page->see('não pode ser vazio!');
    }

    public function adminListsSideDishes(AcceptanceTester $page): void
    {
        $first = new SideDish(['name' => 'Farofa', 'price' => '8.00']);
        $first->save();
        $second = new SideDish(['name' => 'Vinagrete', 'price' => '7.00']);
        $second->save();

        $page->login('admin@example.com', '123456');
        $page->amOnPage('/admin/side-dishes');

        $page->see('Farofa');
        $page->see('Vinagrete');
    }

    public function adminPaginatesSideDishes(AcceptanceTester $page): void
    {
        for ($i = 1; $i <= 11; $i++) {
            $sideDish = new SideDish(['name' => 'Acompanhamento ' . $i, 'price' => '5.00']);
            $sideDish->save();
        }

        $page->login('admin@example.com', '123456');
        $page->amOnPage('/admin/side-dishes');

        $page->see('Mostrando 1 - 10 de 11');
        $page->click('Próxima');

        $page->seeInCurrentUrl('page=2');
        $page->see('Mostrando 11 - 11 de 11');
        $page->see('Acompanhamento 11');
    }

    public function adminDeletesSideDish(AcceptanceTester $page): void
    {
        $sideDish = new SideDish(['name' => 'Farofa', 'price' => '8.00']);
        $sideDish->save();

        $page->login('admin@example.com', '123456');
        $page->amOnPage('/admin/side-dishes');

        $page->click('button[title="Excluir"]');
        $page->acceptPopup();

        $page->see('Acompanhamento excluído com sucesso!');
        $page->see('Nenhum acompanhamento cadastrado.');
    }
}
