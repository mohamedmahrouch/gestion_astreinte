<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\IndisponibiliteAgent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class IndisponibiliteAgentController extends Controller
{
    use AuthorizesRequests; 
    
    public function index(Request $request)
    {
        // ... your index method is correct ...
    }

    // --- THIS METHOD HAS BEEN CORRECTED ---
    public function store(Request $request)
    {
        $this->authorize('create', IndisponibiliteAgent::class);

        // THE VALIDATION RULES HAVE BEEN ADDED HERE
        $validator = Validator::make($request->all(), [
            'agent_id' => 'required|exists:agents,id',
            'type_indisponibilite' => 'required|in:conge_paye,conge_maladie,formation,mission,personnel,autre',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'motif' => 'nullable|string|max:255',
            // We don't need to validate 'statut' or 'created_by' as we set them manually
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        /** @var User $user */
        $user = Auth::user();
        
        if ($user->role_type === 'secretaire') {
            $agent = Agent::find($request->agent_id);
            $serviceIds = $user->servicesResponsable()->pluck('id');
            if (!$serviceIds->contains($agent->service_id)) {
                return response()->json(['message' => 'Action non autorisée. Cet agent ne fait pas partie de vos services.'], 403);
            }
        }
        
        $validatedData = $validator->validated();
        $validatedData['created_by'] = Auth::id();
        $validatedData['statut'] = 'approuve';

        $indisponibilite = IndisponibiliteAgent::create($validatedData);

        return response()->json($indisponibilite->load(['agent', 'createdBy']), 201);
    }
    
    public function storeForAgent(Request $request)
    {
        // ... your storeForAgent method is correct ...
    }

    public function show(IndisponibiliteAgent $indisponibiliteAgent)
    {
        // ... your show method is correct ...
    }

    public function update(Request $request, IndisponibiliteAgent $indisponibiliteAgent)
    {
        $this->authorize('update', $indisponibiliteAgent);
        
        // This method also needs its validation rules to be complete
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
        
        if (isset($validatedData['statut']) && $validatedData['statut'] !== 'en_attente') {
            $validatedData['approuve_par'] = Auth::id();
            $validatedData['date_approbation'] = now();
        }

        $indisponibiliteAgent->update($validatedData);

        return response()->json($indisponibiliteAgent->load(['agent', 'approuvePar', 'createdBy']));
    }

    public function destroy(IndisponibiliteAgent $indisponibiliteAgent)
    {
        // its correct honey
    }
}