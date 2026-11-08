<?php

use App\Controllers\AuthenticationsController;
use App\Controllers\HomeController;
use Core\Router\Route;
use App\Controllers\AdminController;
use App\Controllers\CustomersController;
use App\Controllers\OrdersController;
use App\Controllers\ProductionsController;
use App\Controllers\ProductPhotosController;
use App\Controllers\ProductsController;
use App\Controllers\PurchasesController;
use App\Controllers\ReportsController;
use App\Controllers\SalesController;
use App\Controllers\SideDishesController;
use App\Controllers\UsersController;

// Authentication
Route::get('/login', [AuthenticationsController::class, 'new'])->name('users.login');
Route::post('/login', [AuthenticationsController::class, 'authenticate'])->name('users.login');

Route::middleware('auth')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('root');
    Route::post('/logout', [AuthenticationsController::class, 'destroy'])->name('users.logout');

    Route::get('/customers', [CustomersController::class, 'index'])->name('customers.index');
    Route::get('/customers/new', [CustomersController::class, 'new'])->name('customers.new');
    Route::post('/customers', [CustomersController::class, 'create'])->name('customers.create');
    Route::get('/customers/{id}', [CustomersController::class, 'show'])->name('customers.show');
    Route::get('/customers/{id}/edit', [CustomersController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{id}', [CustomersController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{id}', [CustomersController::class, 'destroy'])->name('customers.destroy');

    Route::get('/productions', [ProductionsController::class, 'index'])->name('productions.index');
    Route::get('/productions/new', [ProductionsController::class, 'new'])->name('productions.new');
    Route::post('/productions', [ProductionsController::class, 'create'])->name('productions.create');
    Route::get('/productions/{id}', [ProductionsController::class, 'show'])->name('productions.show');
    Route::get('/productions/{id}/edit', [ProductionsController::class, 'edit'])->name('productions.edit');
    Route::put('/productions/{id}', [ProductionsController::class, 'update'])->name('productions.update');
    Route::delete('/productions/{id}', [ProductionsController::class, 'destroy'])->name('productions.destroy');

    Route::get('/purchases', [PurchasesController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/new', [PurchasesController::class, 'new'])->name('purchases.new');
    Route::post('/purchases', [PurchasesController::class, 'create'])->name('purchases.create');
    Route::get('/purchases/{id}', [PurchasesController::class, 'show'])->name('purchases.show');
    Route::get('/purchases/{id}/edit', [PurchasesController::class, 'edit'])->name('purchases.edit');
    Route::put('/purchases/{id}', [PurchasesController::class, 'update'])->name('purchases.update');
    Route::delete('/purchases/{id}', [PurchasesController::class, 'destroy'])->name('purchases.destroy');

    Route::get('/orders', [OrdersController::class, 'index'])->name('orders.index');
    Route::get('/orders/new', [OrdersController::class, 'new'])->name('orders.new');
    Route::post('/orders', [OrdersController::class, 'create'])->name('orders.create');
    Route::get('/orders/{id}', [OrdersController::class, 'show'])->name('orders.show');
    Route::get('/orders/{id}/edit', [OrdersController::class, 'edit'])->name('orders.edit');
    Route::put('/orders/{id}', [OrdersController::class, 'update'])->name('orders.update');
    Route::put('/orders/{id}/cancel', [OrdersController::class, 'cancel'])->name('orders.cancel');
    Route::delete('/orders/{id}', [OrdersController::class, 'destroy'])->name('orders.destroy');

    Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
    Route::get('/sales/new', [SalesController::class, 'new'])->name('sales.new');
    Route::post('/sales', [SalesController::class, 'create'])->name('sales.create');
    Route::get('/sales/{id}', [SalesController::class, 'show'])->name('sales.show');
    Route::get('/sales/{id}/edit', [SalesController::class, 'edit'])->name('sales.edit');
    Route::put('/sales/{id}', [SalesController::class, 'update'])->name('sales.update');
    Route::delete('/sales/{id}', [SalesController::class, 'destroy'])->name('sales.destroy');

    Route::middleware('admin')->group(function () {
        Route::get('/admin/painel', [AdminController::class, 'index'])->name('admin.painel');
        Route::get('/admin/reports', [ReportsController::class, 'index'])->name('reports.index');

        Route::get('/admin/products', [ProductsController::class, 'index'])->name('products.index');
        Route::get('/admin/products/new', [ProductsController::class, 'new'])->name('products.new');
        Route::post('/admin/products', [ProductsController::class, 'create'])->name('products.create');
        Route::get('/admin/products/{id}', [ProductsController::class, 'show'])->name('products.show');
        Route::get('/admin/products/{id}/edit', [ProductsController::class, 'edit'])->name('products.edit');
        Route::put('/admin/products/{id}', [ProductsController::class, 'update'])->name('products.update');
        Route::delete('/admin/products/{id}', [ProductsController::class, 'destroy'])->name('products.destroy');

        Route::post('/admin/products/{product_id}/photos', [ProductPhotosController::class, 'create'])
            ->name('product_photos.create');
        Route::delete('/admin/products/{product_id}/photos/{id}', [ProductPhotosController::class, 'destroy'])
            ->name('product_photos.destroy');

        Route::get('/admin/side-dishes', [SideDishesController::class, 'index'])->name('side_dishes.index');
        Route::get('/admin/side-dishes/new', [SideDishesController::class, 'new'])->name('side_dishes.new');
        Route::post('/admin/side-dishes', [SideDishesController::class, 'create'])->name('side_dishes.create');
        Route::get('/admin/side-dishes/{id}', [SideDishesController::class, 'show'])->name('side_dishes.show');
        Route::get('/admin/side-dishes/{id}/edit', [SideDishesController::class, 'edit'])->name('side_dishes.edit');
        Route::put('/admin/side-dishes/{id}', [SideDishesController::class, 'update'])->name('side_dishes.update');
        Route::delete('/admin/side-dishes/{id}', [SideDishesController::class, 'destroy'])->name('side_dishes.destroy');

        Route::get('/admin/users', [UsersController::class, 'index'])->name('users.index');
        Route::get('/admin/users/new', [UsersController::class, 'new'])->name('users.new');
        Route::post('/admin/users', [UsersController::class, 'create'])->name('users.create');
        Route::get('/admin/users/{id}', [UsersController::class, 'show'])->name('users.show');
        Route::get('/admin/users/{id}/edit', [UsersController::class, 'edit'])->name('users.edit');
        Route::put('/admin/users/{id}', [UsersController::class, 'update'])->name('users.update');
        Route::delete('/admin/users/{id}', [UsersController::class, 'destroy'])->name('users.destroy');
    });
});
