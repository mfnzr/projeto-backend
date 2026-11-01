<?php

require __DIR__ . '/../../config/bootstrap.php';

use Core\Database\Database;
use Database\Populate\CustomersPopulate;
use Database\Populate\OrdersPopulate;
use Database\Populate\ProductionsPopulate;
use Database\Populate\ProductsPopulate;
use Database\Populate\PurchasesPopulate;
use Database\Populate\SalesPopulate;
use Database\Populate\SideDishesPopulate;
use Database\Populate\UsersPopulate;

Database::migrate();

UsersPopulate::populate();
ProductsPopulate::populate();
SideDishesPopulate::populate();
CustomersPopulate::populate();
ProductionsPopulate::populate();
PurchasesPopulate::populate();
OrdersPopulate::populate();
SalesPopulate::populate();
