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
    Schema::create('agent_sessions', function (Blueprint $table) {
        // SQL: id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
        $table->id();

        // SQL: agent_id INT UNSIGNED NOT NULL
        // + FOREIGN KEY ... ON DELETE CASCADE
        $table->foreignId('agent_id')
              ->constrained('agents')
              ->onDelete('cascade');

        // SQL: token VARCHAR(255) NOT NULL UNIQUE
        $table->string('token')->unique();

        // SQL: expires_at TIMESTAMP NOT NULL
        $table->timestamp('expires_at');

        // SQL: ip_address VARCHAR(45) NULL
        $table->ipAddress()->nullable();

        // SQL: user_agent TEXT NULL
        $table->text('user_agent')->nullable();

        // SQL: is_active BOOLEAN DEFAULT TRUE
        $table->boolean('is_active')->default(true);
        
        // SQL: created_at, updated_at
        $table->timestamps();
        
        // --- AJOUT DES INDEX ---
        // SQL: INDEX idx_agent_sessions_agent (agent_id)
        $table->index('agent_id', 'idx_agent_sessions_agent');
        
        // SQL: INDEX idx_agent_sessions_expires (expires_at)
        $table->index('expires_at', 'idx_agent_sessions_expires');
        
        // SQL: INDEX idx_agent_sessions_active (is_active)
        $table->index('is_active', 'idx_agent_sessions_active');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_sessions');
    }
};
