<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use App\Models\Customer;
use Lib\FlashMessage;
use Core\Http\Request;

class CustomersController extends Controller
{
    public function index(Request $request): void
    {
        $title = 'Clientes';

        $page = (int) $request->getParam('page', 1);
        if ($page < 1) {
            $page = 1;
        }

        $paginator = Customer::paginate(page: $page, per_page: 10, route: 'customers.index');
        $customers = $paginator->registers();

        $this->render('customers/index', compact('title', 'customers', 'paginator'));
    }

    public function show(Request $request): void
    {
        $title = 'Exibe o cliente';
        $customer = Customer::findById($request->getParam('id'));

        if ($customer === null) {
            FlashMessage::danger("Cliente não encontrado");
            $this->redirectTo(route('customers.index'));
        }

        $this->render('customers/show', compact('title', 'customer'));
    }

    public function new(): void
    {
        $title = 'Cadastro de clientes';
        $customer = new Customer();
        $this->render('customers/new', compact('title', 'customer'));
    }

    public function create(Request $request): void
    {
        $title = 'Cadastro de clientes';
        $newCustomer = $request->getParam('customer');
        $customer = new Customer($newCustomer);

        if ($customer->save()) {
            FlashMessage::success('Cliente cadastrado com sucesso!');
            $this->redirectTo(route('customers.index'));
        } else {
            FlashMessage::danger('Não foi possível cadastrar o cliente');
            $this->render('customers/new', compact('title', 'customer'));
        }
    }

    public function edit(Request $request): void
    {
        $title = 'Edição de clientes';
        $customer = Customer::findById($request->getParam('id'));

        if ($customer === null) {
            FlashMessage::danger("Cliente não encontrado");
            $this->redirectTo(route('customers.index'));
        }

        $this->render('customers/edit', compact('title', 'customer'));
    }

    public function update(Request $request): void
    {
        $title = 'Edição de clientes';
        $customerRequest = $request->getParam('customer');
        $customer = Customer::findById($request->getParam('id'));

        if ($customer === null) {
            FlashMessage::danger("Cliente não encontrado");
            $this->redirectTo(route('customers.index'));
        }

        $customer->name = $customerRequest['name'];
        $customer->phone = $customerRequest['phone'];

        if ($customer->save()) {
            FlashMessage::success('Cliente atualizado com sucesso!');
            $this->redirectTo(route('customers.index'));
        } else {
            FlashMessage::danger('Não foi possível atualizar o cliente!');
            $this->render('customers/edit', compact('title', 'customer'));
        }
    }

    public function destroy(Request $request): void
    {
        $customer = Customer::findById($request->getParam('id'));

        if ($customer === null) {
            FlashMessage::danger("Cliente não encontrado");
            $this->redirectTo(route('customers.index'));
        }

        if ($customer->hasOrders()) {
            FlashMessage::danger("Não é possível excluir um cliente com pedidos registrados");
            $this->redirectTo(route('customers.index'));
        } elseif ($customer->destroy()) {
            FlashMessage::success("Cliente excluído com sucesso!");
            $this->redirectTo(route('customers.index'));
        } else {
            FlashMessage::danger("Não foi possível excluir o cliente");
            $this->redirectTo(route('customers.index'));
        }
    }
}
