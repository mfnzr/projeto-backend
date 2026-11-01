<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use App\Models\User;
use Lib\FlashMessage;
use Core\Http\Request;

class UsersController extends Controller
{
    public function index(Request $request): void
    {
        $title = 'Controle de usuários';

        $page = (int) $request->getParam('page', 1);
        if ($page < 1) {
            $page = 1;
        }

        $paginator = User::paginate(page: $page, per_page: 10, route: 'users.index');
        $users = $paginator->registers();

        $this->render('users/index', compact('title', 'users', 'paginator'));
    }

    public function show(Request $request): void
    {
        $title = 'Exibe o usuário';
        $user = User::findById($request->getParam('id'));

        if ($user === null) {
            FlashMessage::danger("Usuário não encontrado");
            $this->redirectTo(route('users.index'));
        }

        $this->render('users/show', compact('title', 'user'));
    }

    public function new(): void
    {
        $title = 'Criação de usuários';
        $user = new User();
        $this->render('users/new', compact('title', 'user'));
    }

    public function create(Request $request): void
    {
        $title = 'Criação de usuários';
        $newUser = $request->getParam('user');
        $user = new User($newUser);

        if ($user->save()) {
            FlashMessage::success('Usuário criado com sucesso!');
            $this->redirectTo(route('users.index'));
        } else {
            FlashMessage::danger('Não foi possível criar o usuário');
            $this->render('users/new', compact('title', 'user'));
        }
    }

    public function edit(Request $request): void
    {
        $title = 'Edição de usuários';
        $user = User::findById($request->getParam('id'));

        if ($user === null) {
            FlashMessage::danger("Usuário não encontrado");
            $this->redirectTo(route('users.index'));
        }

        $this->render('users/edit', compact('title', 'user'));
    }

    public function update(Request $request): void
    {
        $title = 'Edição de usuários';
        $userRequest = $request->getParam('user');
        $user = User::findById($request->getParam('id'));

        if ($user === null) {
            FlashMessage::danger("Usuário não encontrado");
            $this->redirectTo(route('users.index'));
        }

        $user->name = $userRequest['name'];
        $user->email = $userRequest['email'];
        $user->role = $userRequest['role'];

        if ($user->save()) {
            FlashMessage::success('Usuário atualizado com sucesso!');
            $this->redirectTo(route('users.index'));
        } else {
            FlashMessage::danger('Não foi possível atualizar o usuário!');
            $this->render('users/edit', compact('title', 'user'));
        }
    }

    public function destroy(Request $request): void
    {
        $user = User::findById($request->getParam('id'));

        if ($user === null) {
            FlashMessage::danger("Usuário não encontrado");
            $this->redirectTo(route('users.index'));
        }

        if ($user->id === $this->currentUser()->id) {
            FlashMessage::danger("Você não pode excluir o seu próprio usuário");
            $this->redirectTo(route('users.index'));
        } elseif ($user->destroy()) {
            FlashMessage::success("Usuário excluído com sucesso!");
            $this->redirectTo(route('users.index'));
        } else {
            FlashMessage::danger("Não foi possível excluir o usuário");
            $this->redirectTo(route('users.index'));
        }
    }
}
