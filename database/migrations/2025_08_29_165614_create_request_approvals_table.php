<?php

// database/migrations/2025_08_29_000002_create_request_approvals_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('request_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_form_id')->constrained('request_forms')->cascadeOnDelete();
            $table->unsignedTinyInteger('level'); // 1,2,3...
            $table->enum('role', ['Encargado','Rector','Compras','Contabilidad']);
            $table->enum('state', ['pendiente','aprobado','rechazado'])->default('pendiente');
            $table->foreignId('decided_by')->nullable()->constrained('users');
            $table->timestamp('decided_at')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['request_form_id','level']);
            $table->index(['role','state']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('request_approvals');
    }
};
