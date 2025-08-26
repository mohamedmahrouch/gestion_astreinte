<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndisponibiliteAgent extends Model
{
    use HasFactory;

    protected $table = 'indisponibilites_agents';

    protected $fillable = [
        'agent_id',
        'type_indisponibilite',
        'date_debut',
        'date_fin',
        'heure_debut',
        'heure_fin',
        'motif',
        'statut',
        'approuve_par',
        'created_by',
        'saisie_par_agent',
    ];
    
    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'saisie_par_agent' => 'boolean',
        'date_approbation' => 'datetime',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
    
    public function approuvePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approuve_par');
    }
    
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}