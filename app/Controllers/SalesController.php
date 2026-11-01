<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Production;
use App\Models\Sale;
use Lib\FlashMessage;
use Core\Http\Request;

class SalesController extends Controller
{
    public function index(Request $request): void
    {
        $title = 'Vendas';

        $page = (int) $request->getParam('page', 1);
        if ($page < 1) {
            $page = 1;
        }

        $paginator = Sale::paginate(page: $page, per_page: 10, route: 'sales.index');
        $sales = $paginator->registers();

        $this->render('sales/index', compact('title', 'sales', 'paginator'));
    }

    public function show(Request $request): void
    {
        $title = 'Exibe a venda';
        $sale = Sale::findById($request->getParam('id'));

        if ($sale === null) {
            FlashMessage::danger("Venda não encontrada");
            $this->redirectTo(route('sales.index'));
        }

        $this->render('sales/show', compact('title', 'sale'));
    }

    public function new(Request $request): void
    {
        $sale = new Sale(['created_at' => date('Y-m-d H:i:s')]);
        $orderId = $request->getParam('order_id');

        if ($orderId === null) {
            $title = 'Venda no balcão';
            $productions = Production::allToday();
            $fixedQuantities = false;
            $this->render('sales/new', compact('title', 'sale', 'productions', 'fixedQuantities'));
            return;
        }

        $order = Order::findById($orderId);

        if ($order === null || !$order->isOrdered()) {
            FlashMessage::danger("Esse pedido não pode ser retirado");
            $this->redirectTo(route('orders.index'));
        } else {
            $title = 'Retirada do pedido #' . $order->id;
            $sale->order_id = $order->id;
            $sale->setItems($this->itemsFromOrder($order, []));
            $productions = $this->productionsOf($sale);
            $fixedQuantities = true;
            $this->render('sales/new', compact('title', 'sale', 'productions', 'fixedQuantities'));
        }
    }

    public function create(Request $request): void
    {
        $saleRequest = $request->getParam('sale');
        $items = $request->getParam('items', []);

        $sale = new Sale([
            'order_id' => $saleRequest['order_id'],
            'payment_method' => $saleRequest['payment_method'] ?? '',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $order = null;
        if ($sale->isFromOrder()) {
            $order = Order::findById($sale->order_id);
        }

        if ($sale->isFromOrder() && ($order === null || !$order->isOrdered())) {
            FlashMessage::danger("Esse pedido não pode ser retirado");
            $this->redirectTo(route('orders.index'));
            return;
        }

        if ($order === null) {
            $title = 'Venda no balcão';
            $sale->setItems($items);
            $fixedQuantities = false;
        } else {
            $title = 'Retirada do pedido #' . $order->id;
            $sale->setItems($this->itemsFromOrder($order, $items));
            $fixedQuantities = true;
        }

        if ($sale->save()) {
            if ($order !== null) {
                $order->update(['status' => 'picked_up']);
            }

            FlashMessage::success('Venda registrada com sucesso!');
            $this->redirectTo(route('sales.index'));
        } else {
            FlashMessage::danger('Não foi possível registrar a venda');

            if ($order === null) {
                $productions = Production::allToday();
            } else {
                $productions = $this->productionsOf($sale);
            }

            $this->render('sales/new', compact('title', 'sale', 'productions', 'fixedQuantities'));
        }
    }

    public function edit(Request $request): void
    {
        $title = 'Edição da venda';
        $sale = Sale::findById($request->getParam('id'));

        if ($sale === null) {
            FlashMessage::danger("Venda não encontrada");
            $this->redirectTo(route('sales.index'));
        } else {
            $sale->loadItems();
            $productions = $this->productionsOf($sale);
            $fixedQuantities = true;
            $this->render('sales/edit', compact('title', 'sale', 'productions', 'fixedQuantities'));
        }
    }

    public function update(Request $request): void
    {
        $title = 'Edição da venda';
        $saleRequest = $request->getParam('sale');
        $sale = Sale::findById($request->getParam('id'));

        if ($sale === null) {
            FlashMessage::danger("Venda não encontrada");
            $this->redirectTo(route('sales.index'));
            return;
        }

        $sale->payment_method = $saleRequest['payment_method'] ?? '';
        $sale->loadItems();
        $sale->setAmounts($request->getParam('items', []));

        if ($sale->save()) {
            FlashMessage::success('Venda atualizada com sucesso!');
            $this->redirectTo(route('sales.index'));
        } else {
            FlashMessage::danger('Não foi possível atualizar a venda!');
            $productions = $this->productionsOf($sale);
            $fixedQuantities = true;
            $this->render('sales/edit', compact('title', 'sale', 'productions', 'fixedQuantities'));
        }
    }

    public function destroy(Request $request): void
    {
        $sale = Sale::findById($request->getParam('id'));

        if ($sale === null) {
            FlashMessage::danger("Venda não encontrada");
            $this->redirectTo(route('sales.index'));
            return;
        }

        $order = null;
        if ($sale->isFromOrder()) {
            $order = $sale->order;
        }

        if ($sale->destroy()) {
            if ($order !== null) {
                $order->update(['status' => 'ordered']);
            }

            FlashMessage::success("Venda excluída com sucesso!");
            $this->redirectTo(route('sales.index'));
        } else {
            FlashMessage::danger("Não foi possível excluir a venda");
            $this->redirectTo(route('sales.index'));
        }
    }

    /**
     * @param array<int|string, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private function itemsFromOrder(Order $order, array $items): array
    {
        $orderItems = [];

        foreach ($order->items as $orderItem) {
            $amount = '';

            if (isset($items[$orderItem->product_id]['amount'])) {
                $amount = $items[$orderItem->product_id]['amount'];
            }

            $orderItems[$orderItem->product_id] = [
                'quantity' => $orderItem->quantity,
                'amount' => $amount,
            ];
        }

        return $orderItems;
    }

    /**
     * @return array<Production>
     */
    private function productionsOf(Sale $sale): array
    {
        $productions = [];

        foreach ($sale->lines() as $productId => $line) {
            $production = $sale->productionOf($productId);

            if ($production !== null) {
                $productions[] = $production;
            }
        }

        return $productions;
    }
}
