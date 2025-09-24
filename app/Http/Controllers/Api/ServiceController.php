<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Rules\SecretaryIsAvailable; // 1. Importer notre nouvelle règle
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    use AuthorizesRequests;

    /**
     * Affiche une liste paginée des services.
     */
    public function index()
    {
        $this->authorize('viewAny', Service::class);
        $services = Service::with('secretaireResponsable')->paginate(15);
        return response()->json($services);
    }

    /**
     * Crée un nouveau service.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Service::class);

        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:100',
            'code_service' => 'required|string|max:20|unique:services,code_service',
            'description' => 'nullable|string',
            // 2. Appliquer la nouvelle règle de validation
            'secretaire_responsable_id' => ['nullable', 'exists:users,id', new SecretaryIsAvailable],
            'email_contact' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $service = Service::create($validator->validated());
        return response()->json($service, 201);
    }

    /**
     * Affiche les détails d'un service spécifique.
     */
    public function show(Service $service)
    {
        $this->authorize('view', $service);
        return response()->json($service->load('secretaireResponsable'));
    }

    /**
     * Met à jour un service existant.
     */
    public function update(Request $request, Service $service)
    {
        $this->authorize('update', $service);

        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|required|string|max:100',
            'code_service' => 'sometimes|required|string|max:20|unique:services,code_service,' . $service->id,
            'description' => 'nullable|string',
            'secretaire_responsable_id' => ['nullable', 'exists:users,id', new SecretaryIsAvailable($service->id)],
            'email_contact' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:20',
            'is_active' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $service->update($validator->validated());
        return response()->json($service);
    }

    /**
     * Supprime un service.
     */
    public function destroy(Service $service)
    {
        $this->authorize('delete', $service);
        $service->delete();
        return response()->noContent();
    }
}