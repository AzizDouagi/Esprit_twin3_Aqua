<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\IncidentController;
use App\Http\Controllers\Api\V1\InfrastructureController;
use App\Http\Controllers\Api\V1\MesureController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::post('auth/token', [AuthController::class, 'token'])->middleware('throttle:10,1')->name('auth.token');

    Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function (): void {
        Route::delete('auth/token', [AuthController::class, 'revoke'])->name('auth.revoke');
        Route::get('me', [AuthController::class, 'me'])->name('me');

        Route::apiResource('infrastructures', InfrastructureController::class);
        Route::get('infrastructures/{infrastructure}/mesures', [MesureController::class, 'index'])->name('infrastructures.mesures.index');
        Route::post('infrastructures/{infrastructure}/mesures', [MesureController::class, 'store'])->name('infrastructures.mesures.store');
        Route::get('mesures/{mesure}', [MesureController::class, 'show'])->name('mesures.show');
        Route::apiResource('incidents', IncidentController::class);
    });
});
