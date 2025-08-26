<?php

// Fichier: routes/api.php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ServiceController; // 1. Importer le nouveau contrôleur
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route publique pour la connexion
Route::post('/login', [AuthController::class, 'login']);

// ---- NOUVEAU CODE CI-DESSOUS ----

// Routes protégées qui nécessitent une authentification
Route::middleware('auth:sanctum')->group(function () {
    // Cette route permet de vérifier qui est l'utilisateur connecté
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // 2. Ajouter cette ligne magique
    Route::apiResource('services', ServiceController::class);
    Route::apiResource('agents', App\Http\Controllers\Api\AgentController::class);
});