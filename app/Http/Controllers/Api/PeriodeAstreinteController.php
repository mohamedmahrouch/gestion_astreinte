<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PeriodeAstreinte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PeriodeAstreinteController extends Controller
{
    public function index(Request $request)
    {
        // On permet de filtrer les périodes par service_id si fourni dans l'URL
        // Exemple d'appel: GET /api/periodes-astreinte?service_id=1
        $query = PeriodeAstreinte::with(['service', 'createdBy']);

        if ($request->has('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        $periodes = $query->orderBy('date_debut', 'desc')->paginate(15);
        
        return response()->json($periodes);
    }

    public function store(Request $request)
    {
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

        $validatedData = $validator->validated();
        $validatedData['created_by'] = Auth::id();

        $periode = PeriodeAstreinte::create($validatedData);

        return response()->json($periode->load(['service', 'createdBy']), 201);
    }

    public function show(PeriodeAstreinte $periodeAstreinte)
    {
        return response()->json($periodeAstreinte->load(['service', 'createdBy']));
    }

    public function update(Request $request, PeriodeAstreinte $periodeAstreinte)
    {
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

        $periodeAstreinte->update($validator->validated());

        return response()->json($periodeAstreinte->load(['service', 'createdBy']));
    }

    public function destroy(PeriodeAstreinte $periodeAstreinte)
    {
        $periodeAstreinte->delete();
        return response()->noContent();
    }
}