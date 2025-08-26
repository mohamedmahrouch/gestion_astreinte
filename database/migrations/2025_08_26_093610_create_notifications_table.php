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
    Schema::create('notifications', function (Blueprint $table) {
        $table->id();

        // CHANGEMENT ICI: onDelete('restrict')
        $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('restrict');
        
        // CHANGEMENT ICI: onDelete('restrict')
        $table->foreignId('agent_id')->nullable()->constrained('agents')->onDelete('restrict');

        $table->enum('type_notification', [
            'planning_assigne', 'planning_modifie', 'rappel_astreinte', 'demande_remplacement', 
            'indisponibilite_approuvee', 'remplacement_secretaire', 'demande_indisponibilite', 'autre'
        ]);
        $table->string('titre');
        $table->text('message');
        $table->json('data')->nullable();
        $table->boolean('is_read')->default(false);
        $table->timestamp('read_at')->nullable();
        $table->enum('priorite', ['basse', 'normale', 'haute', 'urgente'])->default('normale');
        $table->timestamp('expires_at')->nullable();
        $table->timestamps();

        // Index
        $table->index('user_id', 'idx_notifications_user');
        $table->index('agent_id', 'idx_notifications_agent');
        $table->index('type_notification', 'idx_notifications_type');
        $table->index('is_read', 'idx_notifications_read');
        $table->index('priorite', 'idx_notifications_priorite');
    });

    // ON REACTIVE LA CONTRAINTE CHECK
    \Illuminate\Support\Facades\DB::statement('ALTER TABLE notifications ADD CONSTRAINT chk_notification_recipient CHECK ((user_id IS NOT NULL AND agent_id IS NULL) OR (user_id IS NULL AND agent_id IS NOT NULL))');
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
