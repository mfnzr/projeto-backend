<?php

namespace Database\Populate;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderSideDish;
use App\Models\Production;
use App\Models\SideDish;

class OrdersPopulate
{
    public static function populate(): void
    {
        $customers = Customer::all();
        $productions = Production::allToday();
        $sideDishes = SideDish::all();
        $numberOfOrders = 3;

        for ($i = 0; $i < $numberOfOrders; $i++) {
            $order = new Order([
                'customer_id' => $customers[$i]->id,
                'status' => 'ordered',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $order->setQuantities([$productions[$i]->product_id => 2]);
            $order->save();

            $orderSideDish = new OrderSideDish([
                'order_id' => $order->id,
                'side_dish_id' => $sideDishes[$i]->id,
            ]);
            $orderSideDish->save();
        }

        echo "Orders populated with " . $numberOfOrders . " registers\n";
    }
}
