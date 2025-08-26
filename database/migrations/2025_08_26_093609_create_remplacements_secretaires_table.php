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
    Schema::create('remplacements_secretaires', function (Blueprint $table) {
        // SQL: id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
        $table->id();

        // SQL: secretaire_absent_id INT UNSIGNED NOT NULL
        // + FOREIGN KEY ... ON DELETE CASCADE
        $table->foreignId('secretaire_absent_id')
              ->constrained('users')
              ->onDelete('cascade');
        
        // SQL: secretaire_remplacant_id INT UNSIGNED NOT NULL
        // + FOREIGN KEY ... ON DELETE CASCADE
        $table->foreignId('secretaire_remplacant_id')
              ->constrained('users')
              ->onDelete('cascade');
        
        // SQL: service_id INT UNSIGNED NOT NULL
        // + FOREIGN KEY ... ON DELETE CASCADE
        $table->foreignId('service_id')
              ->constrained('services')
              ->onDelete('cascade');

        // SQL: date_debut DATE NOT NULL, date_fin DATE NOT NULL
        $table->date('date_debut');
        $table->date('date_fin');

        // SQL: motif VARCHAR(255) NULL
        $table->string('motif')->nullable();

        // SQL: statut ENUM(...) DEFAULT 'planifie'
     $table->enum('statut', ['planifie', 'en_cours', 'termine', 'annule'])->default('planifie');
        // SQL: created_by INT UNSIGNED NOT NULL
        // + FOREIGN KEY ... ON DELETE RESTRICT
        $table->foreignId('created_by')
              ->constrained('users')
              ->onDelete('restrict');
        
        // SQL: created_at, updated_at
        $table->timestamps();
    });

    // Ajout des contraintes CHECK personnalisées
    \Illuminate\Support\Facades\DB::statement('ALTER TABLE remplacements_secretaires ADD CONSTRAINT chk_dates_remplacement CHECK (date_fin >= date_debut)');
    \Illuminate\Support\Facades\DB::statement('ALTER TABLE remplacements_secretaires ADD CONSTRAINT chk_secretaires_differents CHECK (secretaire_absent_id != secretaire_remplacant_id)');
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remplacements_secretaires');
    }
};
