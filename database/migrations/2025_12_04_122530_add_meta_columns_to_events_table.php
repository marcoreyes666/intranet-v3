<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Creador del evento (nullable por si ya hay datos antiguos)
            if (! Schema::hasColumn('events', 'created_by')) {
                $table->foreignId('created_by')
                    ->nullable()
                    ->after('notes')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            // Departamento (nullable; si no usas departamentos, igual no estorba)
            if (! Schema::hasColumn('events', 'department_id')) {
                $table->foreignId('department_id')
                    ->nullable()
                    ->after('created_by')
                    ->constrained('departments')
                    ->nullOnDelete();
            }

            // Flag para eventos internos de sonido
            if (! Schema::hasColumn('events', 'is_sound_only')) {
                $table->boolean('is_sound_only')
                    ->default(false)
                    ->after('department_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'is_sound_only')) {
                $table->dropColumn('is_sound_only');
            }

            if (Schema::hasColumn('events', 'department_id')) {
                $table->dropConstrainedForeignId('department_id');
            }

            if (Schema::hasColumn('events', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
        });
    }
};
