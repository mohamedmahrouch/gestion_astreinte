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
    Schema::create('audit_logs', function (Blueprint $table) {
        $table->id();

        // CHANGEMENT ICI: onDelete('restrict')
        $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('restrict');
        
        // CHANGEMENT ICI: onDelete('restrict')
        $table->foreignId('agent_id')->nullable()->constrained('agents')->onDelete('restrict');

        $table->string('action', 100);
        $table->string('table_name', 100);
        $table->unsignedBigInteger('record_id')->nullable();
        $table->json('old_values')->nullable();
        $table->json('new_values')->nullable();
        $table->ipAddress()->nullable();
        $table->text('user_agent')->nullable();
        $table->timestamp('created_at')->useCurrent();

        // Index
        $table->index('user_id', 'idx_audit_user');
        $table->index('agent_id', 'idx_audit_agent');
        $table->index('action', 'idx_audit_action');
        $table->index('table_name', 'idx_audit_table');
        $table->index('record_id', 'idx_audit_record');
    });

    // ON REACTIVE LA CONTRAINTE CHECK
    \Illuminate\Support\Facades\DB::statement('ALTER TABLE audit_logs ADD CONSTRAINT chk_audit_actor CHECK ((user_id IS NOT NULL AND agent_id IS NULL) OR (user_id IS NULL AND agent_id IS NOT NULL) OR (user_id IS NULL AND agent_id IS NULL))');
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
