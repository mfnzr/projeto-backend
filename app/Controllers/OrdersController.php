<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Production;
use Lib\FlashMessage;
use Core\Http\Request;

class OrdersController extends Controller
{
    public function index(Request $request): void
    {
        $title = 'Pedidos';

        $page = (int) $request->getParam('page', 1);
        if ($page < 1) {
            $page = 1;
        }

        $paginator = Order::paginate(page: $page, per_page: 10, route: 'orders.index');
        $orders = $paginator->registers();

        $this->render('orders/index', compact('title', 'orders', 'paginator'));
    }

    public function show(Request $request): void
    {
        $title = 'Exibe o pedido';
        $order = Order::findById($request->getParam('id'));

        if ($order === null) {
            FlashMessage::danger("Pedido não encontrado");
            $this->redirectTo(route('orders.index'));
        }

        $this->render('orders/show', compact('title', 'order'));
    }

    public function new(): void
    {
        $title = 'Novo pedido';
        $order = new Order();
        $customers = Customer::all();
        $productions = Production::allToday();
        $this->render('orders/new', compact('title', 'order', 'customers', 'productions'));
    }

    public function create(Request $request): void
    {
        $title = 'Novo pedido';
        $orderRequest = $request->getParam('order');

        $order = new Order([
            'customer_id' => $orderRequest['customer_id'],
            'status' => 'ordered',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $order->setQuantities($request->getParam('quantities', []));

        if ($order->save()) {
            FlashMessage::success('Pedido criado com sucesso!');
            $this->redirectTo(route('orders.index'));
        } else {
            FlashMessage::danger('Não foi possível criar o pedido');
            $customers = Customer::all();
            $productions = Production::allToday();
            $this->render('orders/new', compact('title', 'order', 'customers', 'productions'));
        }
    }

    public function edit(Request $request): void
    {
        $title = 'Edição do pedido';
        $order = Order::findById($request->getParam('id'));

        if ($order === null) {
            FlashMessage::danger("Pedido não encontrado");
            $this->redirectTo(route('orders.index'));
        } elseif (!$order->isOrdered()) {
            FlashMessage::danger("Só é possível editar pedidos encomendados");
            $this->redirectTo(route('orders.show', ['id' => $order->id]));
        } else {
            $order->loadQuantities();
            $customers = Customer::all();
            $productions = Production::allToday();
            $this->render('orders/edit', compact('title', 'order', 'customers', 'productions'));
        }
    }

    public function update(Request $request): void
    {
        $title = 'Edição do pedido';
        $orderRequest = $request->getParam('order');
        $order = Order::findById($request->getParam('id'));

        if ($order === null) {
            FlashMessage::danger("Pedido não encontrado");
            $this->redirectTo(route('orders.index'));
        } elseif (!$order->isOrdered()) {
            FlashMessage::danger("Só é possível editar pedidos encomendados");
            $this->redirectTo(route('orders.show', ['id' => $order->id]));
        } else {
            $order->customer_id = $orderRequest['customer_id'];
            $order->setQuantities($request->getParam('quantities', []));

            if ($order->save()) {
                FlashMessage::success('Pedido atualizado com sucesso!');
                $this->redirectTo(route('orders.index'));
            } else {
                FlashMessage::danger('Não foi possível atualizar o pedido!');
                $customers = Customer::all();
                $productions = Production::allToday();
                $this->render('orders/edit', compact('title', 'order', 'customers', 'productions'));
            }
        }
    }

    public function cancel(Request $request): void
    {
        $order = Order::findById($request->getParam('id'));

        if ($order === null) {
            FlashMessage::danger("Pedido não encontrado");
            $this->redirectTo(route('orders.index'));
        } elseif (!$order->isOrdered()) {
            FlashMessage::danger("Só é possível cancelar pedidos encomendados");
            $this->redirectTo(route('orders.show', ['id' => $order->id]));
        } else {
            $order->update(['status' => 'cancelled']);
            FlashMessage::success("Pedido cancelado e unidades devolvidas ao disponível");
            $this->redirectTo(route('orders.index'));
        }
    }

    public function destroy(Request $request): void
    {
        $order = Order::findById($request->getParam('id'));

        if ($order === null) {
            FlashMessage::danger("Pedido não encontrado");
            $this->redirectTo(route('orders.index'));
        } elseif ($order->isPickedUp()) {
            FlashMessage::danger("Não é possível excluir um pedido já retirado. Exclua a venda dele primeiro");
            $this->redirectTo(route('orders.index'));
        } elseif ($order->destroy()) {
            FlashMessage::success("Pedido excluído com sucesso!");
            $this->redirectTo(route('orders.index'));
        } else {
            FlashMessage::danger("Não foi possível excluir o pedido");
            $this->redirectTo(route('orders.index'));
        }
    }
}
