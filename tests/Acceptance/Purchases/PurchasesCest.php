<?php

namespace Tests\Acceptance\Purchases;

use App\Models\Product;
use App\Models\Purchase;
use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class PurchasesCest extends BaseAcceptanceCest
{
    private Product $product;

    public function _before(AcceptanceTester $page): void
    {
        parent::_before($page);

        $this->product = new Product(['name' => 'Costela bovina']);
        $this->product->save();
    }

    private function createPurchase(string $date): Purchase
    {
        $purchase = new Purchase([
            'product_id' => $this->product->id,
            'purchase_date' => $date,
            'quantity' => '10',
            'total_amount' => '500.00',
            'price_per_kg' => '25.00'
        ]);
        $purchase->save();

        return $purchase;
    }

    public function attendantRegistersPurchase(AcceptanceTester $page): void
    {
        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/purchases');
        $page->click('Nova compra');

        $page->selectOption('purchase[product_id]', 'Costela bovina');
        $page->fillField('purchase[quantity]', '10');
        $page->fillField('purchase[total_amount]', '500');
        $page->fillField('purchase[price_per_kg]', '25');
        $page->click('Salvar');

        $page->seeInCurrentUrl('/purchases');
        $page->see('Compra registrada com sucesso!');
        $page->see('R$ 500,00');
        $page->see('20,000 kg');
    }

    public function attendantCannotRegisterPurchaseWithInvalidData(AcceptanceTester $page): void
    {
        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/purchases/new');

        $page->selectOption('purchase[product_id]', 'Costela bovina');
        $page->fillField('purchase[quantity]', '10');
        $page->click('Salvar');

        $page->see('Não foi possível registrar a compra');
        $page->see('deve ser um valor maior que zero!');
    }

    public function attendantEditsPurchase(AcceptanceTester $page): void
    {
        $purchase = $this->createPurchase('2026-11-01');

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/purchases/' . $purchase->id . '/edit');

        $page->fillField('purchase[total_amount]', '750');
        $page->click('Salvar');

        $page->see('Compra atualizada com sucesso!');
        $page->see('R$ 750,00');
    }

    public function attendantCannotUpdatePurchaseWithInvalidData(AcceptanceTester $page): void
    {
        $purchase = $this->createPurchase('2026-11-01');

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/purchases/' . $purchase->id . '/edit');

        $page->fillField('purchase[quantity]', '0');
        $page->click('Salvar');

        $page->see('Não foi possível atualizar a compra!');
        $page->see('deve ser um número inteiro maior que zero!');
    }

    public function attendantListsPurchases(AcceptanceTester $page): void
    {
        $this->createPurchase('2026-10-31');
        $this->createPurchase('2026-11-01');

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/purchases');

        $page->see('31/10/2026');
        $page->see('01/11/2026');
        $page->see('Costela bovina');
    }

    public function attendantPaginatesPurchases(AcceptanceTester $page): void
    {
        for ($day = 10; $day <= 20; $day++) {
            $this->createPurchase('2026-10-' . $day);
        }

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/purchases');

        $page->see('Mostrando 1 - 10 de 11');
        $page->click('Próxima');

        $page->seeInCurrentUrl('page=2');
        $page->see('Mostrando 11 - 11 de 11');
        $page->see('20/10/2026');
    }

    public function attendantDeletesPurchase(AcceptanceTester $page): void
    {
        $this->createPurchase('2026-11-01');

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/purchases');

        $page->click('button[title="Excluir"]');
        $page->acceptPopup();

        $page->see('Compra excluída com sucesso!');
        $page->see('Nenhuma compra registrada.');
    }
}
