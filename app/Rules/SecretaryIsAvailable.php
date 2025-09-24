<?php
namespace App\Rules;

use App\Models\Service;
use Illuminate\Contracts\Validation\Rule;

class SecretaryIsAvailable implements Rule
{
    private $ignoreServiceId;

    // On peut ignorer un ID de service (utile pour la mise à jour)
    public function __construct($ignoreServiceId = null)
    {
        $this->ignoreServiceId = $ignoreServiceId;
    }

    public function passes($attribute, $value)
    {
        // La règle passe si la secrétaire n'est responsable d'AUCUN service,
        // ou si le seul service dont elle est responsable est celui qu'on ignore.
        return Service::where('secretaire_responsable_id', $value)
            ->where('id', '!=', $this->ignoreServiceId)
            ->count() === 0;
    }

    public function message()
    {
        return 'Cette secrétaire est déjà affectée à un autre service.';
    }
}