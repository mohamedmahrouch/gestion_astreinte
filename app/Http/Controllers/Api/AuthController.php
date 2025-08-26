<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Gère la tentative de connexion d'un utilisateur.
     */
    public function login(Request $request)
    {
        // 1. Valider les données d'entrée
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Chercher l'utilisateur par email
        $user = User::where('email', $request->email)->first();

        // 3. Vérifier si l'utilisateur existe et si le mot de passe est correct
// Vérifier si l'utilisateur existe, si le mot de passe est correct et si le compte est actif
if (! $user || ! Hash::check($request->password, $user->password) || ! $user->is_active) {
    // Si l'une des conditions échoue, on retourne une erreur 401 (Unauthorized)
    return response()->json([
        'message' => 'Les identifiants fournis sont incorrects ou le compte est inactif.'
    ], 401); 
}
        // 4. (Optionnel mais recommandé) Vérifier si le compte est actif
        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Ce compte a été désactivé.'],
            ]);
        }

        // 5. Créer un token d'API pour l'utilisateur
        $token = $user->createToken('auth-token')->plainTextToken;

        // 6. Retourner la réponse JSON avec le token et les informations de l'utilisateur
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'email' => $user->email,
                'role' => $user->role_type,
            ]
        ]);
    }
}