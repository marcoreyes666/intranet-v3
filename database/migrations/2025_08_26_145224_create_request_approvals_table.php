<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('request_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->string('stage'); // encargado, contabilidad, compras, rector
            $table->foreignId('approver_id')->nullable()->constrained('users');
            $table->enum('decision', ['pendiente','aprobada','rechazada'])->default('pendiente');
            $table->text('comments')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->unsignedInteger('stage_order'); // 1,2,3...
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('request_approvals');
    }
};
