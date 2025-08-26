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
    Schema::create('indisponibilites_secretaires', function (Blueprint $table) {
        // SQL: id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
        $table->id();

        // SQL: secretaire_id INT UNSIGNED NOT NULL
        // + FOREIGN KEY ... ON DELETE CASCADE
        $table->foreignId('secretaire_id')
              ->constrained('users')
              ->onDelete('cascade');
        
        // SQL: type_indisponibilite ENUM(...) NOT NULL
        $table->enum('type_indisponibilite', ['conge_paye', 'conge_maladie', 'formation', 'mission', 'personnel', 'autre']);

        // SQL: date_debut DATE NOT NULL, date_fin DATE NOT NULL
        $table->date('date_debut');
        $table->date('date_fin');

        // SQL: motif VARCHAR(255) NULL
        $table->string('motif')->nullable();

        // SQL: statut ENUM(...) DEFAULT 'en_attente'
        $table->enum('statut', ['en_attente', 'approuve', 'refuse'])->default('en_attente');
        
        // SQL: approuve_par INT UNSIGNED NULL
        // + FOREIGN KEY ... ON DELETE SET NULL
        $table->foreignId('approuve_par')
              ->nullable()
              ->constrained('users')
              ->onDelete('set null');

        // SQL: date_approbation TIMESTAMP NULL
        $table->timestamp('date_approbation')->nullable();
        
        // SQL: commentaire_approbation TEXT NULL
        $table->text('commentaire_approbation')->nullable();
        
        // SQL: created_at, updated_at
        $table->timestamps();
    });

    // Ajout de la contrainte CHECK pour les dates
    \Illuminate\Support\Facades\DB::statement('ALTER TABLE indisponibilites_secretaires ADD CONSTRAINT chk_dates_indispo_secretaires CHECK (date_fin >= date_debut)');
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indisponibilites_secretaires');
    }
};
