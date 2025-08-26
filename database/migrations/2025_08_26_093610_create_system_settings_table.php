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
    Schema::create('system_settings', function (Blueprint $table) {
        // SQL: id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
        $table->id();

        // SQL: setting_key VARCHAR(100) NOT NULL UNIQUE
        $table->string('setting_key', 100)->unique();

        // SQL: setting_value TEXT NULL
        $table->text('setting_value')->nullable();

        // SQL: setting_type ENUM(...) DEFAULT 'string'
        $table->enum('setting_type', ['string', 'integer', 'boolean', 'json', 'date'])->default('string');

        // SQL: description VARCHAR(255) NULL
        $table->string('description')->nullable();

        // SQL: is_public BOOLEAN DEFAULT FALSE
        $table->boolean('is_public')->default(false);
        
        // SQL: updated_by INT UNSIGNED NULL
        // + FOREIGN KEY ... ON DELETE SET NULL
        $table->foreignId('updated_by')
              ->nullable()
              ->constrained('users')
              ->onDelete('set null');
        
        // SQL: created_at, updated_at
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
