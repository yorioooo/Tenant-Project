<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionDashboardController;

Route::get('/dashboard', [TransactionDashboardController::class, 'dashboard'])->name('dashboard');
Route::get('/dashboard/ranking', [TransactionDashboardController::class, 'getRanking'])->name('dashboard.ranking');
Route::get('/detail-transaction', [TransactionDashboardController::class, 'detailTransaction'])->name('detail-transaction');
Route::get('/transactions', [TransactionDashboardController::class, 'index'])->name('transactions');
Route::get('/transaction/{id}', [TransactionDashboardController::class, 'show'])->name('transaction.show');
Route::delete('/transaction/{id}', [TransactionDashboardController::class, 'delete'])->name('transaction.delete');
Route::get('/transaction/sync/{id}', [TransactionDashboardController::class, 'sync'])->name('transaction.sync');
Route::get('/sync', [TransactionDashboardController::class, 'syncMonitoring'])->name('sync');