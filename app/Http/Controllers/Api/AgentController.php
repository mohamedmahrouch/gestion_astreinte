<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Service; // NÉCESSAIRE pour les vérifications
use App\Models\User;   // NÉCESSAIRE pour typer l'utilisateur
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class AgentController extends Controller
{
     use AuthorizesRequests; 
    /**
     * Affiche une liste paginée des agents.
     * Pour une secrétaire, la liste est filtrée pour ne montrer que les agents de ses services.
     */
    public function index()
    {
        // 1. Autorisation : L'utilisateur a-t-il le droit de voir la liste des agents ?
        // La policy autorise les admins et les secrétaires.
        $this->authorize('viewAny', Agent::class);

        /** @var User $user */
        $user = Auth::user();
        $query = Agent::with(['service', 'createdBy']);

        // 2. Logique métier : Si l'utilisateur est une secrétaire, on restreint la requête.
        if ($user->role_type === 'secretaire') {
            $serviceIds = $user->servicesResponsable()->pluck('id');
            $query->whereIn('service_id', $serviceIds);
        }

        $agents = $query->paginate(15);
        return response()->json($agents);
    }

    /**
     * Crée un nouvel agent.
     */
    public function store(Request $request)
    {
        // 1. Autorisation générale de créer.
        $this->authorize('create', Agent::class);

        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'matricule' => 'required|string|max:50|unique:agents,matricule',
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'telephone_principal' => 'required|string|max:20',
            'telephone_secours' => 'nullable|string|max:20',
            'email_professionnel' => 'nullable|email|max:255',
            'date_embauche' => 'nullable|date',
            'poste' => 'nullable|string|max:100',
            'niveau_competence' => 'sometimes|in:junior,senior,expert',
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
                return response()->json(['message' => 'Action non autorisée. Vous pouvez uniquement ajouter des agents dans les services dont vous êtes responsable.'], 403);
            }
        }

        $validatedData = $validator->validated();
        $validatedData['created_by'] = $user->id;

        $agent = Agent::create($validatedData);

        return response()->json($agent->load(['service', 'createdBy']), 201);
    }

    /**
     * Affiche les détails d'un agent spécifique.
     */
    public function show(Agent $agent)
    {
        // Autorisation : L'utilisateur a-t-il le droit de voir CET agent ?
        // La policy vérifiera si c'est un admin ou la secrétaire du bon service.
        $this->authorize('view', $agent);

        return response()->json($agent->load(['service', 'createdBy']));
    }

    /**
     * Met à jour un agent existant.
     */
    public function update(Request $request, Agent $agent)
    {
        // 1. Autorisation : L'utilisateur a-t-il le droit de modifier CET agent ?
        $this->authorize('update', $agent);

        $validator = Validator::make($request->all(), [
            'service_id' => 'sometimes|required|exists:services,id',
            'matricule' => 'sometimes|required|string|max:50|unique:agents,matricule,' . $agent->id,
            'nom' => 'sometimes|required|string|max:100',
            'prenom' => 'sometimes|required|string|max:100',
            'telephone_principal' => 'sometimes|required|string|max:20',
            'telephone_secours' => 'nullable|string|max:20',
            'email_professionnel' => 'nullable|email|max:255',
            'date_embauche' => 'nullable|date',
            'poste' => 'nullable|string|max:100',
            'niveau_competence' => 'sometimes|in:junior,senior,expert',
            'is_disponible_astreinte' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        /** @var User $user */
        $user = Auth::user();

        // 2. Vérification métier : Si on change le service, la secrétaire doit être responsable du NOUVEAU service.
        if ($request->has('service_id') && $request->service_id != $agent->service_id && $user->role_type === 'secretaire') {
            $newService = Service::find($request->service_id);
            if (!$newService || $newService->secretaire_responsable_id !== $user->id) {
                 return response()->json(['message' => 'Action non autorisée. Vous ne pouvez pas déplacer un agent vers un service que vous ne gérez pas.'], 403);
            }
        }

        $agent->update($validator->validated());

        return response()->json($agent->load(['service', 'createdBy']));
    }

    /**
     * Supprime un agent.
     */
    public function destroy(Agent $agent)
    {
        // Autorisation : L'utilisateur a-t-il le droit de supprimer CET agent ?
        $this->authorize('delete', $agent);

        $agent->delete();
        return response()->noContent();
    }
}