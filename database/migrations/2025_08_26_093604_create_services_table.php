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
    Schema::create('services', function (Blueprint $table) {
        // SQL: id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
        $table->id();

        // SQL: nom VARCHAR(100) NOT NULL
        $table->string('nom', 100);

        // SQL: description TEXT NULL
        $table->text('description')->nullable();

        // SQL: code_service VARCHAR(20) NOT NULL UNIQUE
        $table->string('code_service', 20)->unique();

        // SQL: secretaire_responsable_id INT UNSIGNED NULL
        // + FOREIGN KEY ... ON DELETE SET NULL
        $table->foreignId('secretaire_responsable_id')
              ->nullable()
              ->constrained('users')
              ->onDelete('set null');

        // SQL: is_active BOOLEAN DEFAULT TRUE
        $table->boolean('is_active')->default(true);

        // SQL: email_contact VARCHAR(255) NULL
        $table->string('email_contact')->nullable();

        // SQL: telephone VARCHAR(20) NULL
        $table->string('telephone', 20)->nullable();
        
        // SQL: created_at, updated_at
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
