<?php

namespace Database\Populate;

use App\Models\User;

class UsersPopulate
{
    public static function populate(): void
    {
        $admin = new User([
            'name' => 'Administrador',
            'email' => 'admin@example.com',
            'password' => '123456',
            'password_confirmation' => '123456',
            'role' => 'admin',
        ]);
        $admin->save();

        $attendant = new User([
            'name' => 'Fulano',
            'email' => 'fulano@example.com',
            'password' => '123456',
            'password_confirmation' => '123456',
            'role' => 'attendant',
        ]);
        $attendant->save();

        $numberOfAttendants = 5;

        for ($i = 1; $i <= $numberOfAttendants; $i++) {
            $user = new User([
                'name' => 'Atendente ' . $i,
                'email' => 'atendente' . $i . '@example.com',
                'password' => '123456',
                'password_confirmation' => '123456',
                'role' => 'attendant',
            ]);
            $user->save();
        }

        echo "Users populated with " . ($numberOfAttendants + 2) . " registers\n";
    }
}
