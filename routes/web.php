<?php

use App\Http\Controllers\BillController;
use App\Http\Controllers\CashReconciliationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InventoryReportController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfitabilityReportController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\TurnoverReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Admin + Cashier)
|--------------------------------------------------------------------------
| These routes are shared by both roles.
*/

Route::middleware(['auth', 'role:admin,cashier'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | POS (Sales Processing)
    |--------------------------------------------------------------------------
    */

    Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
    Route::post('/pos/add', [POSController::class, 'add'])->name('pos.add');
    Route::post('/pos/checkout', [POSController::class, 'checkout'])->name('pos.checkout');
    Route::post('/pos/remove/{productId}', [POSController::class, 'remove'])->name('pos.remove');
    Route::post('/pos/increase/{productId}', [POSController::class, 'increase'])->name('pos.increase');
    Route::post('/pos/decrease/{productId}', [POSController::class, 'decrease'])->name('pos.decrease');
    Route::post('/pos/update/{product}', [POSController::class, 'updateQuantity'])->name('pos.updateQuantity');

    /*
    |--------------------------------------------------------------------------
    | Sales Viewing
    |--------------------------------------------------------------------------
    */

    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');

    /*
    |--------------------------------------------------------------------------
    | Refunds (Cashier + Admin)
    |--------------------------------------------------------------------------
    | Real-life POS: refunds happen at the till.
    */

    Route::post('/sales/{sale}/refund', [SaleController::class, 'refund'])
        ->name('sales.refund');

    Route::post('/sales/{sale}/partial-refund', [SaleController::class, 'partialRefund'])
        ->name('sales.partial-refund');

    Route::get('/sales/{sale}/refunds/{refund}', [SaleController::class, 'refundReceipt'])
        ->name('sales.refund-receipt');

    // --- Expense Routes ---
    // This one line handles index, create, store, edit, update, and destroy
    Route::resource('expenses', ExpenseController::class);

    /*
    |--------------------------------------------------------------------------
    | Room Billing
    |--------------------------------------------------------------------------
    */

    Route::prefix('bills')->group(function () {
        Route::post('/add-item', [BillController::class, 'addItem'])->name('bills.addItem');
        Route::post('/update-quantity/{item}', [BillController::class, 'updateQuantity'])->name('bills.updateQuantity');
        Route::post('/store', [BillController::class, 'store'])->name('bills.store');
        Route::post('/checkout', [BillController::class, 'checkout'])->name('bills.checkout');
        Route::get('/{room}', [BillController::class, 'show'])->name('bills.show');
    });

    /*
    |--------------------------------------------------------------------------
    | Room Folio & Settlement
    |--------------------------------------------------------------------------
    */
    Route::get('/rooms/{room}/folio', [\App\Http\Controllers\RoomFolioController::class, 'show'])->name('rooms.folio');
    Route::post('/rooms/{room}/settle', [\App\Http\Controllers\RoomFolioController::class, 'settle'])->name('rooms.settle');
});

/*
|--------------------------------------------------------------------------
| Admin-Only Routes
|--------------------------------------------------------------------------
| Back office, oversight, configuration.
*/

Route::middleware(['auth', 'role:admin'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('verified')
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Product Management
    |--------------------------------------------------------------------------
    */

    Route::resource('products', ProductController::class);

    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */

    Route::get('/reports/z-report', [ReportController::class, 'zReport'])
        ->name('reports.z');

    Route::get('/reports/inventory', [InventoryReportController::class, 'index'])
        ->name('reports.inventory');

    Route::get('/reports/profitability', [ProfitabilityReportController::class, 'index'])
        ->name('reports.profitability');

    Route::get('/reports/turnover', [TurnoverReportController::class, 'index'])
        ->name('reports.turnover');

    /*
    |--------------------------------------------------------------------------
    | Cash Reconciliation
    |--------------------------------------------------------------------------
    */
    Route::prefix('cash-reconciliation')->group(function () {
        // The main list of past reconciliations
        Route::get('/', [CashReconciliationController::class, 'index'])
            ->name('cash.reconcile.index');

        // The screen where Admin verifies expenses and counts cash
        Route::get('/create', [CashReconciliationController::class, 'create'])
            ->name('cash.reconcile.create');

        // Saving the reconciliation and confirming expenses
        Route::post('/store', [CashReconciliationController::class, 'store'])
            ->name('cash.reconcile.store');
        // Edit
        Route::get('/{reconciliation}/edit', [CashReconciliationController::class, 'edit'])
            ->name('cash.reconcile.edit');

        // Update
        Route::put('/{reconciliation}', [CashReconciliationController::class, 'update'])
            ->name('cash.reconcile.update');

        Route::patch(
            '/cash-reconciliation/{reconciliation}/cancel',
            [CashReconciliationController::class, 'cancel']
        )->name('cash.reconcile.cancel');
    });

    /*
    |--------------------------------------------------------------------------
    | Purchases
    |--------------------------------------------------------------------------
    */

    Route::resource('purchases', PurchaseController::class);

    /*
    |--------------------------------------------------------------------------
    | User Management
    |--------------------------------------------------------------------------
    */

    Route::resource('users', UserController::class);
    /*
    |--------------------------------------------------------------------------
    | Rooms Management
    |--------------------------------------------------------------------------
    */
    Route::resource('rooms', \App\Http\Controllers\RoomController::class);
});


/*
|--------------------------------------------------------------------------
| Settings / Profile Routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/settings.php';
