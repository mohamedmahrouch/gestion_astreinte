<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
        */
// Fichier : database/migrations/xxxx_xx_xx_xxxxxx_create_users_table.php

public function up(): void
{
    Schema::create('users', function (Blueprint $table) {
        // Votre SQL : id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
        $table->id(); // Équivalent Laravel

        // Votre SQL : nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL
        $table->string('nom', 100);
        $table->string('prenom', 100);

        // Votre SQL : email VARCHAR(255) NOT NULL UNIQUE
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        
        // Votre SQL : password VARCHAR(255) NOT NULL
        $table->string('password');
        
        // Votre SQL : role_type ENUM('admin', 'secretaire') NOT NULL DEFAULT 'secretaire'
        $table->enum('role_type', ['admin', 'secretaire'])->default('secretaire');
        
        // Vos champs additionnels
        $table->string('telephone', 20)->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamp('last_login_at')->nullable();
        
        // Champ pour le "se souvenir de moi"
        $table->rememberToken();
        
        // Votre SQL : created_at, updated_at
        $table->timestamps(); // Équivalent Laravel
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
