<?php

namespace Tests\Acceptance\Customers;

use App\Models\Customer;
use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class CustomersCest extends BaseAcceptanceCest
{
    public function attendantCreatesCustomer(AcceptanceTester $page): void
    {
        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/customers');
        $page->click('Novo cliente');

        $page->fillField('customer[name]', 'Maria');
        $page->fillField('customer[phone]', '42999990000');
        $page->click('Salvar');

        $page->seeInCurrentUrl('/customers');
        $page->see('Cliente cadastrado com sucesso!');
        $page->see('42999990000');
    }

    public function attendantCannotCreateCustomerWithInvalidData(AcceptanceTester $page): void
    {
        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/customers/new');

        $page->fillField('customer[name]', 'Maria');
        $page->fillField('customer[phone]', '9999');
        $page->click('Salvar');

        $page->see('Não foi possível cadastrar o cliente');
        $page->see('deve ter 10 ou 11 números, com DDD!');
    }

    public function attendantEditsCustomer(AcceptanceTester $page): void
    {
        $customer = new Customer(['name' => 'Maria', 'phone' => '42999990000']);
        $customer->save();

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/customers/' . $customer->id . '/edit');

        $page->fillField('customer[name]', 'Maria Silva');
        $page->click('Salvar');

        $page->see('Cliente atualizado com sucesso!');
        $page->see('Maria Silva');
    }

    public function attendantCannotUpdateCustomerWithInvalidData(AcceptanceTester $page): void
    {
        $customer = new Customer(['name' => 'Maria', 'phone' => '42999990000']);
        $customer->save();

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/customers/' . $customer->id . '/edit');

        $page->fillField('customer[phone]', '');
        $page->click('Salvar');

        $page->see('Não foi possível atualizar o cliente!');
        $page->see('não pode ser vazio!');
    }

    public function attendantListsCustomers(AcceptanceTester $page): void
    {
        $first = new Customer(['name' => 'Maria', 'phone' => '42999990000']);
        $first->save();
        $second = new Customer(['name' => 'João', 'phone' => '4232220000']);
        $second->save();

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/customers');

        $page->see('Maria');
        $page->see('João');
    }

    public function attendantPaginatesCustomers(AcceptanceTester $page): void
    {
        for ($i = 10; $i <= 20; $i++) {
            $customer = new Customer(['name' => 'Cliente ' . $i, 'phone' => '429999900' . $i]);
            $customer->save();
        }

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/customers');

        $page->see('Mostrando 1 - 10 de 11');
        $page->click('Próxima');

        $page->seeInCurrentUrl('page=2');
        $page->see('Mostrando 11 - 11 de 11');
        $page->see('Cliente 20');
    }

    public function attendantDeletesCustomer(AcceptanceTester $page): void
    {
        $customer = new Customer(['name' => 'Maria', 'phone' => '42999990000']);
        $customer->save();

        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/customers');

        $page->click('button[title="Excluir"]');
        $page->acceptPopup();

        $page->see('Cliente excluído com sucesso!');
        $page->see('Nenhum cliente cadastrado.');
    }
}
