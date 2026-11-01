<?php

namespace Tests\Acceptance\Orders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Production;
use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class OrdersCest extends BaseAcceptanceCest
{
    private Customer $customer;
    private Product $product;

    public function _before(AcceptanceTester $page): void
    {
        parent::_before($page);

        $this->customer = new Customer(['name' => 'Maria', 'phone' => '42999990000']);
        $this->customer->save();

        $this->product = new Product(['name' => 'Frango assado']);
        $this->product->save();

        $production = new Production([
            'product_id' => $this->product->id,
            'production_date' => date('Y-m-d'),
            'quantity' => '20',
            'price_per_kg' => '45.90'
        ]);
        $production->save();
    }

    private function createOrder(int $quantity): Order
    {
        $order = new Order([
            'customer_id' => $this->customer->id,
            'status' => 'ordered',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $order->setQuantities([$this->product->id => $quantity]);
        $order->save();

        return $order;
    }

    public function attendantCreatesOrder(AcceptanceTester $page): void
    {
        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/orders');
        $page->click('Novo pedido');

        $page->selectOption('order[customer_id]', 'Maria - 42999990000');
        $page->fillField('quantities[' . $this->product->id . ']', '3');
        $page->click('Salvar');

        $page->seeInCurrentUrl('/orders');
        $page->see('Pedido criado com sucesso!');
        $page->see('Encomendado');
    }

    public function attendantCannotCreateOrderWithInvalidData(AcceptanceTester $page): void
    {
        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/orders/new');

        $page->fillField('quantities[' . $this->product->id . ']', '25');
        $page->click('Salvar');

        $page->see('Não foi possível criar o pedido');
        $page->see('não pode ser vazio!');
        $page->see('só tem 20 disponível!');
    }

    public function attendantEditsOrder(AcceptanceTester $page): void
    {
        $order = $this->createOrder(2);

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/orders/' . $order->id . '/edit');

        $page->fillField('quantities[' . $this->product->id . ']', '5');
        $page->click('Salvar');

        $page->see('Pedido atualizado com sucesso!');
        $page->amOnPage('/orders/' . $order->id);
        $page->see('5');
    }

    public function attendantCannotUpdateOrderWithInvalidData(AcceptanceTester $page): void
    {
        $order = $this->createOrder(2);

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/orders/' . $order->id . '/edit');

        $page->fillField('quantities[' . $this->product->id . ']', '-1');
        $page->click('Salvar');

        $page->see('Não foi possível atualizar o pedido!');
        $page->see('deve ser um número inteiro maior que zero!');
    }

    public function attendantListsOrders(AcceptanceTester $page): void
    {
        $this->createOrder(2);
        $this->createOrder(3);

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/orders');

        $page->see('Maria');
        $page->seeNumberOfElements('tbody tr', 2);
    }

    public function attendantPaginatesOrders(AcceptanceTester $page): void
    {
        for ($i = 1; $i <= 11; $i++) {
            $this->createOrder(1);
        }

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/orders');

        $page->see('Mostrando 1 - 10 de 11');
        $page->click('Próxima');

        $page->seeInCurrentUrl('page=2');
        $page->see('Mostrando 11 - 11 de 11');
    }

    public function attendantCancelsOrder(AcceptanceTester $page): void
    {
        $order = $this->createOrder(2);

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/orders/' . $order->id);

        $page->click('Cancelar pedido');
        $page->acceptPopup();

        $page->see('Pedido cancelado e unidades devolvidas ao disponível');
        $page->see('Cancelado');
    }

    public function attendantDeletesOrder(AcceptanceTester $page): void
    {
        $this->createOrder(2);

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/orders');

        $page->click('button[title="Excluir"]');
        $page->acceptPopup();

        $page->see('Pedido excluído com sucesso!');
        $page->see('Nenhum pedido registrado.');
    }
}
