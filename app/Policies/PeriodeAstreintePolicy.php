<?php

namespace App\Policies;

use App\Models\PeriodeAstreinte;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;


class PeriodeAstreintePolicy
{
    use HandlesAuthorization;
    

    /**
     * L'admin a tous les droits, on ne vérifie pas plus loin.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role_type === 'admin') {
            return true;
        }
        return null;
    }

    /**
     * Les secrétaires peuvent voir la liste (qui sera filtrée).
     */
    public function viewAny(User $user): bool
    {
        return $user->role_type === 'secretaire';
    }

    /**
     * Une secrétaire peut voir une période si elle appartient à l'un de ses services.
     */
    public function view(User $user, PeriodeAstreinte $periodeAstreinte): bool
    {
        $serviceIds = $user->servicesResponsable()->pluck('id');
        return $serviceIds->contains($periodeAstreinte->service_id);
    }

    /**
     * Une secrétaire peut créer une période (la validation du service se fait dans le contrôleur).
     */
    public function create(User $user): bool
    {
        return $user->role_type === 'secretaire';
    }

    /**
     * Une secrétaire peut mettre à jour une période si elle appartient à son service.
     */
    public function update(User $user, PeriodeAstreinte $periodeAstreinte): bool
    {
        return $this->view($user, $periodeAstreinte);
    }

    /**
     * Une secrétaire peut supprimer une période si elle appartient à son service.
     */
    public function delete(User $user, PeriodeAstreinte $periodeAstreinte): bool
    {
        return $this->view($user, $periodeAstreinte);
    }
}