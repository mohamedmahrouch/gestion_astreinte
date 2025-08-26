<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting; // Importez le modèle SystemSetting

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SystemSetting::insert([
            ['setting_key' => 'app_name', 'setting_value' => 'Gestion Astreinte', 'setting_type' => 'string', 'description' => "Nom de l'application", 'is_public' => true],
            ['setting_key' => 'delai_confirmation_heures', 'setting_value' => '48', 'setting_type' => 'integer', 'description' => "Délai en heures pour confirmer une astreinte", 'is_public' => false],
            ['setting_key' => 'notification_rappel_heures', 'setting_value' => '24', 'setting_type' => 'integer', 'description' => "Heures avant astreinte pour envoyer rappel", 'is_public' => false],
            ['setting_key' => 'agent_session_duration_hours', 'setting_value' => '24', 'setting_type' => 'integer', 'description' => "Durée de validité session agent en heures", 'is_public' => false],
        ]);
    }
}