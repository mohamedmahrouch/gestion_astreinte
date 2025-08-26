<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Planning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PlanningController extends Controller
{
    public function index(Request $request)
    {
        // Permet de filtrer par période ou par agent
        $query = Planning::with(['periodeAstreinte.service', 'agent', 'agentRemplacant', 'createdBy']);

        if ($request->has('periode_astreinte_id')) {
            $query->where('periode_astreinte_id', $request->periode_astreinte_id);
        }
        if ($request->has('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        $plannings = $query->paginate(30);
        return response()->json($plannings);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'periode_astreinte_id' => [
                'required',
                'exists:periodes_astreinte,id',
                // Règle d'unicité pour empêcher le doublon agent/période
                Rule::unique('plannings')->where(function ($query) use ($request) {
                    return $query->where('agent_id', $request->agent_id);
                }),
            ],
            'agent_id' => 'required|exists:agents,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        $validatedData['created_by'] = Auth::id();
        $validatedData['statut'] = 'planifie';

        $planning = Planning::create($validatedData);

        return response()->json($planning->load(['periodeAstreinte', 'agent']), 201);
    }

    public function show(Planning $planning)
    {
        return response()->json($planning->load(['periodeAstreinte.service', 'agent', 'agentRemplacant', 'createdBy']));
    }

    public function update(Request $request, Planning $planning)
    {
        // Mise à jour principale: remplacer un agent ou changer le statut
        $validator = Validator::make($request->all(), [
            'agent_remplacant_id' => 'nullable|exists:agents,id|different:agent_id',
            'statut' => 'sometimes|required|in:planifie,confirme,en_cours,termine,annule',
            'commentaire_admin' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $validatedData = $validator->validated();
        
        // Logique de remplacement
        if ($request->has('agent_remplacant_id')) {
            $validatedData['secretaire_remplacant_id'] = Auth::id();
        }

        $planning->update($validatedData);

        return response()->json($planning->load(['periodeAstreinte', 'agent', 'agentRemplacant']));
    }

    public function destroy(Planning $planning)
    {
        $planning->delete();
        return response()->noContent();
    }
}