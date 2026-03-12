<?php

declare(strict_types=1);

use App\Presentation\Http\Controllers\Wallet\DashboardController;
use App\Presentation\Http\Controllers\Wallet\DepositController;
use App\Presentation\Http\Controllers\Wallet\ReversalController;
use App\Presentation\Http\Controllers\Wallet\TransactionController;
use App\Presentation\Http\Controllers\Wallet\TransferController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/deposit', [DepositController::class, 'create'])->name('deposit.create');
    Route::post('/deposit', [DepositController::class, 'store'])->name('deposit.store');

    Route::get('/transfer', [TransferController::class, 'create'])->name('transfer.create');
    Route::post('/transfer', [TransferController::class, 'store'])->name('transfer.store');

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{id}', [TransactionController::class, 'show'])->name('transactions.show');

    Route::post('/transactions/{id}/reverse', [ReversalController::class, 'store'])->name('transactions.reverse');
});
