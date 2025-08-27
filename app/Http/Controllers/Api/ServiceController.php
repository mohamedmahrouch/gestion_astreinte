<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class ServiceController extends Controller
{
      use AuthorizesRequests;
    /**
     * Affiche une liste paginée des services.
     * Accessible à tous les utilisateurs authentifiés.
     */
    public function index()
    {
        // La policy autorise tout utilisateur authentifié à voir la liste.
        $this->authorize('viewAny', Service::class);

        $services = Service::with('secretaireResponsable')->paginate(15);
        return response()->json($services);
    }

    /**
     * Crée un nouveau service.
     * Uniquement accessible aux administrateurs.
     */
    public function store(Request $request)
    {
        // 1. Autorisation : L'utilisateur a-t-il le droit de créer un service ?
        // La policy ne l'autorisera que pour un admin.
        $this->authorize('create', Service::class);

        // 2. Validation des données
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

        // 3. Création
        $service = Service::create($validator->validated());

        return response()->json($service, 201);
    }

    /**
     * Affiche les détails d'un service spécifique.
     * Accessible à tous les utilisateurs authentifiés.
     */
    public function show(Service $service)
    {
        // La policy autorise tout utilisateur authentifié à voir un service.
        $this->authorize('view', $service);

        return response()->json($service->load('secretaireResponsable'));
    }

    /**
     * Met à jour un service existant.
     * Uniquement accessible aux administrateurs.
     */
    public function update(Request $request, Service $service)
    {
        // 1. Autorisation : L'utilisateur a-t-il le droit de modifier CE service ?
        $this->authorize('update', $service);

        // 2. Validation
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

        // 3. Mise à jour
        $service->update($validator->validated());

        return response()->json($service);
    }

    /**
     * Supprime un service.
     * Uniquement accessible aux administrateurs.
     */
    public function destroy(Service $service)
    {
        // 1. Autorisation : L'utilisateur a-t-il le droit de supprimer CE service ?
        $this->authorize('delete', $service);

        // 2. Suppression
        $service->delete();

        return response()->noContent();
    }
}