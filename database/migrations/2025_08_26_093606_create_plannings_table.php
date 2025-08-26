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
    Schema::create('plannings', function (Blueprint $table) {
        // SQL: id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
        $table->id();

        // SQL: periode_astreinte_id INT UNSIGNED NOT NULL
        // + FOREIGN KEY ... ON DELETE CASCADE
        $table->foreignId('periode_astreinte_id')
              ->constrained('periodes_astreinte')
              ->onDelete('cascade');

        // SQL: agent_id INT UNSIGNED NOT NULL
        // + FOREIGN KEY ... ON DELETE CASCADE
        $table->foreignId('agent_id')
              ->constrained('agents')
              ->onDelete('cascade');

        // SQL: agent_remplacant_id INT UNSIGNED NULL
        // + FOREIGN KEY ... ON DELETE SET NULL
        $table->foreignId('agent_remplacant_id')
              ->nullable()
              ->constrained('agents')
              ->onDelete('set null');

        // SQL: secretaire_remplacant_id INT UNSIGNED NULL
        // + FOREIGN KEY ... ON DELETE SET NULL
        $table->foreignId('secretaire_remplacant_id')
              ->nullable()
              ->constrained('users')
              ->onDelete('set null');

        // SQL: statut ENUM(...) DEFAULT 'planifie'
        $table->enum('statut', ['planifie', 'confirme', 'en_cours', 'termine', 'annule'])->default('planifie');

        // SQL: date_assignation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        $table->timestamp('date_assignation')->useCurrent();
        
        // SQL: commentaire_... TEXT NULL
        $table->text('commentaire_assignation')->nullable();
        $table->text('commentaire_admin')->nullable();
        $table->text('commentaire_agent')->nullable();
        
        // SQL: agent_accepte BOOLEAN NULL
        $table->boolean('agent_accepte')->nullable();

        // SQL: date_reponse_agent TIMESTAMP NULL
        $table->timestamp('date_reponse_agent')->nullable();
        
        // SQL: created_by INT UNSIGNED NOT NULL
        // + FOREIGN KEY ... ON DELETE RESTRICT
        $table->foreignId('created_by')
              ->constrained('users')
              ->onDelete('restrict');

        // SQL: created_at, updated_at
        $table->timestamps();

        // SQL: UNIQUE KEY uk_planning_periode_agent (periode_astreinte_id, agent_id)
        $table->unique(['periode_astreinte_id', 'agent_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plannings');
    }
};
