<?php

// Fichier: routes/api.php

use App\Http\Controllers\Api\AgentController;
use App\Http\Controllers\Api\AgentAuthController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GenerationPlanningController;
use App\Http\Controllers\Api\IndisponibiliteAgentController;
use App\Http\Controllers\Api\PeriodeAstreinteController;
use App\Http\Controllers\Api\PlanningController;
use App\Http\Controllers\Api\PublicPlanningController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\IsAdminMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- Routes Publiques ---
Route::post('/login', [AuthController::class, 'login'])->name('login'); // Named for error handling
Route::post('/agent/login', [AgentAuthController::class, 'login']);
Route::get('/public/plannings/{token}', [PublicPlanningController::class, 'showByToken']);

// --- Routes Protégées (nécessitent un token valide) ---
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // --- Routes pour Admins et Secrétaires (protégées par Policies) ---
    Route::apiResource('services', ServiceController::class);
    Route::apiResource('agents', AgentController::class);
    Route::apiResource('periodes-astreinte', PeriodeAstreinteController::class);
    Route::apiResource('indisponibilites-agents', IndisponibiliteAgentController::class);
    Route::apiResource('plannings', PlanningController::class);
    Route::post('/agents/{agent}/generate-code', [AgentAuthController::class, 'generateAccessCode']);
    Route::post('/agent/me/indisponibilites', [IndisponibiliteAgentController::class, 'storeForAgent']);
    Route::get('/agent/me/planning', [AgentAuthController::class, 'getMyPlanning']);

    // --- Routes pour Admins UNIQUEMENT ---
    Route::middleware(IsAdminMiddleware::class)->group(function () {
        Route::post('/plannings/generer', [GenerationPlanningController::class, 'generer']);
        Route::apiResource('users', UserController::class);
    });
});