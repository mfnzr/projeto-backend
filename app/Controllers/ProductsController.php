<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use App\Models\Product;
use Lib\FlashMessage;
use Core\Http\Request;

class ProductsController extends Controller
{
    public function index(): void
    {
        $title = 'Controle de produtos';
        $products = Product::all();
        $this->render('products/index', compact('title', 'products'));
    }

    public function edit(Request $request): void
    {
        $title = 'Edição de produtos';
        $product = Product::findById($request->getParam('id'));

        if ($product === null) {
            FlashMessage::danger("Produto não encontrado");
            $this->redirectTo(route('products.index'));
        }

        $this->render('products/edit', compact('title', 'product'));
    }

    public function new(): void
    {
        $title = 'Criação de produtos';
        $product = new Product();
        $this->render('products/new', compact('title', 'product'));
    }

    public function create(Request $request): void
    {
        $title = 'Criação de produtos';
        $newProduct = $request->getParam('product');
        $product = new Product($newProduct);
        if ($product->save()) {
            FlashMessage::success('Produto criado com sucesso!');
            $this->redirectTo(route('products.index'));
        } else {
            FlashMessage::danger('Não foi possível criar o produto');
            $this->render('products/new', compact('product', 'title'));
        }
    }

    public function update(Request $request): void
    {
        $title = 'Edição de produtos';
        $productRequest = $request->getParam('product');
        $productId = $request->getParam('id');
        $product = Product::findById($productId);

        if ($product === null) {
            FlashMessage::danger("Produto não encontrado");
            $this->redirectTo(route('products.index'));
        }

        $product->name = $productRequest['name'];
        $product->description = $productRequest['description'];

        if ($product->save()) {
            FlashMessage::success('Produto atualizado com sucesso!');
            $this->redirectTo(route('products.index'));
        } else {
            FlashMessage::danger('Não foi possível atualizar o produto!');
            $this->render('products/edit', compact('product', 'title'));
        }
    }

    public function destroy(Request $request): void
    {
        $productId = $request->getParam('id');
        $product = Product::findById($productId);

        if ($product === null) {
            FlashMessage::danger("Produto não encontrado");
            $this->redirectTo(route('products.index'));
        }

        if ($product->destroy()) {
            FlashMessage::success("Produto excluido com sucesso!");
            $this->redirectTo(route('products.index'));
        } else {
            FlashMessage::danger("Não foi possível excluir o produto");
            $this->redirectTo(route('products.index'));
        }
    }

    public function show(Request $request): void
    {
        $title = 'Exibe o produto';
        $productId = $request->getParam('id');
        $product = Product::findById($productId);

        if ($product === null) {
            FlashMessage::danger("Produto não encontrado");
            $this->redirectTo(route('products.index'));
        }

        $this->render('products/show', compact('product', 'title'));
    }
}
