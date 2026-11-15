<?php

namespace Tests\Acceptance\Orders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderSideDish;
use App\Models\Product;
use App\Models\Production;
use App\Models\SideDish;
use PHPUnit\Framework\Assert;
use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class OrderSideDishesCest extends BaseAcceptanceCest
{
    private Order $order;
    private SideDish $farofa;

    public function _before(AcceptanceTester $page): void
    {
        parent::_before($page);

        $customer = new Customer(['name' => 'Maria', 'phone' => '42999990000']);
        $customer->save();

        $product = new Product(['name' => 'Frango assado']);
        $product->save();

        $production = new Production([
            'product_id' => $product->id,
            'production_date' => date('Y-m-d'),
            'quantity' => '20',
            'price_per_kg' => '45.90'
        ]);
        $production->save();

        $this->order = new Order([
            'customer_id' => $customer->id,
            'status' => 'ordered',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $this->order->setQuantities([$product->id => 2]);
        $this->order->save();

        $this->farofa = new SideDish(['name' => 'Farofa', 'price' => '8.00']);
        $this->farofa->save();
    }

    private function addFarofa(): void
    {
        $orderSideDish = new OrderSideDish([
            'order_id' => $this->order->id,
            'side_dish_id' => $this->farofa->id
        ]);
        $orderSideDish->save();
    }

    public function attendantAddsSideDishWithAjax(AcceptanceTester $page): void
    {
        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/orders/' . $this->order->id);
        $page->see('Nenhum acompanhamento.');

        // marca a página: se ela recarregar, a marca some
        $page->executeJS('window.semRecarregar = true;');

        $page->selectOption('#side_dish_select', 'Farofa');
        $page->click('Adicionar');

        $page->waitForText('Farofa - R$ 8,00', 5, '#side_dish_list');
        $page->dontSee('Nenhum acompanhamento.');
        Assert::assertTrue($page->executeJS('return window.semRecarregar === true;'));
        Assert::assertCount(1, OrderSideDish::all());
    }

    public function attendantSeesSideDishesOfTheOrder(AcceptanceTester $page): void
    {
        $this->addFarofa();

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/orders/' . $this->order->id);
        $page->see('Farofa - R$ 8,00', '#side_dish_list');

        $page->amOnPage('/orders');
        $page->see('Farofa', 'tbody');
    }

    public function attendantRemovesSideDishWithAjax(AcceptanceTester $page): void
    {
        $this->addFarofa();

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/orders/' . $this->order->id);
        $page->executeJS('window.semRecarregar = true;');

        $page->click('#side_dish_' . $this->farofa->id . ' .remove-side-dish');

        $page->waitForElementNotVisible('#side_dish_' . $this->farofa->id, 5);
        $page->see('Nenhum acompanhamento.');
        Assert::assertTrue($page->executeJS('return window.semRecarregar === true;'));
        Assert::assertCount(0, OrderSideDish::all());
    }

    public function attendantCannotAddTheSameSideDishTwice(AcceptanceTester $page): void
    {
        $this->addFarofa();

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/orders/' . $this->order->id);

        $page->selectOption('#side_dish_select', 'Farofa');
        $page->click('Adicionar');

        $page->waitForText('esse acompanhamento já está no pedido!', 5, '#side_dish_message');
        Assert::assertCount(1, OrderSideDish::all());
    }

    public function adminCannotDeleteSideDishUsedInOrders(AcceptanceTester $page): void
    {
        $this->addFarofa();

        $page->login('admin@example.com', '123456');
        $page->amOnPage('/admin/side-dishes');

        $page->click('button[title="Excluir"]');
        $page->acceptPopup();

        $page->see('Não é possível excluir um acompanhamento que está em pedidos');
        $page->see('Farofa');
        Assert::assertCount(1, SideDish::all());
    }
}
