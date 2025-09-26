<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PeriodeAstreinte;
use App\Models\Service; // pour les vérifications
use App\Models\User;   // typer l'utilisateur
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class PeriodeAstreinteController extends Controller
{
      use AuthorizesRequests; 
    /**
     * Affiche une liste de périodes d'astreinte.
     * La liste est filtrée pour les secrétaires pour ne montrer que les périodes de leurs services.
     */
    public function index(Request $request)
    {
        // Autorise la vue (permis aux admins et secrétaires par la Policy)
        $this->authorize('viewAny', PeriodeAstreinte::class);

        /** @var User $user */
        $user = Auth::user();
        $query = PeriodeAstreinte::with(['service', 'createdBy']);

        // Si l'utilisateur est une secrétaire, on force le filtre sur ses services
        if ($user->role_type === 'secretaire') {
            $serviceIds = $user->services()->pluck('services.id');
            $query->whereIn('service_id', $serviceIds);
        }

        // On applique aussi le filtre de la requête s'il est présent (utile pour un admin)
        if ($request->has('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        $periodes = $query->orderBy('date_debut', 'desc')->paginate(15);
        
        return response()->json($periodes);
    }

    /**
     * Crée une nouvelle période d'astreinte.
     */
    public function store(Request $request)
    {
        // 1. Autorisation générale de créer
        $this->authorize('create', PeriodeAstreinte::class);

        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'type_periode' => 'required|in:hebdomadaire,weekend,ferie,nuit',
            'date_debut' => 'required|date',
            'heure_debut' => 'required|date_format:H:i:s', // Format HH:MM:SS
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'heure_fin' => 'required|date_format:H:i:s',
            'nb_agents_requis' => 'sometimes|integer|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        /** @var User $user */
        $user = Auth::user();
        
        // 2. Vérification métier : Une secrétaire ne peut créer que dans son propre service.
        if ($user->role_type === 'secretaire') {
            $service = Service::find($request->service_id);
            if (!$service || $service->secretaire_responsable_id !== $user->id) {
                 return response()->json(['message' => 'Action non autorisée. Vous pouvez uniquement créer des périodes pour les services dont vous êtes responsable.'], 403);
            }
        }

        $validatedData = $validator->validated();
        $validatedData['created_by'] = $user->id;

        $periode = PeriodeAstreinte::create($validatedData);

        return response()->json($periode->load(['service', 'createdBy']), 201);
    }

    /**
     * Affiche une période d'astreinte spécifique.
     */
    public function show(PeriodeAstreinte $periodeAstreinte)
    {
        // Autorisation : L'utilisateur a-t-il le droit de voir CETTE période ?
        $this->authorize('view', $periodeAstreinte);

        return response()->json($periodeAstreinte->load(['service', 'createdBy']));
    }

    /**
     * Met à jour une période d'astreinte.
     */
    public function update(Request $request, PeriodeAstreinte $periodeAstreinte)
    {
        // Autorisation : L'utilisateur a-t-il le droit de modifier CETTE période ?
        $this->authorize('update', $periodeAstreinte);

        $validator = Validator::make($request->all(), [
            'service_id' => 'sometimes|required|exists:services,id',
            'type_periode' => 'sometimes|required|in:hebdomadaire,weekend,ferie,nuit',
            'date_debut' => 'sometimes|required|date',
            'heure_debut' => 'sometimes|required|date_format:H:i:s',
            'date_fin' => 'sometimes|required|date|after_or_equal:date_debut',
            'heure_fin' => 'sometimes|required|date_format:H:i:s',
            'nb_agents_requis' => 'sometimes|integer|min:1',
            'description' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        /** @var User $user */
        $user = Auth::user();

        // Vérification métier : Une secrétaire ne peut pas déplacer une période vers un autre service.
        if ($request->has('service_id') && $request->service_id != $periodeAstreinte->service_id && $user->role_type === 'secretaire') {
            return response()->json(['message' => 'Action non autorisée. Vous ne pouvez pas changer le service d\'une période existante.'], 403);
        }

        $periodeAstreinte->update($validator->validated());

        return response()->json($periodeAstreinte->load(['service', 'createdBy']));
    }

    /**
     * Supprime une période d'astreinte.
     */
    public function destroy(PeriodeAstreinte $periodeAstreinte)
    {
        // Autorisation : L'utilisateur a-t-il le droit de supprimer CETTE période ?
        $this->authorize('delete', $periodeAstreinte);

        $periodeAstreinte->delete();
        return response()->noContent();
    }
}