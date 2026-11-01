<?php

namespace Database\Populate;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Production;

class OrdersPopulate
{
    public static function populate(): void
    {
        $customers = Customer::all();
        $productions = Production::allToday();
        $numberOfOrders = 3;

        for ($i = 0; $i < $numberOfOrders; $i++) {
            $order = new Order([
                'customer_id' => $customers[$i]->id,
                'status' => 'ordered',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $order->setQuantities([$productions[$i]->product_id => 2]);
            $order->save();
        }

        echo "Orders populated with " . $numberOfOrders . " registers\n";
    }
}
