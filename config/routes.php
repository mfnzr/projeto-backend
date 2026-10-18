<?php

use App\Controllers\AuthenticationsController;
use App\Controllers\HomeController;
use Core\Router\Route;
use App\Controllers\AdminController;
use App\Controllers\ProductsController;

// Authentication
Route::get('/login', [AuthenticationsController::class, 'new'])->name('users.login');
Route::post('/login', [AuthenticationsController::class, 'authenticate'])->name('users.login');

Route::middleware('auth')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('root');
    Route::post('/logout', [AuthenticationsController::class, 'destroy'])->name('users.logout');

    Route::middleware('admin')->group(function () {
        Route::get('/admin/painel', [AdminController::class, 'index'])->name('admin.painel');
        Route::get('/admin/products', [ProductsController::class, 'index'])->name('products.index');
        Route::get('/admin/products/new', [ProductsController::class, 'new'])->name('products.new');
        Route::post('/admin/products', [ProductsController::class, 'create'])->name('products.create');
        Route::get('/admin/products/{id}', [ProductsController::class, 'show'])->name('products.show');
        Route::get('/admin/products/{id}/edit', [ProductsController::class, 'edit'])->name('products.edit');
        Route::put('/admin/products/{id}', [ProductsController::class, 'update'])->name('products.update');
        Route::delete('/admin/products/{id}', [ProductsController::class, 'destroy'])->name('products.destroy');
    });
});
