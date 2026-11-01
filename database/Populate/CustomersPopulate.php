<?php

namespace Database\Populate;

use App\Models\Customer;

class CustomersPopulate
{
    public static function populate(): void
    {
        $numberOfCustomers = 15;

        for ($i = 1; $i <= $numberOfCustomers; $i++) {
            $customer = new Customer([
                'name' => 'Cliente ' . $i,
                'phone' => '429990000' . str_pad($i, 2, '0', STR_PAD_LEFT),
            ]);
            $customer->save();
        }

        echo "Customers populated with " . $numberOfCustomers . " registers\n";
    }
}
