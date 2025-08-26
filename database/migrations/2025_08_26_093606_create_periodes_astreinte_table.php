<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('periodes_astreinte', function (Blueprint $table) {
        // SQL: id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
        $table->id();

        // SQL: service_id INT UNSIGNED NOT NULL
        // + FOREIGN KEY ... ON DELETE CASCADE
        $table->foreignId('service_id')
              ->constrained('services')
              ->onDelete('cascade');

        // SQL: type_periode ENUM('hebdomadaire', 'weekend', 'ferie', 'nuit') NOT NULL
        $table->enum('type_periode', ['hebdomadaire', 'weekend', 'ferie', 'nuit']);

        // SQL: date_debut DATE NOT NULL
        $table->date('date_debut');
        
        // SQL: heure_debut TIME NOT NULL DEFAULT '08:00:00'
        $table->time('heure_debut')->default('08:00:00');

        // SQL: date_fin DATE NOT NULL
        $table->date('date_fin');
        
        // SQL: heure_fin TIME NOT NULL DEFAULT '18:00:00'
        $table->time('heure_fin')->default('18:00:00');

        // SQL: nb_agents_requis INT DEFAULT 1
        $table->integer('nb_agents_requis')->default(1);
        
        // SQL: description VARCHAR(255) NULL
        $table->string('description')->nullable();

        // SQL: is_active BOOLEAN DEFAULT TRUE
        $table->boolean('is_active')->default(true);
        
        // SQL: created_by INT UNSIGNED NOT NULL
        // + FOREIGN KEY ... ON DELETE RESTRICT
        $table->foreignId('created_by')
              ->constrained('users')
              ->onDelete('restrict');

        // SQL: created_at, updated_at
        $table->timestamps();
    });

    // Ajout de la contrainte CHECK personnalisée
    // On utilise DB::statement pour exécuter du SQL brut
    \Illuminate\Support\Facades\DB::statement('ALTER TABLE periodes_astreinte ADD CONSTRAINT chk_heures_coherentes CHECK ((date_debut = date_fin AND heure_fin > heure_debut) OR (date_fin > date_debut))');
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodes_astreinte');
    }
};
