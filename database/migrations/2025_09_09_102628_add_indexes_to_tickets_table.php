<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Nombres explícitos para evitar choques
            $table->index('estado', 'tickets_estado_idx');
            $table->index('prioridad', 'tickets_prioridad_idx');
            $table->index('departamento_id', 'tickets_departamento_id_idx');
            $table->index('asignado_id', 'tickets_asignado_id_idx');
            $table->index('created_at', 'tickets_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('tickets_estado_idx');
            $table->dropIndex('tickets_prioridad_idx');
            $table->dropIndex('tickets_departamento_id_idx');
            $table->dropIndex('tickets_asignado_id_idx');
            $table->dropIndex('tickets_created_at_idx');
        });
    }
};
