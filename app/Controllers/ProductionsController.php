<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Production;
use Lib\FlashMessage;
use Core\Http\Request;

class ProductionsController extends Controller
{
    public function index(Request $request): void
    {
        $title = 'Produção do dia';

        $page = (int) $request->getParam('page', 1);
        if ($page < 1) {
            $page = 1;
        }

        $paginator = Production::paginate(page: $page, per_page: 10, route: 'productions.index');
        $productions = $paginator->registers();

        $this->render('productions/index', compact('title', 'productions', 'paginator'));
    }

    public function show(Request $request): void
    {
        $title = 'Exibe a produção';
        $production = Production::findById($request->getParam('id'));

        if ($production === null) {
            FlashMessage::danger("Produção não encontrada");
            $this->redirectTo(route('productions.index'));
        }

        $this->render('productions/show', compact('title', 'production'));
    }

    public function new(): void
    {
        $title = 'Abertura do dia';
        $production = new Production(['production_date' => date('Y-m-d')]);
        $products = Product::all();
        $this->render('productions/new', compact('title', 'production', 'products'));
    }

    public function create(Request $request): void
    {
        $title = 'Abertura do dia';
        $newProduction = $request->getParam('production');
        $production = new Production($newProduction);

        if ($production->save()) {
            FlashMessage::success('Produção registrada com sucesso!');
            $this->redirectTo(route('productions.index'));
        } else {
            FlashMessage::danger('Não foi possível registrar a produção');
            $products = Product::all();
            $this->render('productions/new', compact('title', 'production', 'products'));
        }
    }

    public function edit(Request $request): void
    {
        $title = 'Edição da produção';
        $production = Production::findById($request->getParam('id'));

        if ($production === null) {
            FlashMessage::danger("Produção não encontrada");
            $this->redirectTo(route('productions.index'));
        }

        $products = Product::all();
        $this->render('productions/edit', compact('title', 'production', 'products'));
    }

    public function update(Request $request): void
    {
        $title = 'Edição da produção';
        $productionRequest = $request->getParam('production');
        $production = Production::findById($request->getParam('id'));

        if ($production === null) {
            FlashMessage::danger("Produção não encontrada");
            $this->redirectTo(route('productions.index'));
        }

        $production->product_id = $productionRequest['product_id'];
        $production->production_date = $productionRequest['production_date'];
        $production->quantity = $productionRequest['quantity'];
        $production->price_per_kg = $productionRequest['price_per_kg'];

        if ($production->save()) {
            FlashMessage::success('Produção atualizada com sucesso!');
            $this->redirectTo(route('productions.index'));
        } else {
            FlashMessage::danger('Não foi possível atualizar a produção!');
            $products = Product::all();
            $this->render('productions/edit', compact('title', 'production', 'products'));
        }
    }

    public function destroy(Request $request): void
    {
        $production = Production::findById($request->getParam('id'));

        if ($production === null) {
            FlashMessage::danger("Produção não encontrada");
            $this->redirectTo(route('productions.index'));
        }

        if ($production->destroy()) {
            FlashMessage::success("Produção excluída com sucesso!");
            $this->redirectTo(route('productions.index'));
        } else {
            FlashMessage::danger("Não foi possível excluir a produção");
            $this->redirectTo(route('productions.index'));
        }
    }
}
