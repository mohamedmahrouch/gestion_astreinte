<?php
namespace App\Policies;

use App\Models\Agent;
use App\Models\User;

class AgentPolicy
{
    // L'admin a tous les droits
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role_type === 'admin') {
            return true;
        }
        return null; // Si ce n'est pas un admin, on continue vers les autres règles
    }

    // Une secrétaire peut voir tous les agents de son service
    public function viewAny(User $user): bool
    {
        return $user->role_type === 'secretaire';
    }

    // Une secrétaire peut voir un agent s'il est dans son service
    public function view(User $user, Agent $agent): bool
    {
        // On doit récupérer les ID des services gérés par la secrétaire
        $serviceIds = $user->services()->pluck('services.id');
        return $user->services()->where('services.id', $agent->service_id)->exists();
    }
    
    // Une secrétaire peut créer un agent si elle le met dans son service
    public function create(User $user): bool
    {
         return $user->role_type === 'secretaire';
    }
    
    // Une secrétaire peut modifier/supprimer un agent s'il est dans son service
    public function update(User $user, Agent $agent): bool { return $this->view($user, $agent); }
    public function delete(User $user, Agent $agent): bool { return $this->view($user, $agent); }
}