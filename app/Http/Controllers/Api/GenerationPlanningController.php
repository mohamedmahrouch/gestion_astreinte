<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\RotationService; // Importez notre service
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GenerationPlanningController extends Controller
{
    public function generer(Request $request, RotationService $rotationService)
    {
        // 1. Validation
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'mois' => 'required|date_format:Y-m', // Format "2024-09"
        ]);

        // 2. Récupération des données
        $service = Service::find($request->service_id);
        $dateDebutMois = Carbon::createFromFormat('Y-m', $request->mois)->startOfMonth();

        // 3. Appel du service de génération
        $resultat = $rotationService->genererPlanningPourService(
            $service, 
            $dateDebutMois, 
            Auth::id()
        );

        // 4. Retourner la réponse
        if ($resultat['status'] === 'success') {
            return response()->json($resultat);
        } else {
            return response()->json($resultat, 422); // Unprocessable Entity
        }
    }
}