<?php

namespace Database\Populate;

use App\Models\Production;
use App\Models\Sale;

class SalesPopulate
{
    public static function populate(): void
    {
        $productions = Production::allToday();
        $numberOfSales = 12;

        for ($i = 0; $i < $numberOfSales; $i++) {
            $production = $productions[$i % count($productions)];

            $sale = new Sale([
                'payment_method' => 'pix',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $sale->setItems([
                $production->product_id => ['quantity' => 1, 'amount' => '55.08'],
            ]);
            $sale->save();
        }

        echo "Sales populated with " . $numberOfSales . " registers\n";
    }
}
