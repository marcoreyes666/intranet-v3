<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['permiso','cheque','compra']);
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->json('payload');
            $table->enum('status', ['pendiente','aprobada','rechazada'])->default('pendiente');
            $table->string('current_stage')->nullable();
            $table->string('final_file_path')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('requests');
    }
};
