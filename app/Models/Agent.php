<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'matricule',
        'nom',
        'prenom',
        'telephone_principal',
        'telephone_secours',
        'email_professionnel',
        'date_embauche',
        'poste',
        'niveau_competence',
        'is_disponible_astreinte',
        'commentaires',
        'created_by',
    ];

    protected $casts = [
        'date_embauche' => 'date',
        'is_disponible_astreinte' => 'boolean',
    ];

    // Un agent appartient à un service.
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    // Un agent a été créé par un utilisateur (admin/secrétaire).
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Un agent peut avoir plusieurs affectations dans le planning.
    public function plannings(): HasMany
    {
        return $this->hasMany(Planning::class);
    }
    
    // Un agent peut avoir plusieurs périodes d'indisponibilité.
    public function indisponibilites(): HasMany
    {
        return $this->hasMany(IndisponibiliteAgent::class);
    }
}