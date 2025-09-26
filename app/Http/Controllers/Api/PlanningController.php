<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Agent;
use App\Models\PeriodeAstreinte;
use App\Models\Planning;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PlanningController extends Controller
{
      use AuthorizesRequests;
    public function index(Request $request)
    {

        $this->authorize('viewAny', Planning::class);
        
        /** @var User $user */
        $user = Auth::user();
        $query = Planning::with(['periodeAstreinte.service', 'agent', 'agentRemplacant', 'createdBy']);

        // Si c'est une secrétaire, on filtre sur ses services
        if ($user instanceof User && $user->role_type === 'secretaire') {            
            // $serviceIds = $user->servicesResponsable()->pluck('id');
            $serviceIds = $user->services()->pluck('services.id');
            $query->whereHas('periodeAstreinte', function ($q) use ($serviceIds) {
                $q->whereIn('service_id', $serviceIds);

            });
        }
        
        // ... votre logique de filtre existante ...
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
        $this->authorize('create', Planning::class);

        $validator = Validator::make($request->all(), [
            'periode_astreinte_id' => ['required','exists:periodes_astreinte,id',
                Rule::unique('plannings')->where(fn ($query) => $query->where('agent_id', $request->agent_id)),
            ],
            'agent_id' => 'required|exists:agents,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        /** @var User $user */
        $user = Auth::user();
        // Vérification métier : la secrétaire peut-elle créer un planning pour cette période ?
        if ($user->role_type === 'secretaire') {
            $periode = PeriodeAstreinte::find($request->periode_astreinte_id);
            $serviceIds = $user->services()->pluck('id');
            if (!$serviceIds->contains($periode->service_id)) {
                return response()->json(['message' => 'Action non autorisée. Vous ne pouvez créer des affectations que pour vos services.'], 403);
            }
        }

        $validatedData = $validator->validated();
        $validatedData['created_by'] = $user->id;
        $validatedData['statut'] = 'planifie';

        $planning = Planning::create($validatedData);

        return response()->json($planning->load(['periodeAstreinte', 'agent']), 201);
    }

    public function show(Planning $planning)
    {
        $this->authorize('view', $planning);
        return response()->json($planning->load(['periodeAstreinte.service', 'agent', 'agentRemplacant', 'createdBy']));
    }

    public function update(Request $request, Planning $planning)
    {
        $this->authorize('update', $planning);

        $validator = Validator::make($request->all(), [
            'agent_remplacant_id' => 'nullable|exists:agents,id|different:agent_id',
            'statut' => 'sometimes|required|in:planifie,confirme,en_cours,termine,annule',
            'commentaire_admin' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Vérification métier : l'agent remplaçant doit être du même service
        if ($request->has('agent_remplacant_id')) {
            $agentRemplacant = Agent::find($request->agent_remplacant_id);
            if ($agentRemplacant->service_id !== $planning->agent->service_id) {
                return response()->json(['message' => 'L\'agent remplaçant doit appartenir au même service que l\'agent initial.'], 422);
            }
        }
        
        $validatedData = $validator->validated();
        if ($request->has('agent_remplacant_id')) {
            $validatedData['secretaire_remplacant_id'] = Auth::id();
        }

        $planning->update($validatedData);

        return response()->json($planning->load(['periodeAstreinte', 'agent', 'agentRemplacant']));
    }

    public function destroy(Planning $planning)
    {
        $this->authorize('delete', $planning);
        $planning->delete();
        return response()->noContent();
    }
}