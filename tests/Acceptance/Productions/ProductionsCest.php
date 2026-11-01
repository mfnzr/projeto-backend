<?php

namespace Tests\Acceptance\Productions;

use App\Models\Product;
use App\Models\Production;
use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class ProductionsCest extends BaseAcceptanceCest
{
    private Product $product;

    public function _before(AcceptanceTester $page): void
    {
        parent::_before($page);

        $this->product = new Product(['name' => 'Frango assado']);
        $this->product->save();
    }

    private function createProduction(string $date): Production
    {
        $production = new Production([
            'product_id' => $this->product->id,
            'production_date' => $date,
            'quantity' => '20',
            'price_per_kg' => '45.90'
        ]);
        $production->save();

        return $production;
    }

    public function attendantOpensTheDay(AcceptanceTester $page): void
    {
        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/productions');
        $page->click('Nova produção');

        $page->selectOption('production[product_id]', 'Frango assado');
        $page->fillField('production[quantity]', '20');
        $page->fillField('production[price_per_kg]', '45.90');
        $page->click('Salvar');

        $page->seeInCurrentUrl('/productions');
        $page->see('Produção registrada com sucesso!');
        $page->see(date('d/m/Y'));
        $page->see('R$ 45,90');
    }

    public function attendantCannotOpenTheDayWithInvalidData(AcceptanceTester $page): void
    {
        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/productions/new');

        $page->fillField('production[quantity]', '0');
        $page->click('Salvar');

        $page->see('Não foi possível registrar a produção');
        $page->see('não pode ser vazio!');
        $page->see('deve ser um número inteiro maior que zero!');
        $page->see('deve ser um valor maior que zero!');
    }

    public function attendantEditsProduction(AcceptanceTester $page): void
    {
        $production = $this->createProduction('2026-11-01');

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/productions/' . $production->id . '/edit');

        $page->fillField('production[quantity]', '35');
        $page->click('Salvar');

        $page->see('Produção atualizada com sucesso!');
        $page->see('35');
    }

    public function attendantCannotUpdateProductionWithInvalidData(AcceptanceTester $page): void
    {
        $production = $this->createProduction('2026-11-01');

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/productions/' . $production->id . '/edit');

        $page->fillField('production[price_per_kg]', '-10');
        $page->click('Salvar');

        $page->see('Não foi possível atualizar a produção!');
        $page->see('deve ser um valor maior que zero!');
    }

    public function attendantListsProductions(AcceptanceTester $page): void
    {
        $this->createProduction('2026-10-31');
        $this->createProduction('2026-11-01');

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/productions');

        $page->see('31/10/2026');
        $page->see('01/11/2026');
        $page->see('Frango assado');
    }

    public function attendantPaginatesProductions(AcceptanceTester $page): void
    {
        for ($day = 10; $day <= 20; $day++) {
            $this->createProduction('2026-10-' . $day);
        }

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/productions');

        $page->see('Mostrando 1 - 10 de 11');
        $page->click('Próxima');

        $page->seeInCurrentUrl('page=2');
        $page->see('Mostrando 11 - 11 de 11');
        $page->see('20/10/2026');
    }

    public function attendantDeletesProduction(AcceptanceTester $page): void
    {
        $this->createProduction('2026-11-01');

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/productions');

        $page->click('button[title="Excluir"]');
        $page->acceptPopup();

        $page->see('Produção excluída com sucesso!');
        $page->see('Nenhuma produção registrada.');
    }
}
