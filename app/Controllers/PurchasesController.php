<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use Lib\FlashMessage;
use Core\Http\Request;

class PurchasesController extends Controller
{
    public function index(Request $request): void
    {
        $title = 'Compras';

        $page = (int) $request->getParam('page', 1);
        if ($page < 1) {
            $page = 1;
        }

        $paginator = Purchase::paginate(page: $page, per_page: 10, route: 'purchases.index');
        $purchases = $paginator->registers();

        $this->render('purchases/index', compact('title', 'purchases', 'paginator'));
    }

    public function show(Request $request): void
    {
        $title = 'Exibe a compra';
        $purchase = Purchase::findById($request->getParam('id'));

        if ($purchase === null) {
            FlashMessage::danger("Compra não encontrada");
            $this->redirectTo(route('purchases.index'));
        }

        $this->render('purchases/show', compact('title', 'purchase'));
    }

    public function new(): void
    {
        $title = 'Registro de compra';
        $purchase = new Purchase(['purchase_date' => date('Y-m-d')]);
        $products = Product::all();
        $this->render('purchases/new', compact('title', 'purchase', 'products'));
    }

    public function create(Request $request): void
    {
        $title = 'Registro de compra';
        $newPurchase = $request->getParam('purchase');
        $purchase = new Purchase($newPurchase);

        if ($purchase->save()) {
            FlashMessage::success('Compra registrada com sucesso!');
            $this->redirectTo(route('purchases.index'));
        } else {
            FlashMessage::danger('Não foi possível registrar a compra');
            $products = Product::all();
            $this->render('purchases/new', compact('title', 'purchase', 'products'));
        }
    }

    public function edit(Request $request): void
    {
        $title = 'Edição da compra';
        $purchase = Purchase::findById($request->getParam('id'));

        if ($purchase === null) {
            FlashMessage::danger("Compra não encontrada");
            $this->redirectTo(route('purchases.index'));
        }

        $products = Product::all();
        $this->render('purchases/edit', compact('title', 'purchase', 'products'));
    }

    public function update(Request $request): void
    {
        $title = 'Edição da compra';
        $purchaseRequest = $request->getParam('purchase');
        $purchase = Purchase::findById($request->getParam('id'));

        if ($purchase === null) {
            FlashMessage::danger("Compra não encontrada");
            $this->redirectTo(route('purchases.index'));
        }

        $purchase->product_id = $purchaseRequest['product_id'];
        $purchase->purchase_date = $purchaseRequest['purchase_date'];
        $purchase->quantity = $purchaseRequest['quantity'];
        $purchase->total_amount = $purchaseRequest['total_amount'];
        $purchase->price_per_kg = $purchaseRequest['price_per_kg'];

        if ($purchase->save()) {
            FlashMessage::success('Compra atualizada com sucesso!');
            $this->redirectTo(route('purchases.index'));
        } else {
            FlashMessage::danger('Não foi possível atualizar a compra!');
            $products = Product::all();
            $this->render('purchases/edit', compact('title', 'purchase', 'products'));
        }
    }

    public function destroy(Request $request): void
    {
        $purchase = Purchase::findById($request->getParam('id'));

        if ($purchase === null) {
            FlashMessage::danger("Compra não encontrada");
            $this->redirectTo(route('purchases.index'));
        }

        if ($purchase->destroy()) {
            FlashMessage::success("Compra excluída com sucesso!");
            $this->redirectTo(route('purchases.index'));
        } else {
            FlashMessage::danger("Não foi possível excluir a compra");
            $this->redirectTo(route('purchases.index'));
        }
    }
}
