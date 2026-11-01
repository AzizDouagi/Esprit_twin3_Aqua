<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AI\AnomalyController;
use App\Http\Controllers\AI\ChatController;
use App\Http\Controllers\AI\ClassifyIncidentController;
use App\Http\Controllers\AI\IncidentSummaryController;
use App\Http\Controllers\AI\MonthlyReportController;
use App\Http\Controllers\AI\RiskScoreController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeclarationController;
use App\Http\Controllers\EspaceController;
use App\Http\Controllers\FrontOfficeController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\IncidentCommentaireController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\IncidentExportController;
use App\Http\Controllers\IncidentPieceJointeController;
use App\Http\Controllers\InfrastructureController;
use App\Http\Controllers\InfrastructureCorbeilleController;
use App\Http\Controllers\InfrastructureExportController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FrontOfficeController::class, 'home'])->name('front.home');
Route::get('/carte', [FrontOfficeController::class, 'carte'])->name('carte');
Route::get('/infrastructures', [FrontOfficeController::class, 'infrastructures'])->name('front.infrastructures.index');
Route::get('/infrastructures/{infrastructure}', [FrontOfficeController::class, 'infrastructureShow'])->name('front.infrastructures.show');

// Espace connecté (utilisateur vérifié)
Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/mon-espace', EspaceController::class)->name('espace.index');

    Route::get('/mes-signalements', [DeclarationController::class, 'index'])->name('espace.signalements');
    Route::get('/mes-signalements/{incident}', [DeclarationController::class, 'show'])->name('espace.signalements.show');
    Route::get('/declarer-un-incident', [DeclarationController::class, 'create'])->name('espace.declarer');
    Route::post('/declarer-un-incident', [DeclarationController::class, 'store'])->name('espace.declarer.store');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/tout-lire', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('/notifications/{id}/lire', [NotificationController::class, 'read'])->name('notifications.read');

    // Commentaires et pièces jointes : accessibles au déclarant comme au personnel (policy IncidentPolicy@comment)
    Route::post('/incidents/{incident}/commentaires', [IncidentCommentaireController::class, 'store'])->name('incidents.commentaires.store');
    Route::post('/incidents/{incident}/pieces-jointes', [IncidentPieceJointeController::class, 'store'])->name('incidents.pieces-jointes.store');
    Route::get('/incidents/{incident}/pieces-jointes/{pieceJointe}', [IncidentPieceJointeController::class, 'show'])->name('incidents.pieces-jointes.show');
    Route::delete('/incidents/{incident}/pieces-jointes/{pieceJointe}', [IncidentPieceJointeController::class, 'destroy'])->name('incidents.pieces-jointes.destroy');

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
    Route::get('infrastructures/export/csv', [InfrastructureExportController::class, 'csv'])->name('infrastructures.export-csv');
    Route::get('infrastructures/export/pdf', [InfrastructureExportController::class, 'pdf'])->name('infrastructures.export-pdf');
    Route::get('infrastructures/corbeille', [InfrastructureCorbeilleController::class, 'index'])->name('infrastructures.corbeille');
    Route::post('infrastructures/corbeille/{id}/restaurer', [InfrastructureCorbeilleController::class, 'restore'])->name('infrastructures.restore');
    Route::delete('infrastructures/corbeille/{id}', [InfrastructureCorbeilleController::class, 'forceDelete'])->name('infrastructures.force-delete');
    Route::get('infrastructures/{infrastructure}/fiche-pdf', [InfrastructureExportController::class, 'fiche'])->name('infrastructures.fiche-pdf');
    Route::resource('infrastructures', InfrastructureController::class);

    Route::middleware('permission:maintenances.manage')->group(function (): void {
        Route::resource('maintenances', MaintenanceController::class)->only(['index', 'store', 'update', 'destroy']);
    });
    Route::get('incidents/export/csv', [IncidentExportController::class, 'csv'])->name('incidents.export-csv');
    Route::get('incidents/export/pdf', [IncidentExportController::class, 'pdf'])->name('incidents.export-pdf');
    Route::patch('incidents/{incident}/statut', [IncidentController::class, 'changerStatut'])->name('incidents.statut');
    Route::patch('incidents/{incident}/assigner', [IncidentController::class, 'assigner'])->name('incidents.assigner');
    Route::resource('incidents', IncidentController::class);

    Route::middleware('permission:users.manage')->prefix('utilisateurs')->name('admin.users.')->group(function (): void {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::put('/{user}/role', [UserController::class, 'updateRole'])->name('role');
        Route::put('/{user}/actif', [UserController::class, 'toggleActif'])->name('actif');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });
});

Route::middleware(['auth', 'verified', 'permission:ai.use'])->prefix('ia')->name('ai.')->group(function (): void {
    Route::post('classer', ClassifyIncidentController::class)->name('classify');
});
Route::middleware(['auth', 'verified', 'permission:admin.access', 'permission:ai.use'])->prefix('admin/ia')->name('ai.')->group(function (): void {
    Route::post('incidents/{incident}/resume', IncidentSummaryController::class)->name('summary');
    Route::get('infrastructures/{infrastructure}/rapport', MonthlyReportController::class)->name('report');
    Route::get('infrastructures/{infrastructure}/risque', RiskScoreController::class)->name('risk');
    Route::post('infrastructures/{infrastructure}/risque', RiskScoreController::class)->name('risk.explain');
    Route::get('chat', [ChatController::class, 'index'])->name('chat');
    Route::post('chat', [ChatController::class, 'store'])->name('chat.store');
    Route::get('anomalies', [AnomalyController::class, 'index'])->name('anomalies');
    Route::post('anomalies/{suggestion}/accepter', [AnomalyController::class, 'accept'])->name('anomalies.accept');
});

Route::get('/health', HealthController::class)->name('health');
