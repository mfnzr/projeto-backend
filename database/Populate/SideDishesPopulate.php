<?php

namespace Database\Populate;

use App\Models\SideDish;

class SideDishesPopulate
{
    public static function populate(): void
    {
        $sideDishes = [
            ['name' => 'Maionese', 'price' => '12.00'],
            ['name' => 'Farofa', 'price' => '8.00'],
            ['name' => 'Vinagrete', 'price' => '7.00'],
            ['name' => 'Arroz', 'price' => '10.00'],
        ];

        foreach ($sideDishes as $data) {
            $sideDish = new SideDish($data);
            $sideDish->save();
        }

        echo "Side dishes populated with " . count($sideDishes) . " registers\n";
    }
}
