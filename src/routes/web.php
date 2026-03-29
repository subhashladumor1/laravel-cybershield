<?php

use Illuminate\Support\Facades\Route;
use CyberShield\Http\Controllers\Dashboard\DashboardController;

Route::prefix('cybershield')->middleware(['web'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('cybershield.dashboard');
    Route::post('/dashboard/refresh', [DashboardController::class, 'refresh'])->name('cybershield.refresh');

    // Security Logs
    Route::get('/logs', [\CyberShield\Http\Controllers\Dashboard\LogController::class, 'index'])->name('cybershield.logs.index');
    Route::get('/logs/export/csv', [\CyberShield\Http\Controllers\Dashboard\LogController::class, 'exportCsv'])->name('cybershield.logs.export.csv');
    Route::get('/logs/export/json', [\CyberShield\Http\Controllers\Dashboard\LogController::class, 'exportJson'])->name('cybershield.logs.export.json');
});

