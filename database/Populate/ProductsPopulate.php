<?php

namespace Database\Populate;

use App\Models\Product;

class ProductsPopulate
{
    public static function populate(): void
    {
        $products = [
            ['name' => 'Frango assado', 'description' => 'Frango inteiro temperado e assado na brasa.'],
            ['name' => 'Costela de porco', 'description' => 'Costela suína assada lentamente.'],
            ['name' => 'Costela bovina', 'description' => 'Costela de boi assada por 8 horas.'],
            ['name' => 'Pernil', 'description' => 'Pernil suíno assado, vendido por kg.'],
            ['name' => 'Linguiça', 'description' => null],
        ];

        foreach ($products as $data) {
            $product = new Product($data);
            $product->save();
        }

        echo "Products populated with " . count($products) . " registers\n";
    }
}
