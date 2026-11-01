<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use App\Models\User;
use Lib\Authentication\Auth;
use Lib\FlashMessage;
use Core\Http\Request;

class AuthenticationsController extends Controller
{
    protected string $layout = 'login';

    public function new(): void
    {
        $title = 'Login';
        $this->render('authentications/new', compact('title'));
    }

    public function authenticate(Request $request): void
    {
        $data = $request->getParam('user');
        $user = User::findByEmail($data['email']);
        if ($user !== null && $user->authenticate($data['password'])) {
            Auth::login($user);
            FlashMessage::success('Login efetuado com sucesso');
            if ($user->isAdmin()) {
                $this->redirectTo(route('admin.painel'));
            } else {
                $this->redirectTo(route('root'));
            }
        } else {
            FlashMessage::success('E-mail ou senha incorretos');
            $this->redirectTo(route('users.login'));
        }
    }

    public function destroy(): void
    {
        Auth::logout();
        FlashMessage::success('Você saiu do sistema');
        $this->redirectTo(route('users.login'));
    }
}
