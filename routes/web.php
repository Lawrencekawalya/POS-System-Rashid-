<?php

use App\Http\Controllers\CashReconciliationController;
use App\Http\Controllers\InventoryReportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfitabilityReportController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\TurnoverReportController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Product Management Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::resource('products', ProductController::class);
});

/*
|--------------------------------------------------------------------------
| Product Management Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/pos', [\App\Http\Controllers\POSController::class, 'index'])->name('pos.index');
    Route::post('/pos/add', [\App\Http\Controllers\POSController::class, 'add'])->name('pos.add');
    Route::post('/pos/checkout', [\App\Http\Controllers\POSController::class, 'checkout'])->name('pos.checkout');
    Route::post('/pos/remove/{productId}', [\App\Http\Controllers\POSController::class, 'remove'])
        ->name('pos.remove');
    Route::post('/pos/increase/{productId}', [\App\Http\Controllers\POSController::class, 'increase'])
        ->name('pos.increase');

    Route::post('/pos/decrease/{productId}', [\App\Http\Controllers\POSController::class, 'decrease'])
        ->name('pos.decrease');
    Route::get('/sales/{sale}', [\App\Http\Controllers\SaleController::class, 'show'])
        ->name('sales.show');
    Route::get('/sales', [\App\Http\Controllers\SaleController::class, 'index'])
        ->name('sales.index');
    Route::post('/sales/{sale}/refund', [\App\Http\Controllers\SaleController::class, 'refund'])
        ->name('sales.refund');
    Route::post(
        '/sales/{sale}/partial-refund',
        [\App\Http\Controllers\SaleController::class, 'partialRefund']
    )->name('sales.partial-refund');
    Route::get(
        '/sales/{sale}/refunds/{refund}',
        [\App\Http\Controllers\SaleController::class, 'refundReceipt']
    )->name('sales.refund-receipt');
    Route::get('/reports/z-report', [\App\Http\Controllers\ReportController::class, 'zReport'])
        ->name('reports.z');
    Route::get('/cash-reconciliation', [CashReconciliationController::class, 'create'])
        ->name('cash.reconcile');

    Route::post('/cash-reconciliation', [CashReconciliationController::class, 'store'])
        ->name('cash.reconcile.store');
    Route::get(
        '/cash-reconciliation/history',
        [CashReconciliationController::class, 'index']
    )->name('cash.reconcile.index');
    Route::get('/purchases/create', [PurchaseController::class, 'create'])
        ->name('purchases.create');
    Route::post('/purchases', [PurchaseController::class, 'store'])
        ->name('purchases.store');
    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    Route::get('/reports/inventory', [InventoryReportController::class, 'index'])
        ->name('reports.inventory');
    Route::get('/reports/profitability', [ProfitabilityReportController::class, 'index'])
        ->name('reports.profitability');
    Route::get('/reports/turnover', [TurnoverReportController::class, 'index'])
        ->name('reports.turnover');
});

require __DIR__ . '/settings.php';
