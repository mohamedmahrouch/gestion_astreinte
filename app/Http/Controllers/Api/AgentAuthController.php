<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AgentAuthController extends Controller
{
    use AuthorizesRequests;

    /**
     * For a secretary/admin: Generates a temporary access code for an agent.
     */
    public function generateAccessCode(Agent $agent)
    {
        $code = random_int(100000, 999999);
        $expiresAt = now()->addMinutes(10);

        AgentSession::create([
            'agent_id' => $agent->id,
            'token' => $code,
            'expires_at' => $expiresAt,
        ]);

        return response()->json([
            'message' => "Un code d'accès a été généré pour {$agent->prenom} {$agent->nom}.",
            'access_code' => $code,
            'expires_at' => $expiresAt->toDateTimeString()
        ]);
    }

    /**
     * For an agent: Logs in with their matricule and access code.
     */
    public function login(Request $request)
    {
        $request->validate([
            'matricule' => 'required|string|exists:agents,matricule',
            'access_code' => 'required|string',
        ]);

        $session = AgentSession::where('token', $request->access_code)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->first();

        $agent = Agent::where('matricule', $request->matricule)->first();

        if (!$session || !$agent || $session->agent_id !== $agent->id) {
            throw ValidationException::withMessages([
                'access_code' => ['Le matricule ou le code d\'accès est invalide ou a expiré.'],
            ]);
        }

        $session->update(['is_active' => false]);

        $apiToken = $agent->createToken('agent-access-token', ['role:agent'])->plainTextToken;

        return response()->json([
            'access_token' => $apiToken,
            'token_type' => 'Bearer',
            'agent' => $agent
        ]);
    }

    /**
     * For an authenticated agent: Retrieves their personal schedule.
     */
    public function getMyPlanning()
    {
        /** @var Agent $agent */
        $agent = Auth::user();

        $planning = $agent->plannings()
            ->with(['periodeAstreinte.service'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($planning);
    }
}