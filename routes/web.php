<?php

use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DeviceMonitorController;
use App\Http\Controllers\Admin\PairingCodeController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Customer\CompanyController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\DeviceController;
use App\Http\Controllers\Customer\ReportController;
use App\Http\Controllers\Customer\SyncJobController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', AdminDashboardController::class)->name('dashboard');
    Route::resource('customers', AdminCustomerController::class)->except(['destroy']);
    Route::patch('customers/{customer}/toggle-status', [AdminCustomerController::class, 'toggleStatus'])
        ->name('customers.toggle-status');
    Route::post('customers/{customer}/pairing-codes', [PairingCodeController::class, 'store'])
        ->name('customers.pairing-codes.store');
    Route::get('devices', DeviceMonitorController::class)->name('devices.index');
});

Route::middleware(['auth', 'role:customer_admin,customer_staff'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('dashboard', CustomerDashboardController::class)->name('dashboard');
    Route::get('devices', [DeviceController::class, 'index'])->name('devices.index');
    Route::post('companies/{company}/authorize', [CompanyController::class, 'connect'])->name('companies.authorize');
    Route::delete('companies/{company}/authorize', [CompanyController::class, 'disconnect'])->name('companies.deauthorize');
    Route::get('sync-jobs', [SyncJobController::class, 'index'])->name('sync-jobs.index');
    Route::post('company-connections/{connection}/sync', [SyncJobController::class, 'store'])->name('company-connections.sync');

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('trial-balance', [ReportController::class, 'trialBalance'])->name('trial-balance');
        Route::get('day-book', [ReportController::class, 'dayBook'])->name('day-book');
        Route::get('sales-register', [ReportController::class, 'salesRegister'])->name('sales-register');
        Route::get('purchase-register', [ReportController::class, 'purchaseRegister'])->name('purchase-register');
        Route::get('stock-summary', [ReportController::class, 'stockSummary'])->name('stock-summary');
        Route::get('ledgers/{ledger}/statement', [ReportController::class, 'ledgerStatement'])->name('ledger-statement');
        Route::get('profit-and-loss', [ReportController::class, 'profitAndLoss'])->name('profit-and-loss');
        Route::get('balance-sheet', [ReportController::class, 'balanceSheet'])->name('balance-sheet');
        Route::get('receivables', [ReportController::class, 'receivables'])->name('receivables');
        Route::get('payables', [ReportController::class, 'payables'])->name('payables');
        Route::get('cash-book', [ReportController::class, 'cashBook'])->name('cash-book');
        Route::get('bank-book', [ReportController::class, 'bankBook'])->name('bank-book');
        Route::get('cost-centre-summary', [ReportController::class, 'costCentreSummary'])->name('cost-centre-summary');
        Route::get('gst', [ReportController::class, 'gst'])->name('gst');
    });
});
