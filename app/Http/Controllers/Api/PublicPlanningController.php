<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;

class PublicPlanningController extends Controller
{
    public function showByToken($token)
    {
        $agent = Agent::where('public_token', $token)->firstOrFail();
        
        $planning = $agent->plannings()
            ->with(['periodeAstreinte.service'])
            ->orderBy('periodeAstreinte.date_debut', 'asc')
            ->get();

        return response()->json([
            'agent' => ['nom' => $agent->nom, 'prenom' => $agent->prenom],
            'plannings' => $planning->map(function ($p) {
                return [
                    'service' => $p->periodeAstreinte->service->nom,
                    'date_debut' => $p->periodeAstreinte->date_debut,
                    'date_fin' => $p->periodeAstreinte->date_fin,
                    'description' => $p->periodeAstreinte->description,
                ];
            })
        ]);
    }
}