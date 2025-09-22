<?php

// database/migrations/2025_08_29_000001_create_request_forms_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('request_forms', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['permiso','cheque','compra']);
            $table->enum('status', ['borrador','en_revision','aprobada','rechazada','completada'])->default('borrador');
            $table->foreignId('user_id')->constrained('users');                     // quien solicita
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->unsignedTinyInteger('current_level')->default(1);               // nivel de aprobación actual
            $table->timestamp('submitted_at')->nullable();                          // cuando pasó de borrador a revisión
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('request_forms');
    }
};
