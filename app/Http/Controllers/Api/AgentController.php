<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AgentController extends Controller
{
    /**
     * Affiche une liste paginée des agents.
     */
    public function index()
    {
        // On charge les relations 'service' et 'createdBy' pour avoir les détails
        $agents = Agent::with(['service', 'createdBy'])->paginate(15);
        return response()->json($agents);
    }

    /**
     * Crée un nouvel agent.
     */
    public function store(Request $request)
    {
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

        $validatedData = $validator->validated();
        // On ajoute automatiquement l'ID de l'utilisateur connecté comme créateur
        $validatedData['created_by'] = Auth::id();

        $agent = Agent::create($validatedData);

        return response()->json($agent->load(['service', 'createdBy']), 201);
    }

    /**
     * Affiche les détails d'un agent spécifique.
     */
    public function show(Agent $agent)
    {
        // On charge les relations pour avoir un résultat complet
        return response()->json($agent->load(['service', 'createdBy']));
    }

    /**
     * Met à jour un agent existant.
     */
    public function update(Request $request, Agent $agent)
    {
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

        $agent->update($validator->validated());

        return response()->json($agent->load(['service', 'createdBy']));
    }

    /**
     * Supprime un agent.
     */
    public function destroy(Agent $agent)
    {
        $agent->delete();
        return response()->noContent();
    }
}