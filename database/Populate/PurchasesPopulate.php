<?php

namespace Database\Populate;

use App\Models\Product;
use App\Models\Purchase;

class PurchasesPopulate
{
    public static function populate(): void
    {
        $products = Product::all();

        foreach ($products as $product) {
            $purchase = new Purchase([
                'product_id' => $product->id,
                'purchase_date' => date('Y-m-d'),
                'quantity' => 30,
                'total_amount' => '600.00',
                'price_per_kg' => '20.00',
            ]);
            $purchase->save();
        }

        echo "Purchases populated with " . count($products) . " registers\n";
    }
}
