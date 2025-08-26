<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // On supprime toute création d'utilisateur par défaut ici.
        // On appelle UNIQUEMENT nos seeders personnalisés.

        $this->call([
            UserSeeder::class, // Celui-ci crée notre admin avec 'nom' et 'prenom'
            SystemSettingSeeder::class,
        ]);
    }
}