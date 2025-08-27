<?php

// Fichier: routes/api.php

use App\Http\Controllers\Api\AgentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GenerationPlanningController;
use App\Http\Controllers\Api\IndisponibiliteAgentController;
use App\Http\Controllers\Api\PeriodeAstreinteController;
use App\Http\Controllers\Api\PlanningController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\UserController; // <-- 1. AJOUTER CETTE LIGNE
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('services', ServiceController::class);
    Route::apiResource('agents', AgentController::class);
    Route::apiResource('periodes-astreinte', PeriodeAstreinteController::class);
    Route::apiResource('indisponibilites-agents', IndisponibiliteAgentController::class);
    Route::apiResource('plannings', PlanningController::class);

    Route::middleware('is.admin')->group(function () {
        Route::post('/plannings/generer', [GenerationPlanningController::class, 'generer']);
        Route::apiResource('users', UserController::class);
    });

});