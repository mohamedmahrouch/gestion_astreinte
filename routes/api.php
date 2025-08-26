<?php

// Fichier: routes/api.php

use App\Http\Controllers\Api\AgentController; // <-- Ajoutez-les si elles manquent
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\IndisponibiliteAgentController;
use App\Http\Controllers\Api\PeriodeAstreinteController;
use App\Http\Controllers\Api\PlanningController;
use App\Http\Controllers\Api\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// LA LIGNE MAGIQUE À AJOUTER EST ICI
use App\Http\Controllers\Api\GenerationPlanningController;


// Route publique pour la connexion
Route::post('/login', [AuthController::class, 'login']);

// ---- NOUVEAU CODE CI-DESSOUS ----

// Routes protégées qui nécessitent une authentification
Route::middleware('auth:sanctum')->group(function () {
    // Cette route permet de vérifier qui est l'utilisateur connecté
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('services', ServiceController::class);
    Route::apiResource('agents', AgentController::class);
    Route::apiResource('periodes-astreinte', PeriodeAstreinteController::class);
    Route::apiResource('indisponibilites-agents', IndisponibiliteAgentController::class);
    Route::apiResource('plannings', PlanningController::class);
    Route::post('/plannings/generer', [GenerationPlanningController::class, 'generer']);
});