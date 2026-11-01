<?php

namespace Tests\Acceptance\Sales;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Production;
use App\Models\Sale;
use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class SalesCest extends BaseAcceptanceCest
{
    private Product $product;

    public function _before(AcceptanceTester $page): void
    {
        parent::_before($page);

        $this->product = new Product(['name' => 'Frango assado']);
        $this->product->save();

        $production = new Production([
            'product_id' => $this->product->id,
            'production_date' => date('Y-m-d'),
            'quantity' => '20',
            'price_per_kg' => '50.00'
        ]);
        $production->save();
    }

    private function createSale(): Sale
    {
        $sale = new Sale(['payment_method' => 'cash', 'created_at' => date('Y-m-d H:i:s')]);
        $sale->setItems([$this->product->id => ['quantity' => 1, 'amount' => '50.00']]);
        $sale->save();

        return $sale;
    }

    public function attendantMakesCounterSale(AcceptanceTester $page): void
    {
        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/sales');
        $page->click('Nova venda');

        $page->fillField('items[' . $this->product->id . '][quantity]', '2');
        $page->fillField('items[' . $this->product->id . '][amount]', '75');
        $page->checkOption('#payment_pix');
        $page->click('Salvar');

        $page->seeInCurrentUrl('/sales');
        $page->see('Venda registrada com sucesso!');
        $page->see('Balcão');
        $page->see('R$ 75,00');
    }

    public function attendantCannotMakeSaleWithInvalidData(AcceptanceTester $page): void
    {
        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/sales/new');

        $page->fillField('items[' . $this->product->id . '][quantity]', '30');
        $page->fillField('items[' . $this->product->id . '][amount]', '75');
        $page->click('Salvar');

        $page->see('Não foi possível registrar a venda');
        $page->see('só tem 20 disponível!');
        $page->see('escolha a forma de pagamento!');
    }

    public function attendantRegistersPickupOfAnOrder(AcceptanceTester $page): void
    {
        $customer = new Customer(['name' => 'Maria', 'phone' => '42999990000']);
        $customer->save();

        $order = new Order([
            'customer_id' => $customer->id,
            'status' => 'ordered',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $order->setQuantities([$this->product->id => 2]);
        $order->save();

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/orders/' . $order->id);
        $page->click('Registrar retirada');

        $page->see('Retirada do pedido #' . $order->id);
        $page->fillField('items[' . $this->product->id . '][amount]', '100');
        $page->checkOption('#payment_card');
        $page->click('Salvar');

        $page->see('Venda registrada com sucesso!');
        $page->see('Pedido #' . $order->id);
        $page->amOnPage('/orders/' . $order->id);
        $page->see('Retirado');
    }

    public function attendantEditsSale(AcceptanceTester $page): void
    {
        $sale = $this->createSale();

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/sales/' . $sale->id . '/edit');

        $page->fillField('items[' . $this->product->id . '][amount]', '62.50');
        $page->click('Salvar');

        $page->see('Venda atualizada com sucesso!');
        $page->see('R$ 62,50');
    }

    public function attendantCannotUpdateSaleWithInvalidData(AcceptanceTester $page): void
    {
        $sale = $this->createSale();

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/sales/' . $sale->id . '/edit');

        $page->fillField('items[' . $this->product->id . '][amount]', '0');
        $page->click('Salvar');

        $page->see('Não foi possível atualizar a venda!');
        $page->see('o valor da balança deve ser maior que zero!');
    }

    public function attendantListsSales(AcceptanceTester $page): void
    {
        $this->createSale();
        $this->createSale();

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/sales');

        $page->see('Balcão');
        $page->seeNumberOfElements('tbody tr', 2);
    }

    public function attendantPaginatesSales(AcceptanceTester $page): void
    {
        for ($i = 1; $i <= 11; $i++) {
            $this->createSale();
        }

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/sales');

        $page->see('Mostrando 1 - 10 de 11');
        $page->click('Próxima');

        $page->seeInCurrentUrl('page=2');
        $page->see('Mostrando 11 - 11 de 11');
    }

    public function attendantDeletesSale(AcceptanceTester $page): void
    {
        $this->createSale();

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/sales');

        $page->click('button[title="Excluir"]');
        $page->acceptPopup();

        $page->see('Venda excluída com sucesso!');
        $page->see('Nenhuma venda registrada.');
    }
}
