<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'description',
        'code_service',
        'secretaire_responsable_id',
        'is_active',
        'email_contact',
        'telephone',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Un service a une secrétaire responsable (qui est un User).
    public function secretaireResponsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'secretaire_responsable_id');
    }

    // Un service a plusieurs agents.
    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class);
    }

    // Un service a plusieurs périodes d'astreinte.
    public function periodesAstreinte(): HasMany
    {
        return $this->hasMany(PeriodeAstreinte::class);
    }
}