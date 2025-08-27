<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // On vérifie deux choses :
        // 1. L'utilisateur est-il bien connecté ?
        // 2. Si oui, son `role_type` est-il bien 'admin' ?
        if ($request->user() && $request->user()->role_type === 'admin') {
            // Si les deux conditions sont vraies, on le laisse continuer sa requête.
            return $next($request);
        }

        // Si l'une des conditions est fausse, on bloque la requête.
        // On renvoie une erreur 403, qui signifie "Accès Interdit".
        return response()->json([
            'message' => 'Accès non autorisé. Cette action est réservée aux administrateurs.'
        ], 403);
    }
}