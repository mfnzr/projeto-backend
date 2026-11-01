<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use App\Models\SideDish;
use Lib\FlashMessage;
use Core\Http\Request;

class SideDishesController extends Controller
{
    public function index(Request $request): void
    {
        $title = 'Controle de acompanhamentos';

        $page = (int) $request->getParam('page', 1);
        if ($page < 1) {
            $page = 1;
        }

        $paginator = SideDish::paginate(page: $page, per_page: 10, route: 'side_dishes.index');
        $sideDishes = $paginator->registers();

        $this->render('side_dishes/index', compact('title', 'sideDishes', 'paginator'));
    }

    public function show(Request $request): void
    {
        $title = 'Exibe o acompanhamento';
        $sideDish = SideDish::findById($request->getParam('id'));

        if ($sideDish === null) {
            FlashMessage::danger("Acompanhamento não encontrado");
            $this->redirectTo(route('side_dishes.index'));
        }

        $this->render('side_dishes/show', compact('title', 'sideDish'));
    }

    public function new(): void
    {
        $title = 'Criação de acompanhamentos';
        $sideDish = new SideDish();
        $this->render('side_dishes/new', compact('title', 'sideDish'));
    }

    public function create(Request $request): void
    {
        $title = 'Criação de acompanhamentos';
        $newSideDish = $request->getParam('side_dish');
        $sideDish = new SideDish($newSideDish);

        if ($sideDish->save()) {
            FlashMessage::success('Acompanhamento criado com sucesso!');
            $this->redirectTo(route('side_dishes.index'));
        } else {
            FlashMessage::danger('Não foi possível criar o acompanhamento');
            $this->render('side_dishes/new', compact('title', 'sideDish'));
        }
    }

    public function edit(Request $request): void
    {
        $title = 'Edição de acompanhamentos';
        $sideDish = SideDish::findById($request->getParam('id'));

        if ($sideDish === null) {
            FlashMessage::danger("Acompanhamento não encontrado");
            $this->redirectTo(route('side_dishes.index'));
        }

        $this->render('side_dishes/edit', compact('title', 'sideDish'));
    }

    public function update(Request $request): void
    {
        $title = 'Edição de acompanhamentos';
        $sideDishRequest = $request->getParam('side_dish');
        $sideDish = SideDish::findById($request->getParam('id'));

        if ($sideDish === null) {
            FlashMessage::danger("Acompanhamento não encontrado");
            $this->redirectTo(route('side_dishes.index'));
        }

        $sideDish->name = $sideDishRequest['name'];
        $sideDish->price = $sideDishRequest['price'];

        if ($sideDish->save()) {
            FlashMessage::success('Acompanhamento atualizado com sucesso!');
            $this->redirectTo(route('side_dishes.index'));
        } else {
            FlashMessage::danger('Não foi possível atualizar o acompanhamento!');
            $this->render('side_dishes/edit', compact('title', 'sideDish'));
        }
    }

    public function destroy(Request $request): void
    {
        $sideDish = SideDish::findById($request->getParam('id'));

        if ($sideDish === null) {
            FlashMessage::danger("Acompanhamento não encontrado");
            $this->redirectTo(route('side_dishes.index'));
        }

        if ($sideDish->destroy()) {
            FlashMessage::success("Acompanhamento excluído com sucesso!");
            $this->redirectTo(route('side_dishes.index'));
        } else {
            FlashMessage::danger("Não foi possível excluir o acompanhamento");
            $this->redirectTo(route('side_dishes.index'));
        }
    }
}
