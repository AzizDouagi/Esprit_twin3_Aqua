<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EspaceController;
use App\Http\Controllers\FrontOfficeController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\InfrastructureController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FrontOfficeController::class, 'home'])->name('front.home');
Route::get('/infrastructures', [FrontOfficeController::class, 'infrastructures'])->name('front.infrastructures.index');
Route::get('/infrastructures/{infrastructure}', [FrontOfficeController::class, 'infrastructureShow'])->name('front.infrastructures.show');

// Espace connecté (utilisateur vérifié)
Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/mon-espace', EspaceController::class)->name('espace.index');

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/mot-de-passe', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profil/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profil/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
    Route::get('/profil/securite', [ProfileController::class, 'security'])->name('profile.security');
    Route::delete('/profil', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Back office : réservé aux rôles disposant de la permission admin.access
Route::prefix('admin')->middleware(['auth', 'verified', 'permission:admin.access'])->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::resource('infrastructures', InfrastructureController::class);
    Route::resource('incidents', IncidentController::class);

    Route::middleware('permission:users.manage')->prefix('utilisateurs')->name('admin.users.')->group(function (): void {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::put('/{user}/role', [UserController::class, 'updateRole'])->name('role');
        Route::put('/{user}/actif', [UserController::class, 'toggleActif'])->name('actif');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });
});
