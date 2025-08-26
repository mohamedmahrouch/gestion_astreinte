<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IndisponibiliteAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class IndisponibiliteAgentController extends Controller
{
    public function index(Request $request)
    {
        $query = IndisponibiliteAgent::with(['agent', 'approuvePar', 'createdBy']);

        if ($request->has('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        $indisponibilites = $query->orderBy('date_debut', 'desc')->paginate(15);
        return response()->json($indisponibilites);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agent_id' => 'required|exists:agents,id',
            'type_indisponibilite' => 'required|in:conge_paye,conge_maladie,formation,mission,personnel,autre',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'motif' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        $validatedData['created_by'] = Auth::id();
        $validatedData['statut'] = 'approuve'; // Un admin/secrétaire approuve directement

        $indisponibilite = IndisponibiliteAgent::create($validatedData);

        return response()->json($indisponibilite->load(['agent', 'createdBy']), 201);
    }

    public function show(IndisponibiliteAgent $indisponibilitesAgent)
    {
        return response()->json($indisponibilitesAgent->load(['agent', 'approuvePar', 'createdBy']));
    }

    public function update(Request $request, IndisponibiliteAgent $indisponibilitesAgent)
    {
        $validator = Validator::make($request->all(), [
            'type_indisponibilite' => 'sometimes|required|in:conge_paye,conge_maladie,formation,mission,personnel,autre',
            'date_debut' => 'sometimes|required|date',
            'date_fin' => 'sometimes|required|date|after_or_equal:date_debut',
            'motif' => 'nullable|string|max:255',
            'statut' => 'sometimes|required|in:en_attente,approuve,refuse'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $validatedData = $validator->validated();
        // Si on change le statut, on enregistre qui a fait l'approbation
        if (isset($validatedData['statut']) && $validatedData['statut'] !== 'en_attente') {
            $validatedData['approuve_par'] = Auth::id();
            $validatedData['date_approbation'] = now();
        }

        $indisponibilitesAgent->update($validatedData);

        return response()->json($indisponibilitesAgent->load(['agent', 'approuvePar', 'createdBy']));
    }

    public function destroy(IndisponibiliteAgent $indisponibilitesAgent)
    {
        $indisponibilitesAgent->delete();
        return response()->noContent();
    }
}