<?php

namespace App\Policies;

use App\Models\Service; // <-- LIGNE MANQUANTE : importer le modèle
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization; // <-- LIGNE MANQUANTE : importer le trait

class ServicePolicy
{
    use HandlesAuthorization; // <-- LIGNE MANQUANTE : utiliser le trait

    /**
     * Règle privée pour vérifier si l'utilisateur est un admin.
     */
    private function isAdmin(User $user): bool
    {
        return $user->role_type === 'admin';
    }

    /**
     * Détermine si n'importe quel utilisateur peut voir la liste des services.
     */
    public function viewAny(User $user): bool
    {
        // Dans notre cas, un utilisateur authentifié peut voir la liste
        return true;
    }

    /**
     * Détermine si un utilisateur peut voir un service spécifique.
     */
    public function view(User $user, Service $service): bool
    {
        // Tout utilisateur authentifié peut voir les détails d'un service
        return true;
    }

    /**
     * Détermine si un utilisateur peut créer un service.
     */
    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Détermine si un utilisateur peut mettre à jour un service.
     */
    public function update(User $user, Service $service): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Détermine si un utilisateur peut supprimer un service.
     */
    public function delete(User $user, Service $service): bool
    {
        return $this->isAdmin($user);
    }
}