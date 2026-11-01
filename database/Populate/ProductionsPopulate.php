<?php

namespace Database\Populate;

use App\Models\Product;
use App\Models\Production;

class ProductionsPopulate
{
    public static function populate(): void
    {
        $products = Product::all();
        $total = 0;

        for ($day = 0; $day < 3; $day++) {
            $date = date('Y-m-d', strtotime('-' . $day . ' days'));

            foreach ($products as $product) {
                $production = new Production([
                    'product_id' => $product->id,
                    'production_date' => $date,
                    'quantity' => 20,
                    'price_per_kg' => '45.90',
                ]);
                $production->save();
                $total++;
            }
        }

        echo "Productions populated with " . $total . " registers\n";
    }
}
