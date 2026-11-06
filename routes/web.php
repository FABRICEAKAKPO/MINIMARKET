<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\CashController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth', 'permission:catalog.view'])->group(function (): void {
    Route::get('catalogue', [CatalogController::class, 'index'])->name('catalog.index');
});

Route::middleware(['auth', 'permission:catalog.manage'])->group(function (): void {
    Route::get('catalogue/create', [CatalogController::class, 'create'])->name('catalog.create');
    Route::post('catalogue', [CatalogController::class, 'store'])->name('catalog.store');
});

Route::middleware(['auth', 'permission:inventory.view'])->group(function (): void {
    Route::get('stock', [StockController::class, 'index'])->name('stock.index');
});

Route::middleware(['auth', 'permission:inventory.manage'])->group(function (): void {
    Route::get('stock/receive', [StockController::class, 'receiveForm'])->name('stock.receive');
    Route::post('stock/receive', [StockController::class, 'receive'])->name('stock.receive.store');
    Route::post('stock/{product}/issue', [StockController::class, 'issue'])->name('stock.issue');
    Route::get('inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('inventory', [InventoryController::class, 'store'])->name('inventory.store');
});

Route::middleware(['auth', 'permission:purchasing.view'])->group(function (): void {
    Route::get('achats', [PurchasingController::class, 'index'])->name('purchasing.index');
});

Route::middleware(['auth', 'permission:purchasing.manage'])->group(function (): void {
    Route::post('fournisseurs', [PurchasingController::class, 'supplierStore'])->name('suppliers.store');
    Route::get('achats/create', [PurchasingController::class, 'orderCreate'])->name('purchasing.create');
    Route::post('achats', [PurchasingController::class, 'orderStore'])->name('purchasing.store');
    Route::get('achats/{purchaseOrder}/receive', [PurchasingController::class, 'receiveForm'])->name('purchasing.receive');
    Route::post('achats/{purchaseOrder}/receive', [PurchasingController::class, 'receive'])->name('purchasing.receive.store');
});

Route::middleware(['auth', 'permission:cash.open'])->group(function (): void {
    Route::get('caisse', [CashController::class, 'index'])->name('cash.index');
    Route::post('caisse/open', [CashController::class, 'open'])->name('cash.open');
});

Route::middleware(['auth', 'permission:cash.close'])->group(function (): void {
    Route::post('caisse/{cashSession}/close', [CashController::class, 'close'])->name('cash.close');
});

Route::middleware(['auth', 'permission:sales.create'])->group(function (): void {
    Route::get('pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('pos/sell', [PosController::class, 'sell'])->name('pos.sell');
    Route::get('pos/{sale}/receipt', [PosController::class, 'receipt'])->name('pos.receipt');
});

Route::middleware(['auth', 'permission:sales.cancel'])->group(function (): void {
    Route::post('pos/{sale}/cancel', [PosController::class, 'cancel'])->name('pos.cancel');
});

Route::middleware(['auth', 'permission:reports.view'])->group(function (): void {
    Route::get('clients', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('clients/{customer}/statement', [CustomerController::class, 'statement'])->name('customers.statement');
    Route::get('depenses', [ExpenseController::class, 'index'])->name('expenses.index');
});

Route::middleware(['auth', 'permission:users.manage'])->group(function (): void {
    Route::post('clients', [CustomerController::class, 'store'])->name('customers.store');
    Route::post('clients/{customer}/payment', [CustomerController::class, 'payment'])->name('customers.payment');
    Route::post('depenses', [ExpenseController::class, 'store'])->name('expenses.store');
});

require __DIR__.'/auth.php';
