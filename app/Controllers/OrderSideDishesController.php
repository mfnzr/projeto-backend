<?php

namespace App\Controllers;

use App\Models\Order;
use App\Models\OrderSideDish;
use App\Models\SideDish;
use Core\Http\Controllers\Controller;
use Core\Http\Request;

class OrderSideDishesController extends Controller
{
    public function create(Request $request): void
    {
        $order = Order::findById($request->getParam('order_id'));

        if ($order === null) {
            $this->renderError(404, 'Pedido não encontrado');
            return;
        }

        if (!$order->isOrdered()) {
            $this->renderError(422, 'Só é possível alterar os acompanhamentos de pedidos encomendados');
            return;
        }

        $orderSideDish = new OrderSideDish([
            'order_id' => $order->id,
            'side_dish_id' => $request->getParam('side_dish_id'),
        ]);

        if (!$orderSideDish->save()) {
            $message = 'Não foi possível adicionar: ' . $orderSideDish->errors('side_dish_id');
            $this->renderError(422, $message);
            return;
        }

        $sideDish = SideDish::findById($orderSideDish->side_dish_id);
        $this->renderJson('order_side_dishes/create', compact('sideDish'));
    }

    public function destroy(Request $request): void
    {
        $order = Order::findById($request->getParam('order_id'));

        if ($order === null) {
            $this->renderError(404, 'Pedido não encontrado');
            return;
        }

        if (!$order->isOrdered()) {
            $this->renderError(422, 'Só é possível alterar os acompanhamentos de pedidos encomendados');
            return;
        }

        $orderSideDish = OrderSideDish::findBy([
            'order_id' => $order->id,
            'side_dish_id' => $request->getParam('side_dish_id'),
        ]);

        if ($orderSideDish === null) {
            $this->renderError(404, 'Esse acompanhamento não está no pedido');
            return;
        }

        $orderSideDish->destroy();
        $this->renderJson('order_side_dishes/destroy', compact('orderSideDish'));
    }

    private function renderError(int $status, string $message): void
    {
        http_response_code($status);
        $this->renderJson('order_side_dishes/error', compact('message'));
    }
}
