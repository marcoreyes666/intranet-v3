<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('code')->nullable();         // opcional (siglas)
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // (Opcional, pensado para la Fase 3B)
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'department_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'department_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('department_id');
            });
        }
        Schema::dropIfExists('departments');
    }
};
