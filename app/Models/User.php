<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Use BelongsToMany
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nom', 'prenom', 'email', 'password', 'role_type',
        'telephone', 'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [ 'password', 'remember_token' ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Defines the new many-to-many relationship.
     * A user (secretary) can be responsible for many services.
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_user');
    }

    // --- THIS IS THE FIX ---

    /**
     * The list of virtual attributes to append to the model's array form.
     */
    protected $appends = ['is_available_for_assignment'];

    /**
     * Accessor method to calculate the 'is_available_for_assignment' attribute.
     * A secretary is available if they are not assigned to any services.
     * WE NOW USE THE NEW 'services()' RELATIONSHIP.
     */
    public function getIsAvailableForAssignmentAttribute(): bool
    {
        // This now correctly checks the pivot table. If the count is 0, the user is available.
        return $this->services()->count() === 0;
    }
}