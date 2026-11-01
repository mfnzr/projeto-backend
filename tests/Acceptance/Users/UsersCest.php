<?php

namespace Tests\Acceptance\Users;

use App\Models\User;
use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class UsersCest extends BaseAcceptanceCest
{
    public function adminCreatesUser(AcceptanceTester $page): void
    {
        $page->login('admin@example.com', '123456');
        $page->click('Gerenciar usuários');
        $page->click('Novo usuário');

        $page->fillField('user[name]', 'Ciclano');
        $page->fillField('user[email]', 'ciclano@example.com');
        $page->selectOption('user[role]', 'Atendente');
        $page->fillField('user[password]', '123456');
        $page->fillField('user[password_confirmation]', '123456');
        $page->click('Salvar');

        $page->seeInCurrentUrl('/admin/users');
        $page->see('Usuário criado com sucesso!');
        $page->see('ciclano@example.com');
    }

    public function adminCannotCreateUserWithInvalidData(AcceptanceTester $page): void
    {
        $page->login('admin@example.com', '123456');
        $page->amOnPage('/admin/users/new');

        $page->fillField('user[name]', 'Ciclano');
        $page->fillField('user[email]', 'email-invalido');
        $page->fillField('user[password]', '123456');
        $page->fillField('user[password_confirmation]', '654321');
        $page->click('Salvar');

        $page->see('Não foi possível criar o usuário');
        $page->see('deve ser um e-mail válido!');
        $page->see('as senhas devem ser idênticas!');
    }

    public function adminEditsUser(AcceptanceTester $page): void
    {
        $user = User::findByEmail('fulano@example.com');

        $page->login('admin@example.com', '123456');
        $page->amOnPage('/admin/users/' . $user->id . '/edit');

        $page->fillField('user[name]', 'Fulano de Tal');
        $page->selectOption('user[role]', 'Administrador');
        $page->click('Salvar');

        $page->see('Usuário atualizado com sucesso!');
        $page->see('Fulano de Tal');
    }

    public function adminCannotUpdateUserWithInvalidData(AcceptanceTester $page): void
    {
        $user = User::findByEmail('fulano@example.com');

        $page->login('admin@example.com', '123456');
        $page->amOnPage('/admin/users/' . $user->id . '/edit');

        $page->fillField('user[email]', 'admin@example.com');
        $page->click('Salvar');

        $page->see('Não foi possível atualizar o usuário!');
        $page->see('já existe um registro com esse dado');
    }

    public function adminListsUsers(AcceptanceTester $page): void
    {
        $page->login('admin@example.com', '123456');
        $page->amOnPage('/admin/users');

        $page->see('admin@example.com');
        $page->see('fulano@example.com');
        $page->see('Administrador');
        $page->see('Atendente');
    }

    public function adminPaginatesUsers(AcceptanceTester $page): void
    {
        for ($i = 1; $i <= 9; $i++) {
            $user = new User([
                'name' => 'Atendente ' . $i,
                'email' => 'atendente' . $i . '@example.com',
                'password' => '123456',
                'password_confirmation' => '123456',
                'role' => 'attendant'
            ]);
            $user->save();
        }

        $page->login('admin@example.com', '123456');
        $page->amOnPage('/admin/users');

        $page->see('Mostrando 1 - 10 de 11');
        $page->click('Próxima');

        $page->seeInCurrentUrl('page=2');
        $page->see('Mostrando 11 - 11 de 11');
        $page->see('atendente9@example.com');
    }

    public function adminDeletesUser(AcceptanceTester $page): void
    {
        $user = User::findByEmail('fulano@example.com');

        $page->login('admin@example.com', '123456');
        $page->amOnPage('/admin/users');

        $page->click('form[action="/admin/users/' . $user->id . '"] button[title="Excluir"]');
        $page->acceptPopup();

        $page->see('Usuário excluído com sucesso!');
        $page->dontSee('fulano@example.com');
    }

    public function attendantCannotManageUsers(AcceptanceTester $page): void
    {
        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/admin/users');

        $page->dontSeeInCurrentUrl('/admin/users');
        $page->see('Você deve ser administrador para acessar essa página');
    }
}
