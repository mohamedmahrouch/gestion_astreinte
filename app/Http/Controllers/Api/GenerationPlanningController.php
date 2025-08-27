<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\User; // Importez le modèle User
use App\Services\RotationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class GenerationPlanningController extends Controller
{
    /**
     * Déclenche la génération automatique du planning pour un service et un mois donnés.
     * Cette route est déjà protégée par le middleware 'is.admin'.
     */
    public function generer(Request $request, RotationService $rotationService)
    {
        // 1. Validation des données d'entrée
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'mois' => 'required|date_format:Y-m', // Format "2024-09"
        ]);

        /**
         * Note : La vérification que l'utilisateur est bien un admin est déjà faite
         * par le middleware 'is.admin' dans le fichier routes/api.php.
         * Il n'est donc techniquement pas nécessaire de le revérifier ici.
         * Mais par sécurité (défense en profondeur), on pourrait ajouter :
         *
         * if (Auth::user()->role_type !== 'admin') {
         *     return response()->json(['message' => 'Action non autorisée.'], 403);
         * }
         */

        // 2. Récupération des données
        $service = Service::find($request->service_id);
        $dateDebutMois = Carbon::createFromFormat('Y-m', $request->mois)->startOfMonth();

        // 3. Appel du service de génération
        $resultat = $rotationService->genererPlanningPourService(
            $service, 
            $dateDebutMois, 
            Auth::id()
        );

        // 4. Retourner la réponse en fonction du résultat du service
        if ($resultat['status'] === 'success') {
            return response()->json($resultat);
        } else {
            // On retourne une erreur avec le message fourni par le service
            return response()->json($resultat, 422); // 422 Unprocessable Entity
        }
    }
}