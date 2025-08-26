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
    Schema::create('agents', function (Blueprint $table) {
        // SQL: id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
        $table->id();

        // SQL: service_id INT UNSIGNED NOT NULL
        // + FOREIGN KEY ... ON DELETE RESTRICT
        $table->foreignId('service_id')
              ->constrained('services')
              ->onDelete('restrict');

        // SQL: matricule VARCHAR(50) NOT NULL UNIQUE
        $table->string('matricule', 50)->unique();

        // SQL: nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL
        $table->string('nom', 100);
        $table->string('prenom', 100);

        // SQL: telephone_principal VARCHAR(20) NOT NULL
        $table->string('telephone_principal', 20);
        
        // SQL: telephone_secours VARCHAR(20) NULL
        $table->string('telephone_secours', 20)->nullable();
        
        // SQL: email_professionnel VARCHAR(255) NULL
        $table->string('email_professionnel')->nullable();

        // SQL: date_embauche DATE NULL
        $table->date('date_embauche')->nullable();
        
        // SQL: poste VARCHAR(100) NULL
        $table->string('poste', 100)->nullable();
        
        // SQL: niveau_competence ENUM('junior', 'senior', 'expert') DEFAULT 'junior'
        $table->enum('niveau_competence', ['junior', 'senior', 'expert'])->default('junior');

        // SQL: is_disponible_astreinte BOOLEAN DEFAULT TRUE
        $table->boolean('is_disponible_astreinte')->default(true);

        // SQL: commentaires TEXT NULL
        $table->text('commentaires')->nullable();

        // SQL: created_by INT UNSIGNED NOT NULL
        // + FOREIGN KEY ... ON DELETE RESTRICT
        $table->foreignId('created_by')
              ->constrained('users')
              ->onDelete('restrict');

        // SQL: created_at, updated_at
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
