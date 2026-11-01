<?php

namespace Tests\Acceptance\Authentication;

use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class LoginCest extends BaseAcceptanceCest
{
    public function redirectToLoginWhenNotAuthenticated(AcceptanceTester $page): void
    {
        $page->amOnPage('/');

        $page->seeInCurrentUrl('/login');
        $page->see('Você deve estar logado para acessar essa página');
    }

    public function loginAsAttendant(AcceptanceTester $page): void
    {
        $page->login('fulano@example.com', '123456');

        $page->seeInCurrentUrl('/');
        $page->see('Login efetuado com sucesso');
        $page->see('Bem-vindo à área do usuário');
        $page->dontSee('Painel Admin');
    }

    public function loginAsAdmin(AcceptanceTester $page): void
    {
        $page->login('admin@example.com', '123456');

        $page->seeInCurrentUrl('/admin/painel');
        $page->see('Você está na área administrativa');
        $page->see('Painel Admin');
    }

    public function loginWithInvalidCredentials(AcceptanceTester $page): void
    {
        $page->login('fulano@example.com', 'senha-errada');

        $page->seeInCurrentUrl('/login');
        $page->see('E-mail ou senha incorretos');
    }

    public function attendantCannotAccessAdminArea(AcceptanceTester $page): void
    {
        $page->login('fulano@example.com', '123456');
        $page->amOnPage('/admin/painel');

        $page->dontSeeInCurrentUrl('/admin');
        $page->see('Você deve ser administrador para acessar essa página');
    }

    public function logout(AcceptanceTester $page): void
    {
        $page->login('fulano@example.com', '123456');
        $page->logout();

        $page->seeInCurrentUrl('/login');

        $page->amOnPage('/');
        $page->seeInCurrentUrl('/login');
    }
}
