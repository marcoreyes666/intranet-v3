<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->enum('categoria', ['Sistemas','Mantenimiento','Redes','Impresoras','Software','Infraestructura'])->default('Sistemas');
            $table->enum('prioridad', ['Baja','Media','Alta','Crítica'])->default('Media');
            $table->enum('estado', ['Abierto','En proceso','Resuelto','Cerrado'])->default('Abierto');
            $table->foreignId('usuario_id')->constrained('users');               // quien reporta
            $table->foreignId('asignado_id')->nullable()->constrained('users');  // técnico asignado
            $table->foreignId('departamento_id')->nullable()->constrained('departments'); // opcional
            $table->timestamp('resuelto_en')->nullable();
            $table->timestamps();
            $table->index(['estado','categoria','prioridad']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('tickets');
    }
};
