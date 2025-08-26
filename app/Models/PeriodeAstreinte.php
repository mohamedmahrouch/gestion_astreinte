<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodeAstreinte extends Model
{
    use HasFactory;
    
    // Le nom de la table est "periodes_astreinte" (avec un s), il faut le préciser.
    protected $table = 'periodes_astreinte';

    protected $fillable = [
        'service_id',
        'type_periode',
        'date_debut',
        'heure_debut',
        'date_fin',
        'heure_fin',
        'nb_agents_requis',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'is_active' => 'boolean',
    ];

    // Une période appartient à un service.
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    // Une période a été créée par un utilisateur.
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Une période peut avoir plusieurs affectations (plannings).
    public function plannings(): HasMany
    {
        return $this->hasMany(Planning::class);
    }
}