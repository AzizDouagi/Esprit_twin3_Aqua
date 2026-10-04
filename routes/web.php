<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FrontOfficeController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\InfrastructureController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FrontOfficeController::class, 'home'])->name('front.home');
Route::get('/infrastructures', [FrontOfficeController::class, 'infrastructures'])->name('front.infrastructures.index');
Route::get('/infrastructures/{infrastructure}', [FrontOfficeController::class, 'infrastructureShow'])->name('front.infrastructures.show');

Route::prefix('admin')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::resource('infrastructures', InfrastructureController::class);
    Route::resource('incidents', IncidentController::class);
});
