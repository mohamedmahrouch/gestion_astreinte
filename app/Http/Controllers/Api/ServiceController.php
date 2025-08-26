<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; // Important pour la validation

class ServiceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/services",
     *     summary="Récupérer tous les services",
     *     @OA\Response(response="200", description="Liste de tous les services")
     * )
     */
    public function index()
    {
        // Récupère les services avec les infos de la secrétaire.
        // paginate() est mieux que get() pour de longues listes.
        $services = Service::with('secretaireResponsable')->paginate(15);

        return response()->json($services);
    }

    /**
     * @OA\Post(
     *     path="/api/services",
     *     summary="Créer un nouveau service",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="nom", type="string", example="Service Informatique"),
     *             @OA\Property(property="code_service", type="string", example="INFO")
     *         )
     *     ),
     *     @OA\Response(response="201", description="Service créé avec succès"),
     *     @OA\Response(response="422", description="Erreur de validation")
     * )
     */
    public function store(Request $request)
    {
        // 1. Validation des données entrantes
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:100',
            'code_service' => 'required|string|max:20|unique:services,code_service',
            'description' => 'nullable|string',
            'secretaire_responsable_id' => 'nullable|exists:users,id',
            'email_contact' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 2. Création du service
        $service = Service::create($validator->validated());

        // 3. Retourner le service créé avec un code 201 (Created)
        return response()->json($service, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/services/{id}",
     *     summary="Récupérer un service spécifique",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Détails du service"),
     *     @OA\Response(response="404", description="Service non trouvé")
     * )
     */
    public function show(Service $service)
    {
        // Laravel fait le findOrFail() pour nous grâce au "Route Model Binding"
        // On charge la relation pour l'inclure dans la réponse
        return response()->json($service->load('secretaireResponsable'));
    }

    /**
     * @OA\Put(
     *     path="/api/services/{id}",
     *     summary="Mettre à jour un service",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="nom", type="string", example="Nouveau Nom du Service")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Service mis à jour"),
     *     @OA\Response(response="422", description="Erreur de validation"),
     *     @OA\Response(response="404", description="Service non trouvé")
     * )
     */
    public function update(Request $request, Service $service)
    {
        // Validation (unique ignore l'enregistrement actuel)
        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|required|string|max:100',
            'code_service' => 'sometimes|required|string|max:20|unique:services,code_service,' . $service->id,
            'description' => 'nullable|string',
            'secretaire_responsable_id' => 'nullable|exists:users,id',
            'email_contact' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:20',
            'is_active' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Mise à jour du service
        $service->update($validator->validated());

        // Retourner le service mis à jour
        return response()->json($service);
    }

    /**
     * @OA\Delete(
     *     path="/api/services/{id}",
     *     summary="Supprimer un service",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="204", description="Service supprimé (pas de contenu en retour)"),
     *     @OA\Response(response="404", description="Service non trouvé")
     * )
     */
    public function destroy(Service $service)
    {
        $service->delete();

        // Retourner une réponse vide avec le code 204 (No Content)
        return response()->noContent();
    }
}