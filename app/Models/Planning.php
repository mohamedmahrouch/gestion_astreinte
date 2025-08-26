<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Planning extends Model
{
    use HasFactory;

    protected $fillable = [
        'periode_astreinte_id',
        'agent_id',
        'agent_remplacant_id',
        'secretaire_remplacant_id',
        'statut',
        'commentaire_assignation',
        'commentaire_admin',
        'commentaire_agent',
        'agent_accepte',
        'date_reponse_agent',
        'created_by',
    ];

    protected $casts = [
        'agent_accepte' => 'boolean',
        'date_assignation' => 'datetime',
        'date_reponse_agent' => 'datetime',
    ];

    public function periodeAstreinte(): BelongsTo
    {
        return $this->belongsTo(PeriodeAstreinte::class, 'periode_astreinte_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }
    
    // Une relation optionnelle (peut être null)
    public function agentRemplacant(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'agent_remplacant_id');
    }

    // Une relation optionnelle (peut être null)
    public function secretaireRemplacant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'secretaire_remplacant_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}