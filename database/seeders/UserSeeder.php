<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // N'oubliez pas d'importer le modèle User
use Illuminate\Support\Facades\Hash; // Et la façade Hash

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer l'administrateur par défaut
        User::create([
            'nom' => 'Admin',
            'prenom' => 'Système',
            'email' => 'admin@astreinte.local',
            'password' => Hash::make('password'), // Le mot de passe sera 'password'
            'role_type' => 'admin',
            'is_active' => true,
            'email_verified_at' => now()
        ]);
    }
}