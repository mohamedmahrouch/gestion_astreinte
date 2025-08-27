<?php

namespace App\Policies;

use App\Models\IndisponibiliteAgent;
use App\Models\User;

class IndisponibiliteAgentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role_type === 'admin') {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->role_type === 'secretaire';
    }

    public function view(User $user, IndisponibiliteAgent $indisponibilite): bool
    {
        // On vérifie si la secrétaire gère le service de l'agent concerné par l'indisponibilité
        $serviceIds = $user->servicesResponsable()->pluck('id');
        return $serviceIds->contains($indisponibilite->agent->service_id);
    }

    public function create(User $user): bool
    {
        return $user->role_type === 'secretaire';
    }

    public function update(User $user, IndisponibiliteAgent $indisponibilite): bool
    {
        return $this->view($user, $indisponibilite);
    }

    public function delete(User $user, IndisponibiliteAgent $indisponibilite): bool
    {
        return $this->view($user, $indisponibilite);
    }
}