<?php

namespace Tests\Acceptance;

use App\Models\User;
use Core\Database\Database;
use Core\Env\EnvLoader;
use Tests\Support\AcceptanceTester;

class BaseAcceptanceCest
{
    public function _before(AcceptanceTester $page): void
    {
        EnvLoader::init();
        Database::create();
        Database::migrate();

        $this->createUser('Administrador', 'admin@example.com', 'admin');
        $this->createUser('Fulano', 'fulano@example.com', 'attendant');
    }

    public function _after(AcceptanceTester $page): void
    {
        Database::drop();
    }

    private function createUser(string $name, string $email, string $role): void
    {
        $user = new User([
            'name' => $name,
            'email' => $email,
            'password' => '123456',
            'password_confirmation' => '123456',
            'role' => $role
        ]);
        $user->save();
    }
}
