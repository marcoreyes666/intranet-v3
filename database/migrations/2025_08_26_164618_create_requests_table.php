<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['permiso','cheque','compra']);
            $table->foreignId('user_id')->constrained('users');  // solicitante
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->json('payload');   // datos del formulario
            $table->enum('status', [
                'borrador',
                'pendiente_encargado','rechazado_encargado',
                'pendiente_contabilidad','rechazado_contabilidad',
                'pendiente_compras','rechazado_compras',
                'pendiente_rectoria','rechazado_rectoria',
                'aprobado'
            ])->default('borrador');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('requests');
    }
};
