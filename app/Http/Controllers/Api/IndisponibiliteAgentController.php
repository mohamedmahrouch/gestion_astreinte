<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent; // NÉCESSAIRE pour la vérification
use App\Models\IndisponibiliteAgent;
use App\Models\User; // NÉCESSAIRE pour typer l'utilisateur
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class IndisponibiliteAgentController extends Controller
{
        use AuthorizesRequests; 
    public function index(Request $request)
    {
        $this->authorize('viewAny', IndisponibiliteAgent::class);

        /** @var User $user */
        $user = Auth::user();
        $query = IndisponibiliteAgent::with(['agent', 'approuvePar', 'createdBy']);

        if ($user->role_type === 'secretaire') {
            $serviceIds = $user->servicesResponsable()->pluck('id');
            // On récupère les indisponibilités des agents qui sont dans les services de la secrétaire
            $query->whereHas('agent', function ($q) use ($serviceIds) {
                $q->whereIn('service_id', $serviceIds);
            });
        }

        if ($request->has('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        $indisponibilites = $query->orderBy('date_debut', 'desc')->paginate(15);
        return response()->json($indisponibilites);
    }

    public function store(Request $request)
    {
        $this->authorize('create', IndisponibiliteAgent::class);

        $validator = Validator::make($request->all(), [
            'agent_id' => 'required|exists:agents,id',
            // ... autres règles ...
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        /** @var User $user */
        $user = Auth::user();
        // Vérification métier : la secrétaire peut-elle agir sur cet agent ?
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
    
    // N'oubliez pas cette méthode pour les agents !
    public function storeForAgent(Request $request)
    {
        /** @var Agent $agent */
        $agent = Auth::user();

        $validator = Validator::make($request->all(), [
            'type_indisponibilite' => 'required|in:conge_paye,personnel,autre',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'motif' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        $validatedData['agent_id'] = $agent->id;
        $validatedData['saisie_par_agent'] = true;
        $validatedData['statut'] = 'en_attente'; // Doit être approuvé

        $indisponibilite = IndisponibiliteAgent::create($validatedData);

        return response()->json($indisponibilite, 201);
    }

    public function show(IndisponibiliteAgent $indisponibiliteAgent)
    {
        $this->authorize('view', $indisponibiliteAgent);
        return response()->json($indisponibiliteAgent->load(['agent', 'approuvePar', 'createdBy']));
    }

    public function update(Request $request, IndisponibiliteAgent $indisponibiliteAgent)
    {
        $this->authorize('update', $indisponibiliteAgent);
        
        // ... votre validation est bonne ...
        
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
        $this->authorize('delete', $indisponibiliteAgent);
        $indisponibiliteAgent->delete();
        return response()->noContent();
    }
}