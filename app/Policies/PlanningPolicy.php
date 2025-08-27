<?php

namespace App\Policies;

use App\Models\Planning;
use App\Models\User;

class PlanningPolicy
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

    public function view(User $user, Planning $planning): bool
    {
        // La secrétaire peut voir un planning si la période d'astreinte associée
        // appartient à l'un de ses services.
        $serviceIds = $user->servicesResponsable()->pluck('id');
        return $serviceIds->contains($planning->periodeAstreinte->service_id);
    }



    public function create(User $user): bool
    {
        return $user->role_type === 'secretaire';
    }

    public function update(User $user, Planning $planning): bool
    {
        return $this->view($user, $planning);
    }

    public function delete(User $user, Planning $planning): bool
    {
        return $this->view($user, $planning);
    }
}